<script setup>
import { ref } from "vue";

// 変数定義
const numbers = ref([]);
for (let i = 1; i <= 40; i++) {
  numbers.value.push(i.toString());
}
let selectedNumber = 0;

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
  undecided: Boolean,
  blank: Boolean,
});

// undecidedがtrueの場合は、クラスの先頭に未定を追加
if (props.undecided) {
  numbers.value.unshift("未定");
}

// 学年選択
const selectNumber = () => {
  // selectedInfoを更新
  props.selectedInfo.number = selectedNumber;
};
</script>

<template>
  <div class="form__select-wrap">
    <select id="studentNumber" class="form__select-box" v-model="selectedNumber" @change="selectNumber">
      <option v-if="blank" value=""></option>
      <option v-for="(label, index) in numbers" :value="label" :key="index">{{ label }}</option>
    </select>
  </div>
  番
</template>
