<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="app-container">
    <RouterView />
  </div>
</template>

<style>
/* Reset CSS biar rapi */
body { 
  margin: 0; 
  padding: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
  background-color: #f4f4f4; 
}
.app-container { 
  width: 100%; 
  min-height: 100vh;
}
</style>