<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const title = ref('')
const caption = ref('')
const albumNumber = ref('78716')
const fileInput = ref<HTMLInputElement | null>(null)

const loading = ref(false)
const uploading = ref(false)
const photos = ref<any[]>([])

const nextCursor = ref<string | null>(null)

const getCorrectImageUrl = (rawUrl: string) => {
  if (!rawUrl) return ''
  if (rawUrl.includes('127.0.0.1') && !rawUrl.includes(':8080')) {
    const parts = rawUrl.split('/storage/')
    return parts.length > 1 ? `/storage/${parts[1]}` : rawUrl
  }
  return rawUrl
}
const fetchPhotos = async () => {
  loading.value = true
  try {
    const res = await fetch(`/api/78716/v1/feed?limit=4`)
    const json = await res.json()
    const rawPhotos = json.data?.data || []

    photos.value = rawPhotos.map((photo: any) => ({
      ...photo,
      image_url: getCorrectImageUrl(photo.image_url || `http://localhost:8080/storage/${photo.image_path}`),
    }))

    nextCursor.value = json.data?.next_cursor || null
  } catch (err) {
    console.error('Помилка завантаження стрічки:', err)
  } finally {
    loading.value = false
  }
}
const loadMore = async () => {
  if (!nextCursor.value || loading.value) return
  loading.value = true
  try {
    const res = await fetch(`/api/78716/v1/feed?limit=4&cursor=${nextCursor.value}`)
    const json = await res.json()

    const nextPhotos = json.data?.data || []
    const mappedNext = nextPhotos.map((photo: any) => ({
      ...photo,
      image_url: getCorrectImageUrl(photo.image_url || `http://localhost:8080/storage/${photo.image_path}`),
    }))
    photos.value = [...photos.value, ...mappedNext]
    nextCursor.value = json.data?.next_cursor || null
  } catch (err) {
    console.error('Помилка довантаження стрічки:', err)
  } finally {
    loading.value = false
  }
}
const handleScroll = () => {
  const { scrollTop, scrollHeight, clientHeight } = document.documentElement
  if (scrollTop + clientHeight >= scrollHeight - 150) {
    if (nextCursor.value && !loading.value) {
      loadMore()
    }
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

    const res = await fetch('/api/78716/v1/photos', {
      method: 'POST',
      body: formData,
    })

    const data = await res.json()

    if (data.success) {
      title.value = ''
      caption.value = ''
      if (fileInput.value) fileInput.value.value = ''
      await fetchPhotos()
    }
  } catch (err) {
    console.error(err)
  } finally {
    uploading.value = false
  }
}

onMounted(() => {
  fetchPhotos()
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="page">
    <div class="grid-actions">
      <a class="card link" href="/api/health" target="_blank">🟢 Health</a>
      <a class="card link" href="/api/78716/v1/photos" target="_blank">📸 Photos API</a>
      <button class="card link-btn" @click="fetchPhotos">🔄 Refresh Feed</button>
    </div>

    <div class="card">
      <h2>Upload New Post</h2>

      <div class="form-group">
        <input type="text" v-model="title" placeholder="Photo Title" required class="input-field" />
        <textarea
          v-model="caption"
          placeholder="Write a caption..."
          class="input-field textarea-field"
        ></textarea>

        <div class="row">
          <input type="file" ref="fileInput" accept="image/*" class="file-field" />
          <input type="text" v-model="albumNumber" disabled class="album-field" />
        </div>
      </div>

      <button class="btn" @click="handleUpload" :disabled="uploading">
        {{ uploading ? 'Processing File...' : 'Share Post' }}
      </button>
    </div>

    <div class="card">
      <h2>Media Feed (News Service)</h2>

      <div class="list-feed">
        <div v-if="loading && photos.length === 0" class="empty">Loading assets from Redis...</div>
        <div v-else-if="photos.length === 0" class="empty">No media found in system grid.</div>

        <div v-else class="instagram-grid">
          <div v-for="p in photos" :key="p.id" class="insta-card">
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
              <span class="status-badge">{{ p.processing_status }}</span>
            </div>
          </div>
        </div>

        <div v-if="loading && photos.length > 0" class="scroll-loader">
          Loading more posts...
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

.page {
  width: 80vw !important;
  max-width: none !important;
  margin: 0 !important;
  padding: 30px;
  background: #0f0f0f;
  min-height: 100vh;
  color: white;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  box-sizing: border-box;
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

.card {
  background: #1a1a1a;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
}

.card h2 {
  font-size: 18px;
  margin-bottom: 15px;
  border-bottom: 1px solid #2a2a2a;
  padding-bottom: 8px;
  color: #42b883;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin: 15px 0;
}

.input-field {
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #2a2a2a;
  background: #0f0f0f;
  color: white;
  font-size: 14px;
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
  background: #42b883;
  border: none;
  padding: 14px;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  font-size: 15px;
  width: 100%;
  transition: background 0.2s;
}

.btn:hover:not(:disabled) {
  background: #359469;
}

.btn:disabled {
  background: #333;
  color: #777;
  cursor: not-allowed;
}

.list-feed {
  padding-right: 4px;
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
  letter-spacing: 0.5px;
  margin-top: 6px;
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
</style>
