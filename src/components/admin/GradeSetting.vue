<script setup>
import { ref } from "vue";

import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";
import ModuleYears from "../modules/ModuleYears.vue";
import ModuleGradeInfo from "../modules/ModuleGradeInfo.vue";

let selectedYear = ref(0);

const selectYear = (year) => {
  selectedYear.value = year;
};
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="学年情報設定" />
  </header>

  <main>
    <div class="inner-s">
      <p class="lead">新年度が始まるまでに初期設定をしてください。<br />児童数は後からでも変更可能です。</p>
      <ModuleYears :numberOfYears="10" :currentYearFlg="true" @selectedYear="selectYear" />
      <ModuleGradeInfo :year="selectedYear" />
    </div>
    <div class="inner">
      <div class="button-area-lg button-area-flex">
        <router-link :to="{ name: 'AdminMenu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
        <router-link :to="{ name: 'FlagExcludeDate' }" class="button button-next button-white button-small"
          >【朝旗】除外日設定へ</router-link
        >
      </div>
    </div>
  </main>
</template>

<style scoped></style>
