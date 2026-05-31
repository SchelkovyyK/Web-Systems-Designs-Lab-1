<script setup lang="ts">
import { ref } from 'vue'

const cities = [
  { name: 'Katowice', key: 'katowice', lat: 50.2649, lng: 19.0238 },
  { name: 'Warszawa', key: 'warszawa', lat: 52.2297, lng: 21.0122 },
  { name: 'Łódź', key: 'lodz', lat: 51.7592, lng: 19.455 },
  { name: 'Kraków', key: 'krakow', lat: 50.0647, lng: 19.945 },
]

const selectedCity = ref(cities[0])
const radius = ref(5)

const loading = ref(false)

const lastData = ref<any[]>([])
const resultCount = ref<number | null>(null)

const page = ref(1)
const lastPage = ref(1)

const testNearbyRestaurants = async () => {
  loading.value = true

  try {
    page.value = 1
    lastData.value = []

    const c = selectedCity.value

    const res = await fetch(
      `/api/78716/v1/restaurants/nearby?city=${c.key}&lat=${c.lat}&lng=${c.lng}&radius=${radius.value}&page=1`,
    )

    const data = await res.json()

    lastData.value = data.data || []
    resultCount.value = data.total ?? data.data?.length ?? 0
    lastPage.value = data.last_page ?? 1

    console.log('RESULT:', data)
  } catch (err) {
    console.error(err)
    alert('Request failed')
  } finally {
    loading.value = false
  }
}

const loadMore = async () => {
  if (page.value >= lastPage.value) return

  loading.value = true

  try {
    page.value++

    const c = selectedCity.value

    const res = await fetch(
      `/api/78716/v1/restaurants/nearby?city=${c.key}&lat=${c.lat}&lng=${c.lng}&radius=${radius.value}&page=${page.value}`,
    )

    const data = await res.json()

    lastData.value = [...lastData.value, ...(data.data || [])]

    console.log('PAGE:', page.value, data)
  } catch (err) {
    console.error(err)
    alert('Load more failed')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <h1 class="title">Geo Restaurant System</h1>
    <div class="grid">
      <a class="card link" href="/api/health" target="_blank">Health</a>
      <a class="card link" href="/api/78716/v1/restaurants" target="_blank">Restaurants</a>
      <a class="card link" href="/api/78716/v1/tasks" target="_blank">Tasks</a>
    </div>
    <div class="card">
      <h2>Search</h2>

      <div class="row">
        <select v-model="selectedCity">
          <option v-for="c in cities" :key="c.key" :value="c">
            {{ c.name }}
          </option>
        </select>

        <input type="number" v-model="radius" min="1" max="20" />
      </div>

      <button class="btn" @click="testNearbyRestaurants">
        {{ loading ? 'Loading...' : 'Search' }}
      </button>

      <div v-if="resultCount !== null" class="result">
        Found: <b>{{ resultCount }}</b> restaurants
      </div>
    </div>
    <div class="card">
      <h2>Results</h2>

      <div class="list">
        <div v-if="lastData.length === 0" class="empty">No data yet</div>

        <div v-for="r in lastData" :key="r.id" class="item">
          <div class="name">{{ r.name }}</div>
          <div class="meta">{{ r.category }} • {{ Number(r.distance_km).toFixed(2) }} km</div>
        </div>
      </div>
      <button v-if="page < lastPage" class="btn load-more" @click="loadMore">
        Load more (page {{ page }}/{{ lastPage }})
      </button>
    </div>
  </div>
</template>

<style scoped>
.page {
 
  margin: 0 auto; 
  
  background: #0f0f0f;
  min-height: 100vh;
  color: white;
  font-family: Arial, sans-serif;
}

.title {
  font-size: 28px;
  margin-bottom: 10px;
}
.grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 20px;
}

.link {
  text-align: center;
  font-weight: bold;
  text-decoration: none;
}
.card {
  background: #1a1a1a;
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}
.row {
  display: flex;
  gap: 10px;
  margin: 10px 0;
}

select,
input {
  padding: 8px;
  border-radius: 6px;
  border: none;
}
.btn {
  background: #42b883;
  border: none;
  padding: 10px 14px;
  color: white;
  border-radius: 8px;
  cursor: pointer;
}

.btn:hover {
  background: #2f8f6a;
}
.list {
  max-height: 200px;
  overflow-y: auto;
  padding-right: 6px;
}
.item {
  padding: 10px;
  border-bottom: 1px solid #2a2a2a;
}

.name {
  font-weight: bold;
}

.meta {
  font-size: 12px;
  opacity: 0.7;
}
.empty {
  opacity: 0.6;
  padding: 10px;
}
.load-more {
  margin-top: 10px;
  width: 100%;
}
.list::-webkit-scrollbar {
  width: 8px;
}

.list::-webkit-scrollbar-track {
  background: #111;
  border-radius: 10px;
}

.list::-webkit-scrollbar-thumb {
  background: #42b883;
  border-radius: 10px;
  border: 2px solid #111;
}

.list::-webkit-scrollbar-thumb:hover {
  background: #2f8f6a;
}
@media (min-width: 1024px) {
  .page {
    margin-left: 320px !important;
    max-width: calc(100% - 340px) !important;
  }
}
</style>
