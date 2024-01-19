<script setup>
import { ref } from "vue";

import ModuleHeader from "./modules/ModuleHeader.vue";
import ModuleGrades from "./modules/ModuleGrades.vue";
import ModulePatrolPoints from "./modules/ModulePatrolPoints.vue";
import ModulePatrolSchedule from "./modules/ModulePatrolSchedule.vue";
import ModulePatrolReserveDialog from "./modules/ModulePatrolReserveDialog.vue";
import ModulePatrolStorageView from "./modules/ModulePatrolStorageView.vue";

const page = ref("reserve-patrol");

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

// [ModulePatrolSchedule]のインスタンスを保持する ref
const scheduleRef = ref(null);

// [ModuleSchedule]カレンダーの日付クリック
const updateDate = (date) => {
  // モーダルを開く
  const modal = document.querySelector("#reserve-modal");
  modal.showModal();
};

// [ModulePatrolStorageView]予約データ削除
const updateSchedule = () => {
  scheduleRef.value?.getReservation(selectedInfo.value.pointID, selectedInfo.value.year, selectedInfo.value.month);
};
</script>

<template>
  <header>
    <ModuleHeader title="防犯パトロール予約" />
  </header>

  <main>
    <div class="inner">
      <ol class="number-list">
        <li>
          <h2>学年を選んでください</h2>
          <p class="grade-message">来年度の予約をする場合は、来年度の学年を選んでください。</p>
          <ModuleGrades :selectedInfo="selectedInfo" />
        </li>
        <li v-if="selectedInfo.grade">
          <h2>パトロールのエリアを選んでください</h2>
          <ModulePatrolPoints :selectedInfo="selectedInfo" :displayDetail="true" />
        </li>
        <li v-if="selectedInfo.pointID">
          <h2>パトロールを実施する日を選んでください</h2>
          <ModulePatrolSchedule @selectDate="updateDate" :selectedInfo="selectedInfo" :page="page" ref="scheduleRef" />
        </li>
      </ol>

      <ModulePatrolReserveDialog :selectedInfo="selectedInfo" />
      <ModulePatrolStorageView @delete="updateSchedule" :selectedInfo="selectedInfo" />

      <div class="button-area-lg button-area-flex">
        <router-link :to="{ name: 'Menu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
        <router-link :to="{ name: 'FlagReserve' }" class="button button-next button-white button-small"
          >朝旗の予約へ</router-link
        >
      </div>
    </div>
  </main>
</template>

<style scoped>
h2 {
  font-size: 1.2rem;
}
.grade-message {
  margin-bottom: 1rem;
}
</style>
