<script setup>
import { ref, watch } from "vue";
import { supabase } from "../../lib/supabaseClient";

// 変数定義
const grades = ref(["1", "2", "3", "4", "5", "6"]);

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  year: Number,
});

const gradeInfos = ref([]);

// --------------------------------------
//  設定
// --------------------------------------
const settingGradeInfo = async () => {
  // 確認メッセージ
  if (!confirm(`${props.year}年度の学年情報を設定します。よろしいですか？`)) {
    return;
  }
  // 対象年度のデータを削除
  await _deleteGradeInfo();
  // 対象年度のデータを追加
  _addGradeInfos();
  // メッセージ表示
  alert("設定しました");
};

const _deleteGradeInfo = async () => {
  // データ削除
  const { data, error } = await supabase.from("grade_setting").delete().eq("year", `${props.year}`);
  if (error) {
    console.log(error);
  }
};

const _addGradeInfos = async () => {
  // gradeInfosの件数分繰り返し
  for (const gradeInfo of gradeInfos.value) {
    // 日付、生徒数が空欄の場合はNULLをセット
    gradeInfo.period1_start_month = _nullSet(gradeInfo.period1_start_month);
    gradeInfo.period1_start_day = _nullSet(gradeInfo.period1_start_day);
    gradeInfo.period1_end_month = _nullSet(gradeInfo.period1_end_month);
    gradeInfo.period1_end_day = _nullSet(gradeInfo.period1_end_day);
    gradeInfo.period2_start_month = _nullSet(gradeInfo.period2_start_month);
    gradeInfo.period2_start_day = _nullSet(gradeInfo.period2_start_day);
    gradeInfo.period2_end_month = _nullSet(gradeInfo.period2_end_month);
    gradeInfo.period2_end_day = _nullSet(gradeInfo.period2_end_day);
    gradeInfo.total_student = _nullSet(gradeInfo.total_student);

    // データ登録
    const { data, error } = await supabase.from("grade_setting").insert([
      {
        year: props.year,
        grade: gradeInfo.grade,
        period1_start_month: gradeInfo.period1_start_month,
        period1_start_day: gradeInfo.period1_start_day,
        period1_end_month: gradeInfo.period1_end_month,
        period1_end_day: gradeInfo.period1_end_day,
        period2_start_month: gradeInfo.period2_start_month,
        period2_start_day: gradeInfo.period2_start_day,
        period2_end_month: gradeInfo.period2_end_month,
        period2_end_day: gradeInfo.period2_end_day,
        total_student: gradeInfo.total_student,
      },
    ]);
    if (error) {
      console.log(error);
    }
  }
};

// nullセット関数
const _nullSet = (num) => {
  if (num === "") {
    num = null;
  }
  return num;
};

// --------------------------------------
//  監視（年度）
// --------------------------------------
watch(
  () => props.year,
  async () => {
    // データ取得
    _getGradeInfo();
  }
);

// データ取得
const _getGradeInfo = async () => {
  // gradeInfosを初期化
  gradeInfos.value = [];
  // gradesの件数分繰り返し
  for (const grade of grades.value) {
    // データ取得
    const { data } = await supabase.from("grade_setting").select().eq("year", `${props.year}`).eq("grade", `${grade}`);
    // データが存在した場合は、gradeInfosにセット
    if (data[0]) {
      gradeInfos.value.push(data[0]);
    } else {
      //データが存在しない場合は、空のデータをセット
      gradeInfos.value.push({
        grade: grade,
        period1_start_month: "",
        period1_start_day: "",
        period1_end_month: "",
        period1_end_day: "",
        period2_start_month: "",
        period2_start_day: "",
        period2_end_month: "",
        period2_end_day: "",
        total_student: "",
      });
    }
  }
};
</script>

<template>
  <table>
    <tr class="head">
      <th>学年</th>
      <th>実施期間</th>
      <th>児童数</th>
    </tr>
    <tr v-for="(item, index) in gradeInfos" :key="index" class="grade-wrapper">
      <td>{{ item.grade }}年</td>
      <td>
        <div class="period-wrapper">
          <div class="period-start">
            <input
              type="number"
              name="period1_start_month"
              id="period1_start_month"
              v-model="item.period1_start_month"
              min="1"
              max="12"
            />月
            <input
              type="number"
              name="period1_start_day"
              id="period1_start_day"
              v-model="item.period1_start_day"
              min="1"
              max="31"
            />日
          </div>
          <span class="period-bridge">〜</span>
          <div class="period-end">
            <input
              type="number"
              name="period1_end_month"
              id="period1_end_month"
              v-model="item.period1_end_month"
              min="1"
              max="12"
            />月
            <input
              type="number"
              name="period1_end_day"
              id="period1_end_day"
              v-model="item.period1_end_day"
              min="1"
              max="31"
            />日
          </div>
        </div>
        <hr class="sp-only" />
        <div class="period-wrapper">
          <div class="period-start">
            <input
              type="number"
              name="period2_start_month"
              id="period2_start_month"
              v-model="item.period2_start_month"
              min="1"
              max="12"
            />月
            <input
              type="number"
              name="period2_start_day"
              id="period2_start_day"
              v-model="item.period2_start_day"
              min="1"
              max="31"
            />日
          </div>
          <span class="period-bridge">〜</span>
          <div class="period-end">
            <input
              type="number"
              name="period2_end_month"
              id="period2_end_month"
              v-model="item.period2_end_month"
              min="1"
              max="12"
            />月
            <input
              type="number"
              name="period2_end_day"
              id="period2_end_day"
              v-model="item.period2_end_day"
              min="1"
              max="31"
            />日
          </div>
        </div>
      </td>
      <td><input type="number" name="total_student" id="total_student" v-model="item.total_student" min="0" />人</td>
    </tr>
  </table>
  <div class="button-area">
    <button class="button" type="button" @click="settingGradeInfo">設定</button>
  </div>
</template>

<style scoped>
table {
  margin-top: 2rem;
  width: 100%;
}
tr {
  border-bottom: 1px solid #eee;
}
.period-wrapper {
  margin: 1rem auto;
  width: fit-content;
  display: grid;
  grid-template-columns: auto 1fr;
  grid-template-rows: auto auto;
  align-items: center;
  grid-template-areas: "start start" "bridge end";
  gap: 0.5rem;
}
.period-wrapper + .period-wrapper {
  margin-top: 0.5rem;
}
.period-start {
  grid-area: start;
  padding-right: 0.5em;
  width: fit-content;
}
.period-bridge {
  grid-area: bridge;
}
.period-end {
  grid-area: end;
  width: fit-content;
}
.period-start,
.period-end {
  display: flex;
  flex-wrap: nowrap;
  align-items: flex-end;
}
input[type="number"] {
  margin-inline: 0.5rem 0.2rem;
  width: 3rem;
  text-align: right;
}
#total_student {
  width: 4rem;
}
hr {
  color: #eee;
}
.button-area {
  text-align: center;
}
/* PC */
@media (min-width: 768px) {
  .period-wrapper {
    grid-template-columns: 1fr auto 1fr;
    grid-template-rows: auto;
    grid-template-areas: "start bridge end";
    gap: 0;
  }
}
</style>
