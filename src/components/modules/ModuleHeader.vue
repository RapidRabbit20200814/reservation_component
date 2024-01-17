<script setup>
  // 親コンポーネントから値を受け取る
  defineProps({
    title: String,
    subtitle: String
  })
</script>

<template>
  <div class="inner header-inner">
    <h1>{{title}}<span v-if="subtitle" class="subtitle"> - {{ subtitle }} -</span></h1>
    <div class="button-group">
      <a href="#" target="_blank" rel="noopener" class="button button-white button-icon button-small"><img src="../../assets/img/faq.svg" alt="クエスチョンマーク" width="16" height="16">よくあるご質問<span class="pc-only">はこちら</span></a>
      <a href="https://tisetusyou-pta.org/inquiry" target="_blank" rel="noopener" class="button button-white button-icon button-small"><img src="../../assets/img/mail.svg" alt="メールマーク" width="16" height="16">お問い合わせ<span class="pc-only">はこちら</span></a>
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
.subtitle {
  display: block;
  font-size: 1.2rem;
}
.button-group {
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
