<script setup>
import { ref } from "vue";
import { supabase } from "../lib/supabaseClient";

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

const implementationDate = ref("");
let comment = ref("");

let errorGrade = ref(false);
let errorClass = ref(false);
let errorNumber = ref(false);
let errorPoint = ref(false);
let errorDate = ref(false);

// --------------------------------------
//  登録
// --------------------------------------
const register = async () => {
  // 必須チェック
  errorGrade = false;
  errorClass = false;
  errorNumber = false;
  errorPoint = false;
  errorDate = false;
  // 学年、クラス、出席番号、立ち位置、実施日のいずれかが未入力の場合はエラー
  if (!selectedInfo.value.grade) {
    errorGrade = true;
  }
  if (!selectedInfo.value.class) {
    errorClass = true;
  }
  if (!selectedInfo.value.number) {
    errorNumber = true;
  }
  if (!selectedInfo.value.pointID) {
    errorPoint = true;
  }
  if (!implementationDate.value) {
    errorDate = true;
  }

  // エラーがある場合は、エラーメッセージを表示して処理を終了
  if (!errorGrade && !errorClass && !errorNumber && !errorPoint && !errorDate) {
    // 確認メッセージ
    if (!confirm("予約しますか？")) {
      return;
    }
    // 予約データを登録
    const { data, error } = await supabase
      .from("report")
      .upsert([
        {
          grade: selectedInfo.value.grade,
          class: selectedInfo.value.class,
          student_no: selectedInfo.value.number,
          work_type: selectedInfo.value.type,
          point_id: selectedInfo.value.pointID,
          implementation_date: implementationDate,
          comment: comment,
        },
      ])
      .select();

    if (error) {
      console.error("データの挿入エラー:", error);
    } else {
      // メッセージを表示
      alert("登録しました");
    }
  } else {
    alert("必須項目を入力してください");
    return { errorGrade, errorClass, errorNumber, errorPoint, errorDate };
  }
};

const register2 = () => {
  comment = "テスト";
  console.log(comment);
};
</script>

<template>
  <header>
    <ModuleHeader title="朝旗・防パトの実施報告" />
  </header>

  <main>
    <div class="inner-s">
      <p class="lead">朝旗・防パトの実施報告をする画面です。<br />実施した回数分、その都度入力をお願いします。</p>
      <div class="bg-white form">
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">学年</span><span class="form__required">必須</span></dt>
          <dd class="form__data">
            <ModuleGrades :selectedInfo="selectedInfo" />
            <p v-if="errorGrade" class="form__error">学年を入力してください</p>
            {{ errorGrade }}
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">クラス</span><span class="form__required">必須</span></dt>
          <dd class="form__data">
            <ModuleClasses :selectedInfo="selectedInfo" />
            <p v-if="errorClass" class="form__error">クラスを入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">出席番号</span><span class="form__required">必須</span></dt>
          <dd class="form__data">
            <ModuleStudentNumbers :selectedInfo="selectedInfo" />
            <p v-if="errorNumber" class="form__error">出席番号を入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">種別</span><span class="form__required">必須</span></dt>
          <dd class="form__data">
            <ModuleWorkType :selectedInfo="selectedInfo" />
          </dd>
        </div>
        <div class="form__row" v-if="selectedInfo.type === '1'">
          <dt class="form__head"><span class="form__ttl">立ち位置</span><span class="form__required">必須</span></dt>
          <dd class="form__data">
            <ModuleFlagPoints :selectedInfo="selectedInfo" />
            <p v-if="errorPoint" class="form__error">立ち位置を入力してください</p>
          </dd>
        </div>
        <div class="form__row" v-else>
          <dt class="form__head"><span class="form__ttl">エリア</span><span class="form__required">必須</span></dt>
          <dd class="form__data">
            <ModulePatrolPoints :selectedInfo="selectedInfo" :displayDetail="false" />
            <p v-if="errorPoint" class="form__error">エリアを入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head"><span class="form__ttl">実施日</span><span class="form__required">必須</span></dt>
          <dd class="form__data">
            <input type="date" name="implementation-date" id="implementation-date" v-model="implementationDate" />
            <p v-if="errorDate" class="form__error">実施日を入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head">
            <span class="form__ttl">コメント</span>
          </dt>
          <dd class="form__data">
            <textarea name="comment" id="comment" v-model="comment"></textarea>
            <p>{{ comment }}</p>
          </dd>
        </div>
        <div class="button-area">
          <button class="button" type="button" @click="register2">登録する</button>
        </div>
      </div>
      <div class="button-area-lg">
        <router-link :to="{ name: 'Menu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
      </div>
    </div>
    <p>{{ comment }}</p>
  </main>
</template>

<style scoped>
.form__row {
  padding: 1rem;
}
.form__row + .form__row {
  border-top: 1px solid #eee;
}
.form__head {
  display: flex;
  align-items: center;
}
.form__data {
  margin-top: 0.5rem;
}
textarea {
  height: 10rem;
}
.form .button-area {
  text-align: center;
}

/* PC */
@media (min-width: 768px) {
  .form__row {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 1rem;
  }
  .form__head {
    justify-content: space-between;
  }
  ul:has(.form__radio-item) {
    display: flex;
    gap: 1rem;
  }
}
</style>
