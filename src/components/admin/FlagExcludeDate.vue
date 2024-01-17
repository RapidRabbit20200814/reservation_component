<script setup>
import { ref } from 'vue';

import ModuleHeaderAdmin from '../modules/ModuleHeaderAdmin.vue';
import ModuleCalendar from '../modules/ModuleCalendar.vue';

const page = ref("exclude-date");

</script>

<template>
  <header>
    <ModuleHeaderAdmin title="朝旗／除外日設定" />
  </header>

  <main>
    <div class="inner">
      <p class="lead">
        年間行事予定に基づき、土日祝、長期休暇など朝旗の除外日を設定してください。<br>曜日列の☑をつけると、まとめて除外日にできます。<br>設定後、朝旗の予約画面で除外日が「ー」（予約不可）となっていることを確認してください。
      </p>
      <ModuleCalendar :page="page" />

      <div class="button-area-lg">
        <router-link :to="{name:'AdminMenu'}" class="button button-return button-white button-small">メニューへ戻る</router-link>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* リード文 */
.lead {
  margin-bottom: 2rem;
}

</style>
