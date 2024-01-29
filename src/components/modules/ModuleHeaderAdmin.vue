<script setup>
// 親コンポーネントから値を受け取る
defineProps({
  title: String,
});
</script>

<template>
  <div class="inner header-inner">
    <h1>{{ title }}</h1>
    <div class="button-wrapper">
      <router-link :to="{ name: 'AdminMenu' }" class="button button-white button-small">管理者用メニューへ</router-link>
      <router-link :to="{ name: 'Menu' }" class="button button-white button-small">保護者用メニューへ</router-link>
    </div>
  </div>
</template>

<style scoped>
.header-inner {
  padding-block: 1rem 2rem;
  display: flex;
  flex-direction: column-reverse;
  gap: 1rem;
}
.button-wrapper {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  align-items: center;
  gap: 0.5rem;
}
@media (min-width: 768px) {
  .header-inner {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}
</style>
