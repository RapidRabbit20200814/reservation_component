<script setup>
import { ref } from "vue";

import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";
import ModulePatrolSchedule from "../modules/ModulePatrolSchedule.vue";

const page = ref("exclude-date");

const selectedInfo = ref({
  id: "",
  pointID: "",
  pointNo: "",
  pointName: "",
  year: "",
  month: "",
  day: "",
  weekDay: "",
  grade: "",
  class: "",
  number: "",
});
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="防パト／除外日設定" />
  </header>

  <main>
    <div class="inner">
      <p class="lead">
        年間行事予定に基づき、防パトの除外日を設定してください。<br />設定後、防パトの予約画面で除外日が「ー」となっていることを確認してください。
      </p>
      <ModulePatrolSchedule :selectedInfo="selectedInfo" :page="page" />

      <div class="button-area-lg">
        <router-link :to="{ name: 'AdminMenu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
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
