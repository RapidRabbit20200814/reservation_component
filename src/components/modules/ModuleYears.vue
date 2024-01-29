<script setup>
import { ref, defineEmits, onMounted } from "vue";

// 変数定義
const years = ref([]);
let startYear = 0;
let selectedYear = ref(0);

// 親コンポーネントから受け取る値を定義
let props = defineProps({
  startYear: Number, // 開始年度
  numberOfYears: Number, // 年数
  currentYearFlg: Boolean, // 現在年度を初期表示する/しない
  selectedYear: Number, // 選択された年度
});
// 親コンポーネントへ渡す値を定義
const emits = defineEmits();

// --------------------------------------
//  初期表示
// --------------------------------------
onMounted(() => {
  // 現在の年度を取得
  let currentYear = String(new Date().getFullYear());
  const currentMonth = new Date().getMonth() + 1;
  // 1~3月の場合は-1年
  if (currentMonth >= 1 && currentMonth <= 3) {
    currentYear = String(Number(currentYear) - 1);
  }
  // startYearが空の場合は現在の年度をセット
  if (!props.startYear) {
    startYear = Number(currentYear);
  } else if (props.startYear == "1") {
    // startYearが1の場合は現在の年度+1をセット
    startYear = Number(currentYear) + 1;
  } else {
    startYear = Number(props.startYear);
  }
  // startYearからnumberOfYears分の配列を作成
  const numberOfYears = Number(props.numberOfYears);
  for (let i = 0; i < numberOfYears; i++) {
    years.value.push(String(startYear + i));
  }

  // 現在年度を初期表示する場合
  if (props.currentYearFlg) {
    selectedYear.value = currentYear;
    // 親コンポーネントにカスタムイベントを伝える
    emits("selectedYear", Number(selectedYear.value));
  }
});

// --------------------------------------
//  年度選択
// --------------------------------------
const onYearChange = () => {
  // 親コンポーネントにカスタムイベントを伝える
  emits("selectedYear", Number(selectedYear.value));
};
</script>

<template>
  <div class="form__select-wrap">
    <select id="year" class="form__select-box" v-model="selectedYear" @change="onYearChange">
      <option v-for="(label, index) in years" :value="label" :key="index">{{ label }}年度</option>
    </select>
  </div>
</template>
