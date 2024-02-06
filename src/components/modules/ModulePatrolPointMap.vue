<script setup>
import { ref, onMounted, defineEmits, watch } from "vue";
import { supabase } from "../../lib/supabaseClient";

// 変数定義
const points = ref([]);

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  page: String,
});

// 親コンポーネントへ渡す値を定義
const emits = defineEmits();

// 立ち位置情報取得
const getPoints = async () => {
  const { data } = await supabase.from("patrol_point").select();
  // pointsにセット
  points.value = data;
};

onMounted(() => {
  getPoints();
});

// 立ち位置選択
const selectPoint = (event) => {
  // 選択された立ち位置の情報を取得
  const targetSpan = event.target.querySelector("span");
  const targetNo = targetSpan.innerHTML;
  const filteredData = points.value.filter((row) => row.point_no == targetNo);
  props.selectedInfo.pointID = filteredData[0].point_id;
  props.selectedInfo.pointNo = filteredData[0].point_no;
  props.selectedInfo.pointName = filteredData[0].point_name;
  // 呼び出し元にカスタムイベントを伝える
  emits("select", true);
};

// --------------------------------------
//  監視（point_noが変更されたら再読み込み）
// --------------------------------------
watch(
  () => props.selectedInfo.pointNo,
  (newValue, oldValue) => {
    if (newValue === "") {
      getPoints();
    }
  }
);
</script>

<template>
  <figure class="map-wrapper" :class="props.page">
    <img src="../../assets/img/patrol-area-map.png" alt="防パトのエリアマップ" width="1400" height="1800" class="map" />
    <div class="map-point-wrapper">
      <button
        type="button"
        class="map-point"
        v-for="(item, index) in points"
        :key="index"
        :class="['map-point' + item.point_no, { active: item.point_no === props.selectedInfo.pointNo }]"
        @click="selectPoint"
      >
        <span class="map-point__no">{{ item.point_no }}</span>
      </button>
    </div>
  </figure>
</template>

<style scoped>
.map-wrapper {
  position: relative;
  height: fit-content;
}
.map {
  width: 100%;
  height: auto;
  aspect-ratio: 1400 / 1800;
}
.map-point-wrapper {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  width: 100%;
  height: 100%;
}
.map-point {
  position: absolute;
}
.map-point1 {
  top: 7%;
  left: 8%;
  width: 48%;
  height: 39%;
}
.map-point2 {
  top: 6%;
  right: 3%;
  width: 40%;
  height: 36%;
}
.map-point3 {
  bottom: 10%;
  left: 55%;
  width: 14%;
  height: 44%;
  transform: rotate(-20deg);
}
.map-point4 {
  bottom: 14%;
  right: 9%;
  width: 22%;
  height: 45%;
  transform: rotate(-20deg);
}
.map-point__no {
  display: inline-block;
  width: 100%;
  height: 100%;
  opacity: 0;
  visibility: hidden;
  cursor: pointer;
  pointer-events: initial;
}
/* PC */
@media (min-width: 768px) {
}
</style>
