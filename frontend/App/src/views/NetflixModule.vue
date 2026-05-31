<script setup lang="ts">
import { ref, onMounted } from 'vue'

// Змінні для форми додавання відео
const videoTitle = ref('')
const videoDescription = ref('')
const videoGenre = ref('Games')
const videoDuration = ref(30)
const videoUrl = ref('')
const albumNumber = ref('78716')

// Список доступних жанрів для автопідстановки
const availableGenres = ref(['Games', 'Cooking', 'Sci-Fi', 'Comedy', 'Horror', 'Drama', 'Action'])

// Стан інтерфейсу та завантаження
const loadingVideos = ref(false)
const loadingRecs = ref(false)
const loadingHistory = ref(false)
const processingVideo = ref(false)

// Масиви даних з нашого API
const videos = ref<any[]>([])
const recommendations = ref<any[]>([])
const watchHistory = ref<any[]>([])

// Поточний фільтр жанру для каталогу
const selectedGenreFilter = ref('')

// 1. Отримання всього каталогу відео з пагінацією/фільтрацією
const fetchVideos = async () => {
  loadingVideos.value = true
  try {
    let url = `/api/78716/v1/videos`
    if (selectedGenreFilter.value) {
      url += `?genre=${selectedGenreFilter.value}`
    }
    const res = await fetch(url)
    const json = await res.json()
    videos.value = json.data || []
  } catch (err) {
    console.error('Помилка завантаження каталогу відео:', err)
  } finally {
    loadingVideos.value = false
  }
}

// 2. Отримання рекомендацій (кешується в Redis на 60 сек)
const fetchRecommendations = async () => {
  loadingRecs.value = true
  try {
    const res = await fetch(`/api/78716/v1/recommendations`)
    const json = await res.json()
    recommendations.value = json.data || []
  } catch (err) {
    console.error('Помилка завантаження рекомендацій:', err)
  } finally {
    loadingRecs.value = false
  }
}

// 3. Отримання списку "Продовжити перегляд"
const fetchWatchHistory = async () => {
  loadingHistory.value = true
  try {
    const res = await fetch(`/api/78716/v1/continue-watching`)
    const json = await res.json()
    watchHistory.value = json.data || []
  } catch (err) {
    console.error('Помилка завантаження історії:', err)
  } finally {
    loadingHistory.value = false
  }
}

// 4. Створення нового відео (каталогу фільмів)
const handleCreateVideo = async () => {
  if (!videoTitle.value || !videoUrl.value) {
    alert('Будь ласка, вкажіть назву відео та посилання.')
    return
  }

  processingVideo.value = true
  try {
    const res = await fetch('/api/78716/v1/videos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        title: videoTitle.value,
        description: videoDescription.value,
        genre: videoGenre.value,
        duration_minutes: videoDuration.value,
        video_url: videoUrl.value,
        album_number: albumNumber.value,
      }),
    })

    if (res.status === 201 || res.status === 200) {
      // Додаємо новий жанр у список автопідстановки, якщо його там ще немає
      if (videoGenre.value && !availableGenres.value.includes(videoGenre.value)) {
        availableGenres.value.push(videoGenre.value)
      }

      videoTitle.value = ''
      videoDescription.value = ''
      videoUrl.value = ''
      await fetchVideos() // Оновлюємо список фільмів
    }
  } catch (err) {
    console.error('Помилка додавання відео:', err)
  } finally {
    processingVideo.value = false
  }
}

// 5. Симуляція перегляду відео (Клік на відео для оновлення прогресу перегляду)
const simulateWatch = async (videoId: number, markCompleted: boolean = false) => {
  try {
    const progress = markCompleted ? 0 : Math.floor(Math.random() * 1000) + 100
    await fetch('/api/78716/v1/watch-history', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({
        video_id: videoId,
        progress_seconds: progress,
        completed: markCompleted,
      }),
    })

    // Миттєво оновлюємо історію та двигун рекомендацій
    await fetchWatchHistory()
    await fetchRecommendations()
  } catch (err) {
    console.error('Помилка синхронізації перегляду:', err)
  }
}

// Завантаження всіх даних під час старту компонента
onMounted(() => {
  fetchVideos()
  fetchRecommendations()
  fetchWatchHistory()
})
</script>

<template>
  <div class="page">
    <!-- Навігаційні швидкі посилання та екшени -->
    <div class="grid-actions">
      <a class="card link" href="/api/78716/v1/videos" target="_blank">🎬 Videos Catalog API</a>
      <a class="card link" href="/api/78716/v1/recommendations" target="_blank"
        >🧠 Redis Recs API</a
      >
      <button
        class="card link-btn"
        @click="
          () => {
            fetchVideos()
            fetchRecommendations()
            fetchWatchHistory()
          }
        "
      >
        🔄 Sync All Platform Data
      </button>
    </div>

    <!-- ФОРМА: Додати нове відео до стрімінгового сервісу -->
    <div class="card">
      <h2>Upload New Video Meta (Netflix Grid)</h2>

      <div class="form-group">
        <input
          type="text"
          v-model="videoTitle"
          placeholder="Video Title"
          required
          class="input-field"
        />
        <textarea
          v-model="videoDescription"
          placeholder="Write plot description..."
          class="input-field textarea-field"
        ></textarea>

        <input
          type="text"
          v-model="videoUrl"
          placeholder="Video Stream URL (e.g., https://youtube.com...)"
          required
          class="input-field"
        />

        <div class="row row-layout">
          <!-- Поле жанру з інтелектуальною автопідстановкою та випадаючим списком -->
          <div class="genre-input-wrapper">
            <input
              type="text"
              v-model="videoGenre"
              list="genre-suggestions"
              placeholder="Type or select Genre..."
              class="input-field dynamic-genre-field"
            />
            <datalist id="genre-suggestions">
              <option v-for="genre in availableGenres" :key="genre" :value="genre"></option>
            </datalist>
          </div>

          <input
            type="number"
            v-model="videoDuration"
            placeholder="Duration (min)"
            min="1"
            class="input-field duration-input"
          />
          <input type="text" v-model="albumNumber" disabled class="album-field" />
        </div>
      </div>

      <button class="btn" @click="handleCreateVideo" :disabled="processingVideo">
        {{ processingVideo ? 'Publishing to Media DB...' : 'Add Video to Catalog' }}
      </button>
    </div>

    <!-- БЛОК 1: Продовжити перегляд (Watch History) -->
    <div class="card" v-if="watchHistory.length > 0">
      <h2 style="color: #ff4a5a">🍿 Continue Watching (Unfinished Media)</h2>
      <div class="netflix-horizontal-scroll">
        <div v-for="h in watchHistory" :key="h.id" class="netflix-mini-card">
          <div class="mini-card-body">
            <div class="video-title">{{ h.video?.title }}</div>
            <div class="video-genre-tag">{{ h.video?.genre }}</div>
            <div class="progress-container">
              <span class="progress-text"
                >Stopped at: {{ Math.floor(h.progress_seconds / 60) }} min</span
              >
              <button class="btn-mini-complete" @click="simulateWatch(h.video_id, true)">
                ✓ Finish
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- БЛОК 2: Розумні рекомендації (Кешуються в Redis на 60 сек) -->
    <div class="card">
      <h2>🧠 Personalized Smart Recommendations (Powered by Redis Cache)</h2>
      <div class="list-feed">
        <div v-if="loadingRecs" class="empty">
          Analyzing watch patterns and retrieving from Redis...
        </div>
        <div v-else-if="recommendations.length === 0" class="empty">
          No recommendations available. Watch some videos first to train the engine!
        </div>

        <div v-else class="instagram-grid">
          <div
            v-for="rec in recommendations"
            :key="rec.id"
            class="insta-card recommendation-highlight"
          >
            <div class="post-info">
              <div class="video-header-row">
                <span class="post-title">{{ rec.title }}</span>
                <span class="genre-badge">{{ rec.genre }}</span>
              </div>
              <div class="post-caption">{{ rec.description || 'No plot overview available.' }}</div>
              <div class="video-footer-row">
                <span class="status-badge">🕒 {{ rec.duration_minutes }} Mins</span>
                <button class="btn-watch" @click="simulateWatch(rec.id, false)">
                  ▶ Play Stream
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- БЛОК 3: Повний каталог медіатеки -->
    <div class="card">
      <div class="catalog-header">
        <h2>📂 Global Video Catalog (Paginated Grid)</h2>
        <div class="filter-box">
          <label>Filter Genre: </label>
          <select
            v-model="selectedGenreFilter"
            @change="fetchVideos"
            class="input-field select-filter"
          >
            <option value="">All Genres</option>
            <option v-for="genre in availableGenres" :key="genre" :value="genre">
              {{ genre }}
            </option>
          </select>
        </div>
      </div>

      <div class="list-feed">
        <div v-if="loadingVideos && videos.length === 0" class="empty">
          Querying PostgreSQL database clusters...
        </div>
        <div v-else-if="videos.length === 0" class="empty">
          The platform catalog is currently empty.
        </div>

        <div v-else class="instagram-grid">
          <div v-for="v in videos" :key="v.id" class="insta-card">
            <div class="post-info">
              <div class="video-header-row">
                <span class="post-title">{{ v.title }}</span>
                <span class="genre-badge gray-badge">{{ v.genre }}</span>
              </div>
              <div class="post-caption">{{ v.description || 'No description provided.' }}</div>
              <div class="video-footer-row">
                <span class="status-badge">Album: {{ v.album_number }}</span>
                <button class="btn-watch" @click="simulateWatch(v.id, false)">▶ Watch</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Глобальні стилі сторінки */
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

.row-layout {
  display: flex;
  gap: 12px;
  align-items: center;
}

.genre-input-wrapper {
  flex: 2;
  position: relative;
}

.dynamic-genre-field {
  width: 100%;
  box-sizing: border-box;
}

.duration-input {
  flex: 1;
  background: #0f0f0f;
  color: white;
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
  padding: 12px 0;
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
  grid-template-columns: repeat(3, 1fr);
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

.post-info {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
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
}

.empty {
  color: #aaa;
  padding: 40px;
  text-align: center;
  font-style: italic;
}

/* Специфічні стилі модулів */
.catalog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #2a2a2a;
  margin-bottom: 15px;
}

.catalog-header h2 {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.select-filter {
  padding: 6px 12px;
  background: #0f0f0f;
  margin-left: 8px;
}

.filter-box {
  font-size: 14px;
  color: #aaa;
}

.video-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.genre-badge {
  font-size: 11px;
  background: rgba(66, 184, 131, 0.2);
  color: #42b883;
  padding: 3px 8px;
  border-radius: 4px;
  font-weight: bold;
}

.gray-badge {
  background: #2a2a2a;
  color: #ccc;
}

.video-footer-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
}

.btn-watch {
  background: #42b883;
  color: white;
  border: none;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  font-size: 13px;
  transition: opacity 0.2s;
}

.btn-watch:hover {
  opacity: 0.9;
}

.recommendation-highlight {
  border: 1px solid rgba(66, 184, 131, 0.4);
  box-shadow: 0 0 10px rgba(66, 184, 131, 0.1);
}

.netflix-horizontal-scroll {
  display: flex;
  gap: 15px;
  overflow-x: auto;
  padding-bottom: 10px;
}

.netflix-mini-card {
  min-width: 240px;
  background: #0f0f0f;
  border: 1px solid #2a2a2a;
  border-left: 4px solid #ff4a5a;
  border-radius: 8px;
  padding: 12px;
}

.mini-card-body {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.video-title {
  font-weight: bold;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.video-genre-tag {
  font-size: 11px;
  color: #888;
}

.progress-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
}

.progress-text {
  font-size: 12px;
  color: #aaa;
}

.btn-mini-complete {
  background: transparent;
  border: 1px solid #ff4a5a;
  color: #ff4a5a;
  padding: 2px 6px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 11px;
}

.btn-mini-complete:hover {
  background: #ff4a5a;
  color: white;
}
</style>
