<script setup>
import { ref } from "vue";
import { supabase } from "../../lib/supabaseClient";

import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";
import ModuleYears from "../modules/ModuleYears.vue";

let year = ref("");
let type = ref("1");
let report = ref([]);
let pluralCount = ref(5);
let totallingResult = ref([]);
let pluralResult = ref([]);
let classAscSort = ref(true);
let totalAscSort = ref(true);
let allGrades = ref({
  totalStudent: 0,
  reserveCount: 0,
  reserveRate: 0,
  reportCount: 0,
  reportCount0: 0,
  reportRate0: 0,
  reportCount1: 0,
  reportRate1: 0,
  reportCount2: 0,
  reportRate2: 0,
});

// --------------------------------------
//  学年情報取得
// --------------------------------------
const gradeSelect = async () => {
  if (!year.value) {
    return;
  }
  // ① 学年情報を取得（grade_settingテーブル）
  const response = await supabase.from("grade_setting").select().eq("year", year.value);
  if (response.data) {
    totallingResult.value = response.data;
  }

  // 全学年の生徒数を取得
  for (const item of totallingResult.value) {
    allGrades.value.totalStudent += item.total_student;
  }
};

// --------------------------------------
//  報告データ取得
// --------------------------------------
const reportSelect = async (startDate, endDate) => {
  // ② 報告データを取得
  // クエリを作成
  const queryReport = `
      work_type,
      grade,
      class,
      student_no
  `;
  // クエリを実行
  const reportResponse = await supabase
    .from("report")
    .select(queryReport)
    .gte("implementation_date", startDate)
    .lte("implementation_date", endDate);
  if (reportResponse.data) {
    report.value = reportResponse.data;
  }
};

// --------------------------------------
//  予約データ取得
// --------------------------------------
const reserveSelect = async (startDate, endDate) => {
  // 初期化
  allGrades.value.reserveCount = 0;
  allGrades.value.reserveRate = 0;
  // ③ 予約データを取得
  // 予約テーブル名をセット
  let tableName = "";
  if (type.value == "1") {
    tableName = "flag_reservation";
  } else {
    tableName = "patrol_reservation";
  }

  // 期間内の予約データ件数を取得
  for (const item of totallingResult.value) {
    // クエリを作成
    const queryReserve = `
      year,
      month,
      day
    `;
    // クエリを実行
    try {
      const response = await supabase
        .from(tableName)
        .select(queryReserve)
        .gte("year", year.value)
        .lte("year", year.value + 1)
        .eq("grade", item.grade);

      if (response.data) {
        // year-month-dayがstartDate〜endDateの間に含まれるデータの件数を取得
        const reserveCount = response.data.filter((item) => {
          const month = item.month < 10 ? `0${item.month}` : item.month;
          const day = item.day < 10 ? `0${item.day}` : item.day;
          const date = `${item.year}-${month}-${day}`;
          return date >= startDate && date <= endDate;
        }).length;

        // totallingResultにセット
        item.reserveCount = reserveCount;

        // 全学年の予約数を取得
        allGrades.value.reserveCount += reserveCount;

        // 予約率を計算
        if (item.total_student) {
          item.reserveRate = Math.round((reserveCount / (item.total_student * 2)) * 100);
        }
        // 全学年の予約率を計算
        if (allGrades.value.totalStudent) {
          allGrades.value.reserveRate = Math.round(
            (allGrades.value.reserveCount / (allGrades.value.totalStudent * 2)) * 100
          );
        }
      }
    } catch (error) {
      console.error(error);
    }
  }
};

// --------------------------------------
//  報告データ計算
// --------------------------------------
const reportCalc = async (startDate, endDate) => {
  // 初期化
  allGrades.value.reportCount = 0;
  allGrades.value.reportCount0 = 0;
  allGrades.value.reportRate0 = 0;
  allGrades.value.reportCount1 = 0;
  allGrades.value.reportRate1 = 0;
  allGrades.value.reportCount2 = 0;
  allGrades.value.reportRate2 = 0;
  // ④ 報告件数、実施率を計算
  for (const item of totallingResult.value) {
    // 報告件数
    item.reportCount = report.value.filter((row) => row.grade == item.grade && row.work_type == type.value).length;
    // 全学年の報告件数
    allGrades.value.reportCount += item.reportCount;
    // 報告人数
    const reportPeople = report.value
      .filter((row) => row.grade == item.grade && row.work_type == type.value)
      .reduce((acc, cur) => {
        const key = `${cur.grade}-${cur.class}-${cur.student_no}`;
        if (!acc[key]) {
          acc[key] = {
            grade: cur.grade,
            class: cur.class,
            student_no: cur.student_no,
            count: 1,
          };
        } else {
          acc[key].count++;
        }
        return acc;
      }, {});

    // 報告1回の件数
    item.reportCount1 = Object.values(reportPeople).filter((item) => {
      return item.count === 1;
    }).length;
    // 全学年の報告1回の件数
    allGrades.value.reportCount1 += item.reportCount1;

    // 報告2回以上の件数
    item.reportCount2 = Object.values(reportPeople).filter((item) => {
      return item.count >= 2;
    }).length;
    // 全学年の報告2回以上の件数
    allGrades.value.reportCount2 += item.reportCount2;

    if (item.total_student) {
      // 報告1回の割合
      item.reportRate1 = Math.round((item.reportCount1 / item.total_student) * 100);
      // 報告2回の割合
      item.reportRate2 = Math.round((item.reportCount2 / item.total_student) * 100);
      // 報告0回の件数と割合
      item.reportCount0 = item.total_student - item.reportCount1 - item.reportCount2;
      item.reportRate0 = Math.round((item.reportCount0 / item.total_student) * 100);
    }

    if (allGrades.value.totalStudent) {
      // 全学年の報告1回の割合
      allGrades.value.reportRate1 = Math.round((allGrades.value.reportCount1 / allGrades.value.totalStudent) * 100);
      // 全学年の報告2回の割合
      allGrades.value.reportRate2 = Math.round((allGrades.value.reportCount2 / allGrades.value.totalStudent) * 100);
      // 全学年の報告0回の件数と割合
      allGrades.value.reportCount0 =
        allGrades.value.totalStudent - allGrades.value.reportCount1 - allGrades.value.reportCount2;
      allGrades.value.reportRate0 = Math.round((allGrades.value.reportCount0 / allGrades.value.totalStudent) * 100);
    }
  }
};

// --------------------------------------
//  集計
// --------------------------------------
const totalling = async () => {
  if (!year.value) {
    return;
  }
  // 年度の開始日と終了日を取得
  const startDate = `${year.value}-04-01`;
  const endDate = `${year.value + 1}-03-31`;

  await reportSelect(startDate, endDate);
  await reserveSelect(startDate, endDate);
  await reportCalc(startDate, endDate);
};

// --------------------------------------
//  複数回実施者データを取得
// --------------------------------------
const searchPlural = async () => {
  // 学年、クラス、番号でグループ化
  const groupResult = report.value.reduce((acc, cur) => {
    const key = `${cur.grade}-${cur.class}-${cur.student_no}`;
    if (!acc[key]) {
      acc[key] = {
        grade: cur.grade,
        class: cur.class,
        student_no: cur.student_no,
        flagCnt: 0,
        patrolCnt: 0,
      };
    }
    if (cur.work_type == "1") {
      acc[key].flagCnt++;
    } else {
      acc[key].patrolCnt++;
    }
    return acc;
  }, {});
  // 朝旗（work_type="1")回数、防パト（work_type="2")回数の合計が指定回数以上のものを抽出
  const extractionResult = Object.values(groupResult).filter((item) => {
    return item.flagCnt + item.patrolCnt >= pluralCount.value;
  });
  // 並び替え（合計回数、学年、クラス、番号の昇順）
  extractionResult.sort(function (a, b) {
    if (a.flagCnt + a.patrolCnt < b.flagCnt + b.patrolCnt) return 1;
    if (a.flagCnt + a.patrolCnt > b.flagCnt + b.patrolCnt) return -1;
    if (a.grade < b.grade) return -1;
    if (a.grade > b.grade) return 1;
    if (a.class < b.class) return -1;
    if (a.class > b.class) return 1;
    if (a.student_no < b.student_no) return -1;
    if (a.student_no > b.student_no) return 1;
    return 0;
  });

  // extractionResultをpluralResultにセット
  pluralResult.value = extractionResult;
};

// --------------------------------------
//  年度選択
// --------------------------------------
const selectedYear = async (selectedYear) => {
  if (selectedYear) {
    year.value = selectedYear;
  } else {
    // 今年度をセット
    const date = new Date();
    year.value = date.getFullYear();
    // 1~3月の場合
    if (date.getMonth() + 1 <= 3) {
      year.value--;
    }
  }

  try {
    await gradeSelect();
    await totalling();
    await searchPlural();
  } catch (error) {
    console.error(error);
  }
};

// --------------------------------------
//  並び替え
// --------------------------------------
const sortClass = (asc) => {
  if (asc) {
    pluralResult.value.sort(function (a, b) {
      if (a.grade < b.grade) return -1;
      if (a.grade > b.grade) return 1;
      if (a.class < b.class) return -1;
      if (a.class > b.class) return 1;
      if (a.student_no < b.student_no) return -1;
      if (a.student_no > b.student_no) return 1;
      return 0;
    });
  } else {
    pluralResult.value.sort(function (a, b) {
      if (a.grade < b.grade) return 1;
      if (a.grade > b.grade) return -1;
      if (a.class < b.class) return 1;
      if (a.class > b.class) return -1;
      if (a.student_no < b.student_no) return 1;
      if (a.student_no > b.student_no) return -1;
      return 0;
    });
  }
  classAscSort.value = !classAscSort.value;
};
const sortTotal = (asc) => {
  if (asc) {
    pluralResult.value.sort(function (a, b) {
      if (a.flagCnt + a.patrolCnt < b.flagCnt + b.patrolCnt) return -1;
      if (a.flagCnt + a.patrolCnt > b.flagCnt + b.patrolCnt) return 1;
      if (a.grade < b.grade) return -1;
      if (a.grade > b.grade) return 1;
      if (a.class < b.class) return -1;
      if (a.class > b.class) return 1;
      if (a.student_no < b.student_no) return -1;
      if (a.student_no > b.student_no) return 1;
      return 0;
    });
  } else {
    pluralResult.value.sort(function (a, b) {
      if (a.flagCnt + a.patrolCnt < b.flagCnt + b.patrolCnt) return 1;
      if (a.flagCnt + a.patrolCnt > b.flagCnt + b.patrolCnt) return -1;
      if (a.grade < b.grade) return 1;
      if (a.grade > b.grade) return -1;
      if (a.class < b.class) return 1;
      if (a.class > b.class) return -1;
      if (a.student_no < b.student_no) return 1;
      if (a.student_no > b.student_no) return -1;
      return 0;
    });
  }
  totalAscSort.value = !totalAscSort.value;
};
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="集計" />
  </header>

  <main>
    <div class="inner">
      <div class="totalling__header">
        <ModuleYears
          :startYear="2023"
          :numberOfYears="20"
          :currentYearFlg="true"
          @selectedYear="selectedYear"
          v-model="year"
        />
        <ul class="form__radio-wrapper">
          <li class="form__radio-item">
            <input type="radio" id="type1" name="type" value="1" v-model="type" @change="totalling" />
            <label for="type1">朝旗</label>
          </li>
          <li class="form__radio-item">
            <input type="radio" id="type2" name="type" value="2" v-model="type" @change="totalling" />
            <label for="type2">防パト</label>
          </li>
        </ul>
      </div>
      <div v-if="totallingResult.length != 0">
        <div class="totalling">
          <table class="table-primary">
            <tr class="totalling-result-head table-primary-head">
              <th class="table-grade">学年</th>
              <th class="table-period">実施期間</th>
              <th class="table-total-student">児童数</th>
              <th class="table-reserve-count">予約数</th>
              <th class="table-reserve-rate">予約率<span class="small">予約数÷(児童数✕2回)</span></th>
              <th class="table-report-count">報告数</th>
              <th class="table-report-rate0">報告0回</th>
              <th class="table-report-rate1">1回</th>
              <th class="table-report-rate2">2回以上</th>
            </tr>
            <tr v-for="item in totallingResult" :key="item.id" class="totalling-result-body table-primary-body">
              <td class="table-grade">{{ item.grade }}年</td>
              <td class="table-period">
                <p v-if="item.period1_start_month">
                  {{ item.period1_start_month }} / {{ item.period1_start_day }}〜{{ item.period1_end_month }} /
                  {{ item.period1_end_day }}
                </p>
                <p v-if="item.period2_start_month">
                  {{ item.period2_start_month }} / {{ item.period2_start_day }}〜{{ item.period2_end_month }} /
                  {{ item.period2_end_day }}
                </p>
              </td>
              <td class="table-total-student">
                <span v-if="item.total_student">{{ item.total_student }}人</span>
              </td>
              <td class="table-reserve-count">{{ item.reserveCount }}件</td>
              <td class="table-reserve-rate">
                <span v-if="item.total_student">{{ item.reserveRate }}%</span>
              </td>
              <td class="table-report-count">{{ item.reportCount }}件</td>
              <td class="table-report-rate0">
                <span v-if="item.total_student">{{ item.reportCount0 }}人</span>
                <span v-if="item.total_student" class="rate">({{ item.reportRate0 }}%)</span>
              </td>
              <td class="table-report-rate1">
                {{ item.reportCount1 }}人
                <span v-if="item.total_student" class="rate">({{ item.reportRate1 }}%)</span>
              </td>
              <td class="table-report-rate2">
                {{ item.reportCount2 }}人
                <span v-if="item.total_student" class="rate">({{ item.reportRate2 }}%)</span>
              </td>
            </tr>
            <tr class="totalling-result-body table-primary-body">
              <td class="table-grade">全校</td>
              <td class="table-period">ー</td>
              <td class="table-total-student">
                <span v-if="allGrades.totalStudent">{{ allGrades.totalStudent }}人</span>
              </td>
              <td class="table-reserve-count">{{ allGrades.reserveCount }}件</td>
              <td class="table-reserve-rate">
                <span v-if="allGrades.totalStudent">{{ allGrades.reserveRate }}%</span>
              </td>
              <td class="table-report-count">{{ allGrades.reportCount }}件</td>
              <td class="table-report-rate0">
                <span v-if="allGrades.totalStudent">{{ allGrades.reportCount0 }}人</span>
                <span v-if="allGrades.totalStudent" class="rate">({{ allGrades.reportRate0 }}%)</span>
              </td>
              <td class="table-report-rate1">
                {{ allGrades.reportCount1 }}人
                <span v-if="allGrades.totalStudent" class="rate">({{ allGrades.reportRate1 }}%)</span>
              </td>
              <td class="table-report-rate2">
                {{ allGrades.reportCount2 }}人
                <span v-if="allGrades.totalStudent" class="rate">({{ allGrades.reportRate2 }}%)</span>
              </td>
            </tr>
          </table>
        </div>
        <div class="plural">
          <h2>朝旗・防パトの報告が合計{{ pluralCount }}回以上の方</h2>
          <div class="plural-result">
            <table v-if="pluralResult.length != 0" class="table-primary">
              <tr class="plural-result-head table-primary-head">
                <th class="table-class">
                  <button type="button" class="underlined" @click="sortClass(classAscSort)">クラス・番号</button>
                </th>
                <th class="table-flag">朝旗</th>
                <th class="table-patrol">防パト</th>
                <th class="table-total">
                  <button type="button" class="underlined" @click="sortTotal(totalAscSort)">合計</button>
                </th>
              </tr>
              <tr v-for="item in pluralResult" :key="item.id" class="plural-result-body table-primary-body">
                <td class="table-class">{{ item.grade }}-{{ item.class }} {{ item.student_no }}番</td>
                <td class="table-flag">{{ item.flagCnt }}回</td>
                <td class="table-patrol">{{ item.patrolCnt }}回</td>
                <td class="table-total">{{ item.flagCnt + item.patrolCnt }}回</td>
              </tr>
            </table>
            <p v-else class="no-data">該当者なし</p>
          </div>
        </div>
      </div>
      <p v-else class="no-data">学年情報が設定されていません</p>
    </div>
    <div class="inner">
      <div class="button-area-lg button-area-flex">
        <router-link :to="{ name: 'AdminMenu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
      </div>
    </div>
  </main>
</template>

<style scoped>
.totalling {
  margin-top: 1rem;
  max-width: 90vw;
  overflow-x: auto;
}
.totalling__header {
  display: flex;
  align-items: center;
  gap: 2rem;
}
.totalling-result-head,
.totalling-result-body {
  display: grid;
  grid-template-columns:
    50px 140px 80px minmax(80px, 1fr) minmax(80px, 1fr) minmax(80px, 1fr) minmax(80px, 1fr) minmax(80px, 1fr)
    minmax(80px, 1fr);
  grid-template-rows: 1fr;
  align-items: center;
}
.small {
  display: block;
  font-size: 10px;
}
.rate {
  display: block;
  font-size: 0.8em;
}

.plural {
  margin-top: 4rem;
}
.plural h2 {
  font-size: 1.2rem;
}
.plural-result table {
  width: auto;
}
.plural-result-head,
.plural-result-body {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  grid-template-rows: 1fr;
  align-items: center;
}
.no-data {
  margin-top: 1rem;
}
</style>
