<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'

const currentTab = ref('feed')
const title = ref('')
const caption = ref('')
const albumNumber = ref('78716')
const fileInput = ref<HTMLInputElement | null>(null)

const loading = ref(false)
const uploading = ref(false)
const photos = ref<any[]>([])
const nextCursor = ref<string | null>(null)

const searchId = ref('')
const followMessage = ref('')
const followError = ref(false)

// New state for system hints
const existingUsers = ref<string[]>([])
const loadingHints = ref(false)

const getCorrectImageUrl = (rawUrl: string) => {
  if (!rawUrl) return ''
  if (rawUrl.includes('127.0.0.1') && !rawUrl.includes(':8080')) {
    const parts = rawUrl.split('/storage/')
    return parts.length > 1 ? `/storage/${parts[1]}` : rawUrl
  }
  return rawUrl
}

const fetchPhotos = async () => {
  if (loading.value) return
  loading.value = true
  try {
    const res = await fetch(`/api/78716/v1/feed?limit=4`)
    const json = await res.json()

    // Спускаємося всередину відповіді Laravel cursorPaginate (json.data)
    const paginationContext = json.data
    const rawPhotos = paginationContext?.data || []

    photos.value = rawPhotos.map((photo: any) => ({
      ...photo,
      image_url: getCorrectImageUrl(
        photo.image_url || `http://localhost:8080/storage/${photo.image_path}`,
      ),
    }))

    // Зберігаємо маркер наступної сторінки
    nextCursor.value = paginationContext?.next_cursor || null
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const loadMore = async () => {
  // КРИТИЧНИЙ ЗАХИСТ: якщо курсору немає або вже йде завантаження — зупиняємо роботу
  if (!nextCursor.value || loading.value) return

  loading.value = true
  try {
    const res = await fetch(`/api/78716/v1/feed?limit=4&cursor=${nextCursor.value}`)
    const json = await res.json()

    const paginationContext = json.data
    const nextPhotos = paginationContext?.data || []

    // Якщо сервер повернув порожній масив — примусово зупиняємо пагінацію
    if (nextPhotos.length === 0) {
      nextCursor.value = null
      return
    }

    const mappedNext = nextPhotos.map((photo: any) => ({
      ...photo,
      image_url: getCorrectImageUrl(
        photo.image_url || `http://localhost:8080/storage/${photo.image_path}`,
      ),
    }))

    // Додаємо нові фотки до існуючих
    photos.value = [...photos.value, ...mappedNext]

    // Оновлюємо курсор значенням наступної сторінки
    nextCursor.value = paginationContext?.next_cursor || null
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Fetch hint tokens from the new endpoint
const fetchUserHints = async () => {
  loadingHints.value = true
  try {
    const res = await fetch('/api/78716/v1/users/hints')
    const json = await res.json()
    existingUsers.value = json.data || []
  } catch (err) {
    console.error('Failed to load user hints:', err)
  } finally {
    loadingHints.value = false
  }
}

const selectHint = (album: string) => {
  searchId.value = album
}

const handleFollow = async () => {
  if (!searchId.value.trim()) return
  followMessage.value = ''
  followError.value = false

  try {
    const res = await fetch(`/api/78716/v1/users/${searchId.value}/follow`, { method: 'POST' })
    const json = await res.json()

    if (res.status === 200 || res.status === 201) {
      followMessage.value = `Successfully followed author ${searchId.value}!`
      await fetchPhotos()
    } else {
      followError.value = true
      followMessage.value = json.message || 'Follow request failed.'
    }
  } catch (err) {
    followError.value = true
    followMessage.value = 'Connection error occurred.'
  }
}

const handleUnfollow = async () => {
  if (!searchId.value.trim()) return
  followMessage.value = ''
  followError.value = false

  try {
    const res = await fetch(`/api/78716/v1/users/${searchId.value}/follow`, { method: 'DELETE' })
    const json = await res.json()

    if (res.status === 200) {
      followMessage.value = `Successfully unfollowed author ${searchId.value}!`
      await fetchPhotos()
    } else {
      followError.value = true
      followMessage.value = json.message || 'Unfollow request failed.'
    }
  } catch (err) {
    followError.value = true
    followMessage.value = 'Connection error occurred.'
  }
}

const handleScroll = () => {
  if (currentTab.value !== 'feed') return
  const { scrollTop, scrollHeight, clientHeight } = document.documentElement
  if (scrollTop + clientHeight >= scrollHeight - 150) {
    loadMore()
  }
}

const handleUpload = async () => {
  if (!fileInput.value?.files?.[0]) {
    alert('Please select an image file first.')
    return
  }
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('title', title.value)
    formData.append('caption', caption.value)
    formData.append('album_number', albumNumber.value)
    formData.append('image', fileInput.value.files[0])

    const res = await fetch('/api/78716/v1/photos', { method: 'POST', body: formData })
    const data = await res.json()
    if (data.success) {
      title.value = ''
      caption.value = ''
      if (fileInput.value) fileInput.value.value = ''
      await fetchPhotos()
      await fetchUserHints() // Refresh hints list after a new upload
    }
  } catch (err) {
    console.error(err)
  } finally {
    uploading.value = false
  }
}

// Watch active tab to load hints when opening the search page
watch(currentTab, (newTab) => {
  if (newTab === 'users') {
    fetchUserHints()
  }
})

onMounted(() => {
  fetchPhotos()
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="feed-container">
    <div class="tabs-navigation">
      <button :class="['tab-btn', { active: currentTab === 'feed' }]" @click="currentTab = 'feed'">
        📰 News Feed
      </button>
      <button
        :class="['tab-btn', { active: currentTab === 'users' }]"
        @click="currentTab = 'users'"
      >
        🔍 Find & Follow
      </button>
    </div>

    <!-- TAB 1: NEWS FEED -->
    <div v-if="currentTab === 'feed'" class="tab-content">
      <div class="grid-actions">
        <a class="feed-card link" href="/api/health" target="_blank">🟢 Health</a>
        <a class="feed-card link" href="/api/78716/v1/photos" target="_blank">📸 Photos API</a>
        <button class="feed-card link-btn" @click="fetchPhotos">🔄 Refresh Feed</button>
      </div>

      <div class="feed-card">
        <h2>Upload New Post</h2>
        <div class="form-group">
          <input
            type="text"
            v-model="title"
            placeholder="Photo Title"
            required
            class="input-field"
          />
          <textarea
            v-model="caption"
            placeholder="Write a caption..."
            class="input-field textarea-field"
          ></textarea>
          <div class="row">
            <input type="file" ref="fileInput" accept="image/*" class="file-field" />
            <input type="text" v-model="albumNumber" class="album-field" />
          </div>
        </div>
        <button class="btn" @click="handleUpload" :disabled="uploading">
          {{ uploading ? 'Processing File...' : 'Share Post' }}
        </button>
      </div>

      <div class="feed-card">
        <h2>Media Feed (News Service)</h2>
        <div class="list-feed">
          <div v-if="loading && photos.length === 0" class="empty">
            Loading assets from Redis...
          </div>
          <div v-else-if="photos.length === 0" class="empty">
            Your feed is empty. Find and follow profiles in the search tab!
          </div>

          <div v-else class="instagram-grid">
            <!-- Комбінуємо ID та індекс для унікальності ключів у Vue -->
            <div v-for="(p, index) in photos" :key="`${p.id}-${index}`" class="insta-card">
              <img
                :src="p.image_url"
                class="post-img"
                alt="Post view"
                loading="lazy"
                decoding="async"
              />
              <div class="post-info">
                <div class="post-title">{{ p.title }}</div>
                <div class="post-caption" v-if="p.caption">{{ p.caption }}</div>
                <span class="status-badge">Author: {{ p.album_number }}</span>
              </div>
            </div>
          </div>
          
          <!-- Блок індикаторів стану пагінації -->
          <div v-if="loading && photos.length > 0" class="scroll-loader">
            ⏳ Loading more posts...
          </div>
          <div v-if="!nextCursor && photos.length > 0" class="scroll-end-message">
            ✨ You have caught up with everything!
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: FIND & FOLLOW -->
    <div v-if="currentTab === 'users'" class="tab-content">
      <div class="feed-card">
        <h2>Manage Subscriptions by Album Number</h2>
        <div class="search-box">
          <input
            type="text"
            v-model="searchId"
            placeholder="Enter student album number (e.g., 99999)"
            class="input-field"
          />

          <!-- HINTS BLOCK -->
          <div class="hints-section">
            <span class="hints-label">Active Creators in Database:</span>
            <div v-if="loadingHints" class="hints-loading">Scanning database...</div>
            <div v-else-if="existingUsers.length === 0" class="hints-empty">
              No active creators found.
            </div>
            <div v-else class="hints-container">
              <button
                v-for="user in existingUsers"
                :key="user"
                class="hint-tag"
                @click="selectHint(user)"
              >
                🆔 {{ user }}
              </button>
            </div>
          </div>

          <div class="search-actions">
            <button class="btn follow-btn" @click="handleFollow">➕ Follow</button>
            <button class="btn unfollow-btn" @click="handleUnfollow">❌ Unfollow</button>
          </div>
        </div>

        <div v-if="followMessage" :class="['notify-box', { error: followError }]">
          {{ followMessage }}
        </div>
      </div>
    </div>
  </div>
</template>


<style scoped>
.feed-container {
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
}

.tabs-navigation {
  display: flex;
  gap: 10px;
  margin-bottom: 25px;
  border-bottom: 2px solid #2a2a2a;
  padding-bottom: 10px;
}

.tab-btn {
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  color: #aaa;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  font-size: 15px;
  transition: all 0.2s;
}

.tab-btn.active {
  background: #42b883;
  color: white;
  border-color: #42b883;
}

.tab-btn:hover:not(.active) {
  background: #252525;
  color: white;
}

.feed-card {
  background: #1a1a1a;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.feed-card h2 {
  font-size: 18px;
  margin-bottom: 15px;
  border-bottom: 1px solid #2a2a2a;
  padding-bottom: 8px;
  color: #42b883;
}

.grid-actions {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

.link,
.link-btn {
  text-align: center;
  font-weight: bold;
  text-decoration: none;
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  color: white;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 14px;
  border-radius: 8px;
  font-size: 14px;
}

.link:hover,
.link-btn:hover {
  background: #252525;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin: 15px 0;
}

.input-field {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #2a2a2a;
  background: #0f0f0f;
  color: white;
  font-size: 14px;
  box-sizing: border-box;
}

.input-field:focus {
  border-color: #42b883;
  outline: none;
}
.textarea-field {
  resize: vertical;
  min-height: 80px;
}
.row {
  display: flex;
  gap: 12px;
}

.file-field {
  flex: 2;
  background: #0f0f0f;
  padding: 10px;
  border-radius: 8px;
  color: white;
  border: 1px solid #2a2a2a;
}

.album-field {
  flex: 1;
  background: #2a2a2a;
  color: #42b883;
  text-align: center;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  font-size: 14px;
}

.btn {
  border: none;
  padding: 14px;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  font-size: 15px;
  width: 100%;
  background: #42b883;
  transition: background 0.2s;
}

.btn:hover:not(:disabled) {
  opacity: 0.9;
}
.btn:disabled {
  background: #333;
  color: #777;
  cursor: not-allowed;
}

.instagram-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
  padding: 5px 0;
}

.insta-card {
  background: #0f0f0f;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
  display: flex;
  flex-direction: column;
}

.post-img {
  width: 100%;
  height: 280px;
  object-fit: cover;
  display: block;
}

.post-info {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  border-top: 1px solid #2a2a2a;
}

.post-title {
  font-weight: bold;
  font-size: 16px;
  color: white;
}
.post-caption {
  font-size: 13px;
  color: #aaa;
  line-height: 1.4;
}

.status-badge {
  align-self: flex-start;
  font-size: 10px;
  background: rgba(66, 184, 131, 0.2);
  color: #42b883;
  padding: 4px 10px;
  border-radius: 20px;
  font-weight: bold;
  text-transform: uppercase;
}

.search-box {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 15px;
}

.search-actions {
  display: flex;
  gap: 15px;
}

.follow-btn {
  background: #42b883;
}
.unfollow-btn {
  background: #b84242;
}

.notify-box {
  margin-top: 20px;
  padding: 12px;
  background: rgba(66, 184, 131, 0.15);
  border: 1px solid #42b883;
  color: #42b883;
  border-radius: 8px;
  font-weight: bold;
  text-align: center;
}

.notify-box.error {
  background: rgba(184, 66, 66, 0.15);
  border: 1px solid #b84242;
  color: #b84242;
}

.empty {
  color: #aaa;
  padding: 40px;
  text-align: center;
  font-style: italic;
}
.scroll-loader {
  text-align: center;
  color: #42b883;
  padding: 15px;
  font-weight: bold;
  font-style: italic;
}
.hints-section {
  margin: 10px 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.hints-label {
  font-size: 13px;
  color: #888;
  font-weight: 500;
}

.hints-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.hint-tag {
  background: #252525;
  border: 1px solid #3a3a3a;
  color: #42b883;
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 13px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.2s;
}

.hint-tag:hover {
  background: #323232;
  border-color: #42b883;
  transform: translateY(-1px);
}

.hints-loading,
.hints-empty {
  font-size: 13px;
  color: #666;
  font-style: italic;
}
</style>
