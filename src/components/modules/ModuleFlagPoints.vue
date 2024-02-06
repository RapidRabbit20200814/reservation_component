<script setup>
import { ref, onMounted } from "vue";
import { supabase } from "../../lib/supabaseClient";

// 変数定義
const points = ref([]);

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  blank: Boolean,
});

// --------------------------------------
//  立ち位置情報取得
// --------------------------------------
const getPoints = async () => {
  const { data } = await supabase.from("flag_point").select();
  // deleted_flgがtrue以外の行のみを抽出
  const filteredData = data.filter((row) => row.deleted_flg !== true);
  // pointsにセット
  points.value = filteredData;

  // pointsをpoint_noでソート
  points.value.sort(function (a, b) {
    if (a.point_no < b.point_no) return -1;
    if (a.point_no > b.point_no) return 1;
    return 0;
  });
};

// --------------------------------------
//  初期表示
// --------------------------------------
onMounted(() => {
  getPoints();
});

// --------------------------------------
//  立ち位置選択
// --------------------------------------
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
  <div class="form__select-wrap">
    <select id="point" class="form__select-box" v-model="props.selectedInfo.pointID" @change="selectPoint">
      <option v-if="blank" value=""></option>
      <option v-else disabled value="">選択してください</option>
      <option v-for="(item, index) in points" :value="item.point_id" :key="index">
        {{ item.point_no }}：{{ item.point_name }}
      </option>
    </select>
  </div>
</template>
