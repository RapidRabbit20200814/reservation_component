<script setup>
import { ref, onMounted, defineEmits, watch } from "vue";
import { supabase } from "../../lib/supabaseClient";

// 変数定義
const points = ref([]);
let selectedPoint = 0;
let mapPointON = ref(true);

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  page: String,
});

// 親コンポーネントへ渡す値を定義
const emits = defineEmits();

// 立ち位置情報取得
const getPoints = async () => {
  const { data } = await supabase.from("flag_point").select();
  // deleted_flgがtrue以外の行のみを抽出
  const filteredData = data.filter((row) => row.deleted_flg !== true);
  // pointsにセット
  points.value = filteredData;
};

onMounted(() => {
  getPoints();
});

// 立ち位置選択
const selectPoint = (event) => {
  // 選択された立ち位置の情報を取得
  const targetNo = event.target.innerText;
  const filteredData = points.value.filter((row) => row.point_no == targetNo);
  props.selectedInfo.pointID = filteredData[0].point_id;
  props.selectedInfo.pointNo = filteredData[0].point_no;
  props.selectedInfo.pointName = filteredData[0].point_name;
  props.selectedInfo.memo = filteredData[0].memo;
  props.selectedInfo.map_x = filteredData[0].map_x;
  props.selectedInfo.map_y = filteredData[0].map_y;
  props.selectedInfo.deleted_flg = filteredData[0].deleted_flg;
  // 呼び出し元にカスタムイベントを伝える
  emits("select", true);
};

// --------------------------------------
//  POINT表示／非表示切り替え
// --------------------------------------
const mapPointSwitch = () => {
  // mapPointONを反転
  mapPointON.value = !mapPointON.value;
  // ボタンのクラスとテキストを切り替え
  const button = document.querySelector(".map-point-switch");
  const label = document.querySelector(".map-point-switch-label");
  if (mapPointON.value) {
    button.classList.add("off");
    label.innerText = "OFF";
  } else {
    button.classList.remove("off");
    label.innerText = "ON";
  }
};

// --------------------------------------
//  監視（X座標、Y座標が変更されたらtop,leftを更新）
// --------------------------------------
watch(
  () => props.selectedInfo.map_x,
  (newValue, oldValue) => {
    // activeクラスのleftを更新
    const activePoint = document.querySelector(".map-point.active");
    if (activePoint) {
      activePoint.style.left = newValue + "%";
    }
  }
);
watch(
  () => props.selectedInfo.map_y,
  (newValue, oldValue) => {
    // activeクラスのbottomを更新
    const activePoint = document.querySelector(".map-point.active");
    if (activePoint) {
      activePoint.style.bottom = newValue + "%";
    }
  }
);
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
    <img src="../../assets/img/flag-point-map.jpg" alt="朝旗の立ち位置マップ" width="623" height="778" class="map" />
    <div v-if="mapPointON" class="map-point-wrapper">
      <button
        type="button"
        class="map-point"
        v-for="(item, index) in points"
        :key="index"
        :style="{ bottom: item.map_y + '%', left: item.map_x + '%' }"
        :class="['map-point' + item.point_no, { active: item.point_no === props.selectedInfo.pointNo }]"
        @click="selectPoint"
      >
        <span class="map-point__no">{{ item.point_no }}</span>
      </button>
    </div>

    <button type="button" class="map-point map-point-switch off" @click="mapPointSwitch">
      <span class="map-point-switch-label">OFF</span>
    </button>
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
  aspect-ratio: 623 / 778;
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
  bottom: 0;
  left: 0;
  display: flex;
  place-content: center;
  width: 1.6em;
  height: 1.6em;
  background: url("../../assets/img/map-marker.png") no-repeat center center;
  background-size: contain;
}
.map-point.active {
  background: url("../../assets/img/map-marker-active.png") no-repeat center center;
  background-size: contain;
}
.map-point13 {
  rotate: -20deg;
}
.map-point19 {
  rotate: 20deg;
}
.map-point-switch {
  bottom: 5%;
  left: 5%;
  width: 1.5em;
  height: 1.5em;
}
.map-point-switch::after {
  position: absolute;
  content: "";
  display: block;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(45deg);
  width: 2px;
  height: 1.6em;
  background-color: transparent;
}
.map-point-switch.off::after {
  background-color: var(--color-font-base);
}
.map-point-switch-label {
  position: relative;
  padding-top: 1.6rem;
  font-size: 10px;
  font-weight: bold;
}
.map-point__no {
  color: #fff;
  font-size: 0.8rem;
  font-weight: bold;
}
/* PC */
@media (min-width: 768px) {
  .map-point {
    width: 2em;
    height: 2em;
    transform: translateX(3px);
  }
  .map-point__no {
    font-size: 1rem;
  }
  .reserve-flag-grade .map-point {
    transform: translateX(10px);
  }
}
</style>
