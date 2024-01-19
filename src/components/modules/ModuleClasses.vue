<script setup>
import { ref } from "vue";

// 変数定義
const classes = ref(["1", "2", "3", "4", "5", "6"]);
let selectedClass = 0;

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  undecided: Boolean,
});

// undecidedがtrueの場合は、クラスの先頭に未定を追加
if (props.undecided) {
  classes.value.unshift("未定");
}

// 学年選択
const selectClass = () => {
  // selectedInfoを更新
  props.selectedInfo.class = selectedClass;
};
</script>

<template>
  <select id="class" v-model="selectedClass" @change="selectClass">
    <option v-for="(label, index) in classes" :value="label" :key="index">{{ label }}</option>
  </select>
  組
</template>
