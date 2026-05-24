<script setup lang="ts">
import { ref } from 'vue'

import WelcomeItem from '../components/WelcomeItem.vue'
import DocumentationIcon from '../components/icons/IconDocumentation.vue'
import ToolingIcon from '../components/icons/IconTooling.vue'
import EcosystemIcon from '../components/icons/IconEcosystem.vue'
import SupportIcon from '../components/icons/IconSupport.vue'

const cities = [
  { name: 'Katowice', lat: 50.2649, lng: 19.0238 },
  { name: 'Warszawa', lat: 52.2297, lng: 21.0122 },
  { name: 'Łódź', lat: 51.7592, lng: 19.455 },
  { name: 'Kraków', lat: 50.0647, lng: 19.945 },
]

const selectedCity = ref('Katowice')

const getCity = () => {
  return cities.find(c => c.name === selectedCity.value)
}

// Geo search
const testNearbyRestaurants = async () => {
  try {
    const city = getCity()
    if (!city) return

    const response = await fetch(
      `/api/78716/v1/restaurants/nearby?lat=${city.lat}&lng=${city.lng}&radius=5`
    )

    const data = await response.json()

    console.log(`Restaurants in ${city.name}`, data)

    alert(
      `[Geo Search]\nCity: ${city.name}\nFound: ${data.count} restaurants`
    )
  } catch (error) {
    console.error(error)
    alert('Geo search failed')
  }
}
</script>

<template>
  <div class="labs-container">
    <h1 class="page-title">Лабораторні роботи (ID: 78716)</h1>

    <!-- System -->
    <WelcomeItem>
      <template #icon>
        <SupportIcon />
      </template>
      <template #heading>Статус системи</template>
      <a href="/api/health" target="_blank">Check API Health</a>
    </WelcomeItem>

    <!-- Restaurants -->
    <WelcomeItem>
      <template #icon>
        <DocumentationIcon />
      </template>
      <template #heading>Ресторани</template>
      <a href="/api/78716/v1/restaurants" target="_blank">
        All restaurants
      </a>
    </WelcomeItem>

    <!-- Geo search -->
    <WelcomeItem>
      <template #icon>
        <ToolingIcon />
      </template>

      <template #heading>Geo Search (Haversine + Redis)</template>

      <p>Search restaurants by city</p>

      <select v-model="selectedCity">
        <option v-for="city in cities" :key="city.name">
          {{ city.name }}
        </option>
      </select>

      <button @click="testNearbyRestaurants" class="btn-test">
        Search restaurants
      </button>
    </WelcomeItem>

    <!-- Tasks -->
    <WelcomeItem>
      <template #icon>
        <EcosystemIcon />
      </template>
      <template #heading>Tasks API</template>
      <a href="/api/78716/v1/tasks" target="_blank">
        View tasks
      </a>
    </WelcomeItem>
  </div>
</template>

<style scoped>
.labs-container {
  padding: 2rem;
  max-width: 800px;
}

.page-title {
  font-size: 2rem;
  margin-bottom: 2rem;
}

.btn-test {
  margin-top: 10px;
  padding: 6px 14px;
  background: #42b883;
  color: white;
  border: none;
  cursor: pointer;
}
</style>