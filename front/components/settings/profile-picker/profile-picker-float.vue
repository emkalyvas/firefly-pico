<template>
  <div>
    <van-floating-bubble v-model:offset="position" axis="y" magnetic="x" @click="isListVisible = true" :gap="0" class="profile-floating-button">
      <icon-nut size="20" color="#fff" stroke="1.6" />
    </van-floating-bubble>

    <van-popup v-model:show="isListVisible" position="bottom" :style="{ height: '40%' }">
      <profile-picker-list></profile-picker-list>
    </van-popup>
  </div>
</template>

<script setup>
import { IconNut } from '@tabler/icons-vue'

const appStore = useAppStore()
const isListVisible = ref(false)

// Ignore the X coordinate as the window can be resized
const position = computed({
  get: () => appStore.profileFloatButtonPosition,
  set: (value) => {
    appStore.profileFloatButtonPosition = { y: value.y }
  },
})
</script>
