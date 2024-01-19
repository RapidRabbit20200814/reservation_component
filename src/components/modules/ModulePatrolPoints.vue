<script setup>
import { ref, onMounted } from "vue";
import { supabase } from "../../lib/supabaseClient";

// 変数定義
const points = ref([]);
let selectedPoint = 0;

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  displayDetail: Boolean,
});

// 防パトエリア情報取得
const getPoints = async () => {
  const { data } = await supabase.from("patrol_point").select();
  points.value = data;

  // pointsをpoint_noでソート
  points.value.sort(function (a, b) {
    if (a.point_no < b.point_no) return -1;
    if (a.point_no > b.point_no) return 1;
    return 0;
  });
};

onMounted(() => {
  getPoints();
});

// 立ち位置選択
const selectPoint = (event) => {
  // 選択された立ち位置のindexを取得
  const index = event.target.selectedIndex;
  // selectedInfoを更新
  props.selectedInfo.pointID = points.value[index - 1].point_id;
  props.selectedInfo.pointNo = points.value[index - 1].point_no;
  props.selectedInfo.pointName = points.value[index - 1].point_name;
};
</script>

<template>
  <select v-if="displayDetail" id="point" v-model="selectedPoint" @change="selectPoint">
    <option disabled value="">選択してください</option>
    <option v-for="(item, index) in points" :value="item.point_id" :key="index">
      {{ item.point_name }}（{{ item.meeting_place }}集合）
    </option>
  </select>
  <select v-else id="point" v-model="selectedPoint" @change="selectPoint">
    <option disabled value="">選択してください</option>
    <option v-for="(item, index) in points" :value="item.point_id" :key="index">
      {{ item.point_name }}
    </option>
  </select>
</template>
