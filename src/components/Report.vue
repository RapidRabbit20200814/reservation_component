<script setup>
import { ref } from "vue";

import ModuleHeader from "./modules/ModuleHeader.vue";
import ModuleGrades from "./modules/ModuleGrades.vue";
import ModuleClasses from "./modules/ModuleClasses.vue";
import ModuleStudentNumbers from "./modules/ModuleStudentNumbers.vue";
import ModuleWorkType from "./modules/ModuleWorkType.vue";
import ModuleFlagPoints from "./modules/ModuleFlagPoints.vue";
import ModulePatrolPoints from "./modules/ModulePatrolPoints.vue";

const selectedInfo = ref({
  grade: "",
  class: "",
  number: "",
  type: "1",
  pointID: "",
});
</script>

<template>
  <header>
    <ModuleHeader title="朝旗・防パトの実施報告" />
  </header>

  <main>
    <div class="inner">
      <p class="lead">朝旗・防パトの実施報告をする画面です。<br />実施した回数分、その都度入力をお願いします。</p>
      <div class="form">
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">学年</span></dt>
          <dd class="form__data">
            <ModuleGrades :selectedInfo="selectedInfo" />
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">クラス</span></dt>
          <dd class="form__data">
            <ModuleClasses :selectedInfo="selectedInfo" />
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">出席番号</span></dt>
          <dd class="form__data">
            <ModuleStudentNumbers :selectedInfo="selectedInfo" />
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">種別</span></dt>
          <dd class="form__data">
            <ModuleWorkType :selectedInfo="selectedInfo" />
          </dd>
        </div>
        <div class="form__row" v-if="selectedInfo.type === '1'">
          <dt class="form__head"><span class="form__ttl">立ち位置</span></dt>
          <dd class="form__data">
            <ModuleFlagPoints :selectedInfo="selectedInfo" />
          </dd>
        </div>
        <div class="form__row" v-else>
          <dt class="form__head"><span class="form__ttl">エリア</span></dt>
          <dd class="form__data">
            <ModulePatrolPoints :selectedInfo="selectedInfo" />
          </dd>
        </div>
      </div>
      <div class="button-area-lg">
        <router-link :to="{ name: 'Menu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
      </div>
    </div>
  </main>
</template>
