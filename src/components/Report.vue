<script setup>
import { ref } from "vue";
import { supabase } from "../lib/supabaseClient";

import ModuleHeader from "./modules/ModuleHeader.vue";
import ModuleGrades from "./modules/ModuleGrades.vue";
import ModuleClasses from "./modules/ModuleClasses.vue";
import ModuleStudentNumbers from "./modules/ModuleStudentNumbers.vue";
import ModuleFlagPoints from "./modules/ModuleFlagPoints.vue";
import ModulePatrolPoints from "./modules/ModulePatrolPoints.vue";
import ModuleUrgencies from "./modules/ModuleUrgencies.vue";

const selectedInfo = ref({
  grade: "",
  class: "",
  number: "",
  pointID: "",
  urgency: "",
});

let type = ref("1");
let implementationDate = ref("");
let notImplementationCheck = ref(false);
let comment = ref("");
let errorGrade = ref(false);
let errorClass = ref(false);
let errorNumber = ref(false);
let errorPoint = ref(false);
let errorDate = ref(false);

let confirmMessage = ref(true);

// --------------------------------------
//  登録
// --------------------------------------
const register = async () => {
  // 必須チェック
  errorGrade.value = false;
  errorClass.value = false;
  errorNumber.value = false;
  errorPoint.value = false;
  errorDate.value = false;
  // 学年、クラス、出席番号、立ち位置、実施日のいずれかが未入力の場合はエラー
  if (!selectedInfo.value.grade) {
    errorGrade.value = true;
  }
  if (!selectedInfo.value.class) {
    errorClass.value = true;
  }
  if (!selectedInfo.value.number) {
    errorNumber.value = true;
  }
  if (!selectedInfo.value.pointID) {
    errorPoint.value = true;
  }
  if (!implementationDate.value) {
    errorDate.value = true;
  }

  // エラーがある場合は、エラーメッセージを表示して処理を終了
  if (!errorGrade.value && !errorClass.value && !errorNumber.value && !errorPoint.value && !errorDate.value) {
    // 確認メッセージ
    if (!confirm("登録しますか？")) {
      return;
    }

    // 報告データを登録
    const { data, error } = await supabase
      .from("report")
      .upsert([
        {
          grade: selectedInfo.value.grade,
          class: selectedInfo.value.class,
          student_no: selectedInfo.value.number,
          work_type: type.value,
          point_id: selectedInfo.value.pointID,
          implementation_date: implementationDate.value,
          not_implementation: notImplementationCheck.value,
          comment: comment.value,
          urgency: selectedInfo.value.urgency,
          admin_memo: "",
        },
      ])
      .select();

    if (error) {
      console.error("データの挿入エラー:", error);
    } else {
      // 確認メッセージを表示
      if (type.value === "1") {
        // メッセージ表示
        alert("朝旗の実施報告ができました。");
        console.log(confirmMessage.value);
        if (confirmMessage.value) {
          if (confirm("続けて防パトの報告もしますか？ →「OK」\r\n終了する →「キャンセル」")) {
            type.value = "2";
            selectedInfo.value.pointID = "";
            implementationDate.value = "";
            notImplementationCheck.value = false;
            comment.value = "";
            selectedInfo.value.urgency = "";
            confirmMessage.value = false;
          } else {
            return;
          }
        } else {
          return;
        }
      } else {
        // メッセージ表示
        alert("防パトの実施報告ができました。");
        console.log(confirmMessage.value);
        if (confirmMessage.value) {
          if (
            // 確認メッセージを表示
            confirm("続けて朝旗の報告もしますか？ →「OK」\r\n終了する →「キャンセル」")
          ) {
            type.value = "1";
            selectedInfo.value.pointID = "";
            implementationDate.value = "";
            notImplementationCheck.value = false;
            comment.value = "";
            selectedInfo.value.urgency = "";
            confirmMessage.value = false;
          } else {
            return;
          }
        } else {
          return;
        }
      }
    }
  } else {
    alert("必須項目を入力してください");
  }
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
          <dt class="form__head">
            <label for="grade" class="form__title"><span>学年</span><span class="form__required">必須</span></label>
          </dt>
          <dd class="form__data">
            <ModuleGrades :selectedInfo="selectedInfo" />
            <p v-if="errorGrade" class="form__error">学年を入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head">
            <label for="class" class="form__title"><span>クラス</span><span class="form__required">必須</span></label>
          </dt>
          <dd class="form__data">
            <ModuleClasses :selectedInfo="selectedInfo" />
            <p v-if="errorClass" class="form__error">クラスを入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head">
            <label for="studentNumber" class="form__title"
              ><span>出席番号</span><span class="form__required">必須</span></label
            >
          </dt>
          <dd class="form__data">
            <ModuleStudentNumbers :selectedInfo="selectedInfo" />
            <p v-if="errorNumber" class="form__error">出席番号を入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head">
            <div class="form__title"><span>種別</span><span class="form__required">必須</span></div>
          </dt>
          <dd class="form__data">
            <ul class="form__radio-wrapper">
              <li class="form__radio-item">
                <input type="radio" id="type1" name="type" value="1" v-model="type" />
                <label for="type1">朝旗</label>
              </li>
              <li class="form__radio-item">
                <input type="radio" id="type2" name="type" value="2" v-model="type" />
                <label for="type2">防パト</label>
              </li>
            </ul>
          </dd>
        </div>
        <div class="form__row" v-if="type === '1'">
          <dt class="form__head">
            <label for="point" class="form__title"><span>立ち位置</span><span class="form__required">必須</span></label>
          </dt>
          <dd class="form__data">
            <ModuleFlagPoints :selectedInfo="selectedInfo" />
            <p v-if="errorPoint" class="form__error">立ち位置を入力してください</p>
          </dd>
        </div>
        <div class="form__row" v-else>
          <dt class="form__head">
            <label for="point" class="form__title"><span>エリア</span><span class="form__required">必須</span></label>
          </dt>
          <dd class="form__data">
            <ModulePatrolPoints :selectedInfo="selectedInfo" :displayDetail="false" />
            <p v-if="errorPoint" class="form__error">エリアを入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head">
            <label for="implementation-date" class="form__title"
              ><span>実施日</span><span class="form__required">必須</span></label
            >
          </dt>
          <dd class="form__data">
            <input type="date" name="implementation-date" id="implementation-date" v-model="implementationDate" />
            <div class="form__checkbox-wrapper">
              <input
                type="checkbox"
                name="not-implemented-check"
                id="not-implemented-check"
                class="form__checkbox"
                v-model="notImplementationCheck"
              />
              <label for="not-implemented-check" class="form__checkbox-label">実施できなかった</label>
            </div>
            <p v-if="errorDate" class="form__error">実施日を入力してください</p>
          </dd>
        </div>
        <div class="form__row">
          <dt class="form__head form__head--top">
            <label for="comment" class="form__title">コメント</label>
          </dt>
          <dd class="form__data">
            <textarea
              name="comment"
              id="comment"
              v-model="comment"
              placeholder="危険な様子はないか、安全面のアドバイス、児童の様子、制度そのものについて、実施できなかった理由、などお気づきの点をお聞かせください"
            ></textarea>
            <p class="urgency-message">安全対策等の対応の必要性を選択してください</p>
            <ModuleUrgencies :selectedInfo="selectedInfo" :blank="true" />
          </dd>
        </div>
        <div class="button-area">
          <button class="button" type="button" @click="register">登録する</button>
        </div>
      </div>
      <p class="message">
        朝旗・防パト実施中に危険な場所を発見された方は、実施報告とあわせて<a
          href="https://tisetusyou-pta.org/safety-map/attention-form"
          target="_blank"
          rel="noopener"
          >こちらのフォーム</a
        >からもご連絡ください（写真が添付できます）。
      </p>
      <p class="message">状況に応じて通学路安全マップの更新、注意喚起の通知などを行います。</p>
      <div class="button-area-lg">
        <router-link :to="{ name: 'Menu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
      </div>
    </div>
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
  align-self: center;
}
.form__head--top {
  align-self: flex-start;
}
.form__title {
  display: flex;
  align-items: center;
}
.form__data {
  margin-top: 0.5rem;
}
textarea {
  height: 10rem;
}
.form__checkbox-wrapper {
  display: inline-block;
  margin-top: 0.5rem;
}
.urgency-message {
  margin-top: 1rem;
}
.form .button-area {
  text-align: center;
}

.message {
  margin: 2rem auto 0 auto;
}

.message + .message {
  margin-top: 1rem;
}

.message a {
  color: var(--color-accent);
  text-decoration: underline;
}

/* PC */
@media (min-width: 768px) {
  .form__row {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 2rem;
  }
  .form__title {
    justify-content: space-between;
  }
  ul:has(.form__radio-item) {
    display: flex;
    gap: 1rem;
  }
  .form__checkbox-wrapper {
    margin-left: 0.5rem;
  }
}
</style>
