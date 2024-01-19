<script setup>
import { ref } from "vue";

// 変数定義
const types = ref(["朝旗", "防パト"]);
let selectedType = "1";

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
});

// 親コンポーネントから受け取ったselectedInfoをselectedTypeに代入
selectedType = props.selectedInfo.type;

// 学年を選択したときにvalueの値をprops.selectedInfo.typeに代入
const selectType = (event) => {
  props.selectedInfo.type = event.target.value;
};
</script>

<template>
  <ul>
    <li class="form__radio-item" v-for="(label, index) in types" :key="index">
      <input
        type="radio"
        :id="'type' + (index + 1)"
        name="type"
        :value="index + 1"
        @click="selectType"
        v-model="selectedType"
      />
      <label :for="'type' + (index + 1)">{{ label }}</label>
    </li>
  </ul>
</template>
