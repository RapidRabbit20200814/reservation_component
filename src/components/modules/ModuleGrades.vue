<script setup>
  import { ref } from 'vue'

  // 変数定義
  const grades = ref(["1", "2", "3", "4", "5", "6"]);
  let selectedGrade = 0;

  // 親コンポーネントから受け取る値を定義
  const props = defineProps({
    selectedInfo: Object
  });

  // 学年選択
  const selectGrade = () => {
    // selectedInfoを更新
    props.selectedInfo.grade = selectedGrade;
  }
</script>

<template>
  <select id="grade" v-model="selectedGrade" @change="selectGrade">
    <option v-for="label, index in grades" :value="label" :key="index">{{ label }}</option>
  </select> 年
</template>
