<script setup>
import { ref } from 'vue';

import ModuleHeader from './modules/ModuleHeader.vue';
import ModuleFlagPoints from './modules/ModuleFlagPoints.vue';
import ModuleCalendar from './modules/ModuleCalendar.vue';
import ModuleReserveDialog from './modules/ModuleReserveDialog.vue';
import ModuleStorageView from "./modules/ModuleStorageView.vue";

const page = ref("reserve-flag-support");

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
  number: ""
});

// [ModuleCalendar]のインスタンスを保持する ref
const calendarRef = ref(null);

// [ModuleCalendar]カレンダーの日付クリック
const updateDate = (date) => {
  // モーダルを開く
  const modal = document.querySelector("#reserve-modal");
  modal.showModal();
}

// [ModuleStorageView]予約データ削除
// [ModuleReserveDialog]データ重複
const updateCalendar = () => {
  calendarRef.value?.getReservation(selectedInfo.value.pointID, selectedInfo.value.year, selectedInfo.value.month);
}

</script>

<template>
  <header>
    <ModuleHeader title="朝のあいさつ旗当番予約" subtitle="つどさぽ" />
  </header>

  <main>
    <div class="inner">
      <ol class="number-list">
        <li>
          <h2>旗当番の立ち位置を選んでください</h2>
          <ModuleFlagPoints :selectedInfo="selectedInfo" />
        </li>
        <li v-if="selectedInfo.pointID">
          <h2>旗当番を実施する日を選んでください</h2>
          <ModuleCalendar @selectDate="updateDate"  :selectedInfo="selectedInfo" :page="page" ref="calendarRef" />
        </li>
      </ol>

      <ModuleReserveDialog @conflict="updateCalendar" :selectedInfo="selectedInfo" />
      <ModuleStorageView @delete="updateCalendar" :selectedInfo="selectedInfo" />

      <div class="button-area-lg">
        <router-link :to="{name:'Menu'}" class="button button-return button-white button-small">メニューへ戻る</router-link>
      </div>
    </div>
  </main>
</template>

<style scoped>
h2 {
  font-size: 1.2rem;
}
</style>
