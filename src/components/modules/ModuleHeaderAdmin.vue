<script setup>
  // 親コンポーネントから値を受け取る
  defineProps({
    title: String
  })
</script>

<template>
  <div class="inner header-inner">
    <h1>{{title}}</h1>
    <router-link :to="{name:'Menu'}" class="button button-white button-small">保護者用メニューへ</router-link>
  </div>
</template>

<style scoped>
.header-inner {
  padding-block: 1rem 2rem;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}
@media (min-width: 768px) {
}
</style>
