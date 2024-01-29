<script setup>
import { ref } from "vue";

// 変数定義
const classes = ref(["1", "2", "3", "4", "5", "6"]);
let selectedClass = 0;

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  undecided: Boolean,
  blank: Boolean,
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
  <div class="form__select-wrap">
    <select id="class" class="form__select-box" v-model="selectedClass" @change="selectClass">
      <option v-if="blank" value=""></option>
      <option v-for="(label, index) in classes" :value="label" :key="index">{{ label }}</option>
    </select>
  </div>
  組
</template>
