<script setup>
import { ref } from "vue";

// 変数定義
const urgencies = ref(["３：急いで対応して欲しい", "２：できれば対応して欲しい", "１：特に必要ない"]);
let selectedUrgency = "";

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  blank: Boolean,
});

// 学年選択
const selectUrgency = () => {
  // selectedInfoを更新
  props.selectedInfo.urgency = selectedUrgency;
};
</script>

<template>
  <div class="form__select-wrap">
    <select id="urgency" class="form__select-box" v-model="selectedUrgency" @change="selectUrgency">
      <option v-if="blank" value=""></option>
      <option v-for="(label, index) in urgencies" :value="urgencies.length - index" :key="index">{{ label }}</option>
    </select>
  </div>
</template>
