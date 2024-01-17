<script setup>
import { ref, defineProps, defineEmits, onMounted, watch } from 'vue';
import { supabase } from '../../lib/supabaseClient';

// --------------------------------------
//  初期設定
// --------------------------------------
let today = "";
const holidays = ref([]);
const excludeDays = ref([]);
let year1 = ref("");
let year2 = ref("");
let year3 = ref("");
const schedule1 = ref([]);
const schedule2 = ref([]);
const schedule3 = ref([]);
const gradeInfos = ref([]);
const periods = ref([]);
const periodsOfGrades = ref([]);
const weeks = ["月", "火", "水", "木", "金", "土", "日"];

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  page: String,
  selectedInfo: Object
});
// 親コンポーネントへ渡す値を定義
const emits = defineEmits();



// --------------------------------------
//  内部関数：本日情報（年月日）を取得
// --------------------------------------
const _getCurrentInfo = () => {
  // 今日の日付を取得
  const d = new Date();
  const year = d.getFullYear();
  const month = d.getMonth() + 1;
  const date = d.getDate();
  // ２桁に編集
  const monthFull = _zeroPadding(month, 2)
  const dateFull = _zeroPadding(date, 2)
  today = `${year}-${monthFull}-${dateFull}`;
}

// --------------------------------------
//  内部関数：祝日情報を取得
// --------------------------------------
const _fetchHolidays = async () => {
  try {
    const response = await fetch('https://holidays-jp.github.io/api/v1/date.json');
    if (!response.ok) {
      throw new Error(`HTTP Error: ${response.status}`);
    }
    const data = await response.json();
    holidays.value = data;
  } catch (error) {
    console.error(error);
  }
}

// --------------------------------------
//  内部関数：除外日情報を取得
// --------------------------------------
const _getExclude = async () => {
  const { data } = await supabase.from('patrol_exclude').select();
  excludeDays.value = data;
}

// --------------------------------------
//  内部関数：実施期間を取得
// --------------------------------------
const _getPeriod = async () => {
  // props.pageが文字列reserveで始まる場合
  if (props.page.startsWith("reserve")) {
    // 実施期間をデータベースから取得
    await _getGradeInfo();
    // 実施期間を初期化
    periods.value = [];
    // 学年、開始日、終了日を設定
    _setPeriod(1);
    _setPeriod(2);
  }
}

// 実施期間をデータベースから取得
const _getGradeInfo = async () => {
  const { data } = await supabase
    .from('grade_setting')
    .select()
  gradeInfos.value = data;
}

// 開始日、終了日を設定
const _setPeriod = (num) => {
  //gradeInfosの件数分繰り返し
  for (const gradeInfo of gradeInfos.value) {
    let startYear = gradeInfo.year;
    let endYear = gradeInfo.year;
    let startMonth = "";
    let startDay = "";
    let endMonth = "";
    let endDay = "";
    let startFullDate = "";
    let endFullDate = "";
    if (num == 1) {
      startMonth = gradeInfo.period1_start_month;
      startDay = gradeInfo.period1_start_day;
      endMonth = gradeInfo.period1_end_month;
      endDay = gradeInfo.period1_end_day;
    } else if (num == 2) {
      startMonth = gradeInfo.period2_start_month;
      startDay = gradeInfo.period2_start_day;
      endMonth = gradeInfo.period2_end_month;
      endDay = gradeInfo.period2_end_day;
    } else {
      return;
    }
    // １〜３月の場合は、yearに１を足す
    if (startMonth >= 1 && startMonth <= 3) {
      startYear = parseInt(startYear) + 1;
    }
    if (endMonth >= 1 && endMonth <= 3) {
      endYear = parseInt(endYear) + 1;
    }
    if (startMonth) {
      // 月日を２桁に編集
      startMonth = _zeroPadding(startMonth, 2);
      startDay = _zeroPadding(startDay, 2);
      endMonth = _zeroPadding(endMonth, 2);
      endDay = _zeroPadding(endDay, 2);

      startFullDate = `${startYear}-${startMonth}-${startDay}`;
      endFullDate = `${endYear}-${endMonth}-${endDay}`;

      // 実施期間を設定
      periods.value.push({
        grade: gradeInfo.grade,
        start: startFullDate,
        end: endFullDate
      });
    }

  }
}


// --------------------------------------
//  内部関数：防パトスケジュールを生成
// --------------------------------------
const _generateSchedule = (year) => {
  // 該当年の水曜日、土曜日の日付を取得
  const newSchedule = [];
  // 1月1日から12月31日まで繰り返し
  for (let month = 1; month <= 12; month++) {
    for (let date = 1; date <= 31; date++) {
      // 曜日を取得
      const weekdayNo = new Date(`${year}/${month}/${date}`).getDay();
      // 水曜日と土曜日の場合
      if (weekdayNo == 3 || weekdayNo == 6) {
        // 年月日取得(yyyy-mm-dd)
        const monthFull = _zeroPadding(month, 2);
        const dayFull = _zeroPadding(date, 2);
        const fullDate = `${year}-${monthFull}-${dayFull}`;
        // 曜日を取得
        let weekday = "";
        if (weekdayNo == 3) {
          weekday = "水";
        } else if (weekdayNo == 6) {
          weekday = "土";
        }
        const displayDate = `${month}/${date}(${weekday})`;
        // 配列に追加
        newSchedule.push({
          fullDate: fullDate,
          displayDate: displayDate,
          label: "",
          isAble: true,
          exclusionFlg: false,
          tabIndex: "0",
        });
      }
    }
  }
    // newScheduleの件数分繰り返し
  for (const schedule of newSchedule) {
    // 祝日チェック
    Object.keys(holidays.value).forEach(date => {
      if (date === schedule.fullDate) {
        // (水)→(水・祝)、(土)→(土・祝)に置換
        schedule.displayDate = schedule.displayDate.replace(")", "・祝)");
      }
    });
    // 過去日チェック
    if (schedule.fullDate < today) {
      schedule.label = "ー";
      schedule.isAble = false;
      schedule.tabIndex = "-1";
    } else {
      // 除外日チェック
      if (Object.values(excludeDays.value).some(item => item.exclude_day === schedule.fullDate)) {
        schedule.label = "ー";
        schedule.isAble = false;
        schedule.exclusionFlg = true;
        schedule.tabIndex = "-1";
      }
    }
  }
  return newSchedule;
}

// --------------------------------------
//  初期表示
// --------------------------------------
onMounted(() => {
  // 祝日情報を取得
  _fetchHolidays()
  // 除外日情報を取得
  _getExclude()
  // 実施期間を取得
  _getPeriod()

    .then(() => {
      // 本日の情報を取得
      _getCurrentInfo();
      // 本日より３年分のスケジュールを生成
      year1 = today.substring(0, 4);
      year2 = parseInt(year1) + 1;
      year3 = parseInt(year1) + 2;
      schedule1.value = _generateSchedule(year1);
      schedule2.value = _generateSchedule(year2);
      schedule3.value = _generateSchedule(year3);
    })
    .catch(error => {
      console.error('スケジュールの生成に失敗しました:', error);
    });
});

// --------------------------------------
//  予約情報を取得（条件：ポイントID）
// --------------------------------------
const getReservation = async (point_id) => {

  let maxNumber = 0;
  //  防パトエリア情報を取得
  const { data } = await supabase
    .from('patrol_point')
    .select()
    .eq('point_id', `${point_id}`)
  maxNumber = data[0].max_number;

  // 予約情報を取得
  await _getReservationLoop(schedule1,maxNumber);
  await _getReservationLoop(schedule2,maxNumber);
  await _getReservationLoop(schedule3,maxNumber);
}

const _getReservationLoop = async (target,maxNumber) => {
  let count = 0;
// scheduleの件数分繰り返し
  for (const day of target.value) {
    if (day.isAble) {
      const year = day.fullDate.substr(0, 4);
      const month = day.fullDate.substr(5, 2);
      const date = day.fullDate.substr(8);
      // 予約情報を取得
      const { data } = await supabase
        .from('patrol_reservation')
        .select()
        .eq('point_id', `${point_id}`)
        .eq('year', `${year}`)
        .eq('month', `${month}`)
        .eq('day', `${date}`)
      count = Num(maxNumber) - Num(data.length);
      if (count > 0) {
        day.label = `あと${count}名`;
      } else {
        day.label = "✕";
        day.isAble = false;
        day.tabIndex = "-1";
      }
    }
  }
}

// --------------------------------------
//  内部関数：スケジュール生成＆予約情報取得
// --------------------------------------
const _regenerateSchedule = (grade) => {
  // 除外日情報再取得
  _getExclude();
  // スケジュール生成
  schedule.value = _generateSchedule(grade);
  if (props.page.startsWith("reserve")) {
    // 予約情報を取得
    getReservation(props.selectedInfo.pointID);
  } else if (props.page === "exclude-date") {
    // 曜日チェックボックスの初期化
    const checkboxes = document.querySelectorAll(".line-checkbox");
    checkboxes.forEach((checkbox) => {
      checkbox.checked = false;
    });
  }
}

// --------------------------------------
//  日付選択
// --------------------------------------
const selectDate = (date) => {
  // 曜日を取得
  let weekdayNo = new Date(date).getDay();
  if (weekdayNo == 0) {
    weekdayNo = 6;
  } else {
    weekdayNo--;
  }
  // selectedInfoを更新
  props.selectedInfo.year = date.substr(0, 4);
  props.selectedInfo.month = date.substr(5, 2);
  props.selectedInfo.day = date.substr(8);
  props.selectedInfo.weekDay = weeks[weekdayNo];
  // 親コンポーネントにカスタムイベントを伝える
  emits('selectDate', date);
}

// --------------------------------------
//  除外日設定
// --------------------------------------
const excludeSetting = async() => {
  // 確認メッセージ
  if (!confirm("除外日を設定しますか？")) {
    return;
  }

  // 除外日データのクリア
  await _clearExclude();
  // 除外日を登録
  _setExclude();

  // メッセージ表示
  alert("除外日を設定しました。");
}

// 除外日データのクリア
const _clearExclude = async () => {
  // scheduleの件数分繰り返し
  for (const day of schedule.value) {
    // 除外日を削除
    await supabase
      .from('patrol_exclude')
      .delete()
      .eq('exclude_day', `${day.fullDate}`)
  }
}

// 除外日を登録
const _setExclude = () => {
  // 除外日を取得
  const excludeDays = schedule.value.filter((day) => day.isExclusion);
  // 除外日をDBに登録
  excludeDays.forEach((day) => {
    if (day.fullDate) {
      supabase
        .from('patrol_exclude')
        .insert([
          { exclude_day: day.fullDate }
        ])
        .then(() => {
        })
        .catch(error => {
          console.error('除外日の登録に失敗しました:', error);
        });
      }
  });
}

// --------------------------------------
//  内部関数：0埋め関数（Param:変換する数字,桁数）
// --------------------------------------
const _zeroPadding = (num, len) => String(num).padStart(len, "0");

// --------------------------------------
//  監視（予約ID or ポイントIDが変更されたら予約情報を更新）
// --------------------------------------
if (props.page.startsWith("reserve")) {
  watch(() => [props.selectedInfo.id, props.selectedInfo.pointID], ([newID, newPointID], [oldID, oldPointID]) => {
    if (newID !== oldID || newPointID !== oldPointID) {
      getReservation(newPointID);
    }
  });
}

// --------------------------------------
//  監視（学年が変更されたら実施期間を更新）
// --------------------------------------
if (props.page === "reserve-patrol") {
  watch(() => props.selectedInfo.grade, async(newValue, oldValue) => {
    if (newValue !== oldValue) {
      _updatePeriod();
    }
  });
}

const _updatePeriod = () => {
  // 初期化
  let displayStart = "";
  let displayEnd = "";
  periodsOfGrades.value = [];
  // 選択された学年の直近の実施月を取得 → 初期表示
  // periodsの件数分繰り返し
  for (const period of periods.value) {
    if (props.selectedInfo.grade == period.grade) {
      // 終了日が過去日でない場合
      if (period.end >= today) {
        // period.start,period.endを置換したものをdisplayStart,displayEndに設定
        // 月日の0を削除
        displayStart = period.start.replace(/-0/g, "- ");
        displayEnd = period.end.replace(/-0/g, "- ");
        // -を/に変換
        displayStart = displayStart.replace(/-/g, "/");
        displayEnd = displayEnd.replace(/-/g, "/");
        // start,endが今年の場合、年を削除
        if (displayStart.substring(0, 4) == today.substring(0, 4)) {
          displayStart = displayStart.substring(5);
        }
        if (displayEnd.substring(0, 4) == today.substring(0, 4)) {
          displayEnd = displayEnd.substring(5);
        }
        // 実施期間を設定
        periodsOfGrades.value.push({
          start: displayStart,
          end: displayEnd
        });
      }
    }
  }
  // スケジュール生成 & 予約情報を取得
  _regenerateSchedule(props.selectedInfo.value.grade);
}

// --------------------------------------
//  親コンポーネントに公開するメソッドを定義
// --------------------------------------
defineExpose({
  getReservation
});

</script>

<template>
  <div class="schedule" :class="page">
    <p v-if="page === 'reserve-patrol'" class="period-message">
      {{ selectedInfo.grade }}年生の担当期間は
      <ol class="period-list">
        <li v-for="(period,index) in periodsOfGrades" :key=index>
          <span class="period">{{period.start}} 〜 {{period.end}}</span>
        </li>
      </ol>
      です。
    </p>
    <!-- スケジュールメイン -->
    <ol>
      <li>
        <p>{{ year1 }}年</p>
        <ol class="body">
          <li v-for="(list, index) in schedule1" :key="index" class="item" :class="{
            'is-able': list.isAble,
            'is-space': list.isSpace,
            'is-holiday': list.isHoliday,
            'is-exclusion': list.isExclusion
          }">
            <button type="button" class="item--btn js-modal-trigger" :tabindex="list.tabIndex" @click="selectDate(list.fullDate)">
              <span class="item--date">{{list.displayDate}}</span>
              <span v-if="page.includes('reserve')" class="item--label">{{list.label}}</span>
            </button>
            <input v-if="page === 'exclude-date'" type="checkbox" name="exclude" id="exclude" :checked="list.isExclusion" v-model="list.isExclusion">
          </li>
        </ol>
      </li>
      <li>
        <p>{{ year2 }}年</p>
        <ol class="body">
          <li v-for="(list, index) in schedule2" :key="index" class="item" :class="{
            'is-able': list.isAble,
            'is-space': list.isSpace,
            'is-holiday': list.isHoliday,
            'is-exclusion': list.isExclusion
          }">
            <button type="button" class="item--btn js-modal-trigger" :tabindex="list.tabIndex" @click="selectDate(list.fullDate)">
              <span class="item--date">{{list.displayDate}}</span>
              <span v-if="page.includes('reserve')" class="item--label">{{list.label}}</span>
            </button>
            <input v-if="page === 'exclude-date'" type="checkbox" name="exclude" id="exclude" :checked="list.isExclusion" v-model="list.isExclusion">
          </li>
        </ol>
      </li>
      <li>
        <p>{{ year3 }}年</p>
        <ol class="body">
          <li v-for="(list, index) in schedule3" :key="index" class="item" :class="{
            'is-able': list.isAble,
            'is-space': list.isSpace,
            'is-holiday': list.isHoliday,
            'is-exclusion': list.isExclusion
          }">
            <button type="button" class="item--btn js-modal-trigger" :tabindex="list.tabIndex" @click="selectDate(list.fullDate)">
              <span class="item--date">{{list.displayDate}}</span>
              <span v-if="page.includes('reserve')" class="item--label">{{list.label}}</span>
            </button>
            <input v-if="page === 'exclude-date'" type="checkbox" name="exclude" id="exclude" :checked="list.isExclusion" v-model="list.isExclusion">
          </li>
        </ol>
      </li>
    </ol>
    <div class="button-area">
      <button v-if="page === 'exclude-date'" class="button" type="button" @click="excludeSetting">除外日設定</button>
    </div>
  </div>

</template>

<style scoped>
.schedule {
  padding: 2rem 1rem;
  background-color: #fff;
  text-align: center;
}

/* 実施期間 */
.period-message {
  margin-bottom: 1rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5em;
  text-align: left;
  line-height: 2;
}
.period-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5em;
  width: fit-content;
}
.period {
  padding: 0.5em;
  font-weight: bold;
  border-radius: 0.5em;
  background-color: var(--color-light-orange);
}

/* スケジュール全体 */
.body{
  margin-top: 1rem;
  width:100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2px;
  text-align: center;
}

.item{
  min-height: 3rem;
  text-align: center;
  cursor: default;
  pointer-events: none;
}

/* スケジュール（日付エリア） */
.item--btn {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 0.3em;
  width: 100%;
  padding: 0.5rem 0;
}

/* 選択OK */

.reserve-patrol .item.is-able {
  cursor: pointer;
  pointer-events: initial;
}

/* 選択NG */
.item:not(.is-able) {
  pointer-events: none;
}

/* チェックボックス */
input[type="checkbox"] {
  width: 1em;
  height: 1em;
  cursor: pointer;
  pointer-events: initial;
}

</style>
