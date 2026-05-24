<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="main-app-layout">
    <header class="sidebar-header">
      <img alt="Vue logo" class="logo" src="@/assets/logo.svg" width="80" height="80" />
      <div class="wrapper">
        <nav class="sidebar-nav">
          <RouterLink to="/">Home</RouterLink>
          <RouterLink to="/about">About</RouterLink>
          <RouterLink to="/labs">Labs Endpoints</RouterLink>
          <RouterLink to="/photos">Instagram Module</RouterLink>
          <RouterLink to="/feed">News Feed & Follows</RouterLink>
        </nav>
      </div>
    </header>

    <main class="content-body">
      <RouterView />
    </main>
  </div>
</template>

<style>
/* 🌍 Global browser resets */
#app {
  max-width: 100vw !important;
  margin: 0 !important;
  padding: 0 !important;
  display: block !important;
}

body {
  margin: 0;
  padding: 0;
  background: #0f0f0f;
}

.main-app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: #0f0f0f;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  width: 100%;
}

.sidebar-header {
  background: #111111;
  padding: 16px 20px;
  border-bottom: 1px solid #2a2a2a;
  display: flex;
  align-items: center;
  gap: 20px;
  z-index: 100;
}

.sidebar-nav {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.sidebar-nav a {
  display: inline-block;
  padding: 10px 16px;
  background: #1e1e1e !important;
  color: #b3b3b3 !important;
  text-decoration: none !important;
  border-radius: 8px !important;
  border: 1px solid #2e2e2e !important;
  font-size: 14px !important;
  font-weight: 600 !important;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.sidebar-nav a:hover {
  background: #2a2a2a !important;
  color: #ffffff !important;
  border-color: #4a4a4a !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.sidebar-nav a.router-link-exact-active {
  color: #ffffff !important;
  background: #42b883 !important;
  border-color: #42b883 !important;
  box-shadow: 0 4px 12px rgba(66, 184, 131, 0.3) !important;
}

.content-body {
  flex: 1;
  width: 100%;
  box-sizing: border-box;
}

/* 💻 UNIVERSAL DESKTOP GRID EXECUTION */
@media (min-width: 1024px) {
  .main-app-layout {
    flex-direction: row;
  }

  .sidebar-header {
    width: 280px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    border-bottom: none;
    border-right: 1px solid #222;
    padding: 40px 24px;
    box-sizing: border-box;
    background: #111111;
  }

  .logo {
    margin-bottom: 20px;
    filter: drop-shadow(0 4px 8px rgba(66, 184, 131, 0.2));
  }

  .sidebar-nav {
    flex-direction: column;
    width: 100%;
    gap: 10px;
    margin-top: 10px;
  }

  .sidebar-nav a {
    display: block !important;
    width: 100% !important;
    box-sizing: border-box !important;
    text-align: center;
  }

  .content-body {
    margin-left: 280px; /* Space for fixed sidebar */
    width: calc(100% - 280px);
    max-width: calc(100% - 280px);
    display: flex;
  }

  /* 🛠️ UNIVERSAL FIX FOR ALL inner ".page" components */
  .content-body > div,
  .content-body .page {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 40px !important;
    box-sizing: border-box !important;
    flex: 1;
  }
}
</style>
