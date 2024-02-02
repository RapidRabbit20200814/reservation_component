<script setup>
import { ref, onMounted } from "vue";
import { supabase } from "../../lib/supabaseClient";

// 変数定義
const points = ref([]);

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  displayDetail: Boolean,
  blank: Boolean,
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
  if (index === 0) {
    props.selectedInfo.pointID = "";
    props.selectedInfo.pointNo = "";
    props.selectedInfo.pointName = "";
  } else {
    props.selectedInfo.pointID = points.value[index - 1].point_id;
    props.selectedInfo.pointNo = points.value[index - 1].point_no;
    props.selectedInfo.pointName = points.value[index - 1].point_name;
  }
};
</script>

<template>
  <div v-if="displayDetail" class="form__select-wrap">
    <select id="point" class="form__select-box" v-model="props.selectedInfo.pointID" @change="selectPoint">
      <option disabled value="">選択してください</option>
      <option v-for="(item, index) in points" :value="item.point_id" :key="index">
        {{ item.point_name }}（{{ item.meeting_place }}集合）
      </option>
    </select>
  </div>
  <div v-else class="form__select-wrap">
    <select id="point" class="form__select-box" v-model="props.selectedInfo.pointID" @change="selectPoint">
      <option v-if="blank" value=""></option>
      <option v-else disabled value="">選択してください</option>
      <option v-for="(item, index) in points" :value="item.point_id" :key="index">
        {{ item.point_name }}
      </option>
    </select>
  </div>
</template>
