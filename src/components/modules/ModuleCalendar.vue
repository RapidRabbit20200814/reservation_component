<script setup>
import { ref, defineEmits, onMounted, watch } from 'vue';
import { supabase } from '../../lib/supabaseClient';

// --------------------------------------
//  初期設定
// --------------------------------------
let today = "";
let targetDate = "";
let targetYear = "";
let targetMonth = "";
const currentInfo = ref({
  year: null,
  month: null
});
const holidays = ref([]);
const excludeDays = ref([]);
const reservations = ref([]);
const calendar = ref([]);
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

  // currentInfoを更新
  currentInfo.value = {
    year: year,
    month: month
  };
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
  const { data } = await supabase.from('flag_exclude').select();
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
//  内部関数：カレンダーを生成
// --------------------------------------
const _generateCalendar = (year, month) => {
  const newCalendar = [];
  // 月初の曜日を取得
  const firstDay = new Date(year, month - 1, 1).getDay();
  // 前月の空白スペースの数を計算
  const necessarySpace = firstDay == 0 ? 6 : firstDay - 1;
  // 月末の日付を取得
  const lastDate = new Date(year, month, 0).getDate();
  // カレンダー情報編集
  const maxCnt = parseInt(necessarySpace) + parseInt(lastDate);
  for (let cnt = 1; cnt <= maxCnt; cnt++) {
    let date = ""; // 日
    let label = ""; // ラベル（◯：予約可/✕：予約済/ー：予約不可）
    let isAble = false; // 予約可 or 不可フラグ
    let tabIndex = "-1"; // タブ遷移(0:可、-1:不可)
    let isSpace = true; // 空白フラグ
    // 空白スペースではない場合
    if (cnt > necessarySpace) {
      date = cnt - necessarySpace;
      label = "◯";
      isAble = true;
      tabIndex = "0";
      isSpace = false;
    }
    // 年月日取得(yyyy-mm-dd)
    const monthFull = _zeroPadding(month, 2);
    const dateFull = _zeroPadding(date, 2);
    const fullDate = date ? `${year}-${monthFull}-${dateFull}` : "";
    // 今日
    const isToday = fullDate === today;
    // 過去日チェック
    if (fullDate < today && label != "") {
      label = "ー";
      isAble = false;
      tabIndex = "-1";
    }
    // 祝日チェック
    let holidayFlg = false;
    Object.keys(holidays.value).forEach(date => {
      if (date === fullDate) {
        holidayFlg = true;
      }
    });
    // 除外日チェック
    let exclusionFlg = false;
    if (Object.values(excludeDays.value).some(item => item.exclude_day === fullDate)) {
      exclusionFlg = true;
      label = "ー";
      isAble = false;
      tabIndex = "-1";
    }
    // 実施期間チェック
    let isTarget = false;
    if (periods.value.length > 0) {
      for (const period of periods.value) {
        if (props.selectedInfo.grade == period.grade && fullDate >= period.start && fullDate <= period.end) {
          isTarget = true;
          break;
        }
      }
    }
    // 配列に追加
    newCalendar.push({
      date: date,
      fullDate: fullDate,
      label: label,
      isAble: isAble,
      isSpace: isSpace,
      isToday: isToday,
      isHoliday: holidayFlg,
      isExclusion: exclusionFlg,
      isTarget: isTarget,
      tabIndex: tabIndex
    });
  }
  return newCalendar;
}

// --------------------------------------
//  初期表示
// --------------------------------------
onMounted(() => {
  // 祝日情報を取得
  _fetchHolidays()
    .then(() => {
      // 除外日情報を取得
      return _getExclude();
    })
    .then(() => {
      // 実施期間を取得
      return _getPeriod();
    })
    .then(() => {
      // 本日の情報を取得
      return _getCurrentInfo();
    })
    .then(() => {
      // カレンダー生成
      if (props.page === "reserve-flag-grade") {
        _setTargetMonth();
      } else {
        calendar.value = _generateCalendar(currentInfo.value.year, currentInfo.value.month);
      }
      // つどさぽの場合は予約情報を検索する
      if (props.page === "reserve-flag-support") {
        getReservation(props.selectedInfo.pointID, currentInfo.value.year, currentInfo.value.month);
      }
    })
    .catch(error => {
      console.error('カレンダーの生成に失敗しました:', error);
    });
});


// --------------------------------------
//  予約情報を取得（条件：ポイントID、年、月）
// --------------------------------------
const getReservation = async (point_id, year, month) => {

  if (point_id && year && month) {
    const { data } = await supabase
      .from('flag_reservation')
      .select()
      .eq('point_id', `${point_id}`)
      .eq('year', `${year}`)
      .eq('month', `${month}`);

    reservations.value = data;

    // 予約済の日付に✕をつける
    calendar.value.forEach((day) => {
      // ✕ → ◯に戻す（初期化）
      if (day.label === "✕") {
        day.isReserved = false;
        day.isAble = true;
        day.label = "◯";
      }
      // 予約情報と一致する日付には✕をつける
      const matchingReservation = reservations.value.find((reservation) => reservation.day === day.date);
      if (matchingReservation) {
        day.isReserved = true;
        day.isAble = false;
        day.label = "✕";
      }
    });
  }
}

// --------------------------------------
//  前月へ移動
// --------------------------------------
const movePrevMonth = () => {
  // 月を１つ前にする
  currentInfo.value.month--;
  // 月が0になったら、年を１つ前にする
  if (currentInfo.value.month == 0) {
    currentInfo.value.year--;
    currentInfo.value.month = 12;
  }
  // カレンダー生成 & 予約情報を取得
  _regenerateCalendar(currentInfo.value.year, currentInfo.value.month);
}

// --------------------------------------
//  次月へ移動
// --------------------------------------
const moveNextMonth = () => {
  // 月を１つ後にする
  currentInfo.value.month++;
  // 月が13になったら、年を１つ後にする
  if (currentInfo.value.month == 13) {
    currentInfo.value.year++;
    currentInfo.value.month = 1;
  }
  // カレンダー生成 & 予約情報を取得
  _regenerateCalendar(currentInfo.value.year, currentInfo.value.month);
}

// --------------------------------------
//  内部関数：カレンダー生成＆予約情報取得
// --------------------------------------
const _regenerateCalendar = (year, month) => {
  // 除外日情報再取得
  _getExclude();
  // カレンダー生成
  calendar.value = _generateCalendar(year, month);
  if (props.page.startsWith("reserve")) {
    // 予約情報を取得
    getReservation(props.selectedInfo.pointID, year, month);
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
//  除外日一括チェック
// --------------------------------------
const checkLine = (e) => {
  const id = e.target.id;
  const index = id.replace("checkbox", "");
  const checked = e.target.checked;

  // 選択した列すべてのチェックボックスを変更する
  calendar.value.forEach((day, i) => {
    if (i % 7 == index) {
      day.isExclusion = checked;
    }
  });
}

// --------------------------------------
//  除外日設定
// --------------------------------------
const excludeSetting = () => {
  // 確認メッセージ
  if (!confirm("除外日を設定しますか？")) {
    return;
  }
  // monthを２桁に編集
  const monthFull = _zeroPadding(currentInfo.value.month, 2);
  // 対象月の除外日を削除
  supabase
    .from('flag_exclude')
    .delete()
    .like('exclude_day', `${currentInfo.value.year}-${monthFull}-%`)
    .then(() => {
      // 除外日を登録
      _setExclude();
    })
    .catch(error => {
      console.error('除外日の削除に失敗しました:', error);
    });
  // メッセージ表示
  alert("除外日を設定しました。");
}

// 除外日を登録
const _setExclude = () => {
  // 除外日を取得
  const excludeDays = calendar.value.filter((day) => day.isExclusion);
  // 除外日をDBに登録
  excludeDays.forEach((day) => {
    if (day.fullDate) {
      supabase
        .from('flag_exclude')
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
      getReservation(newPointID, currentInfo.value.year, currentInfo.value.month);
    }
  });
}

// --------------------------------------
//  監視（学年が変更されたら実施期間を更新）
// --------------------------------------
if (props.page === "reserve-flag-grade") {
  watch(() => props.selectedInfo.grade, async (newValue, oldValue) => {
    if (newValue !== oldValue) {
      _setTargetMonth();
    }
  });
}

const _setTargetMonth = () => {
  // 初期化
  let displayStart = "";
  let displayEnd = "";
  targetDate = "";
  targetYear = "";
  targetMonth = "";
  periodsOfGrades.value = [];
  // 選択された学年の直近の実施月を取得 → 初期表示
  // periodsの件数分繰り返し
  for (const period of periods.value) {
    if (props.selectedInfo.grade == period.grade) {
      // 終了日が過去日でない場合
      if (period.end >= today) {
        // period.start,period.endを置換したものをdisplayStart,displayEndに設定
        // 月日の0を削除
        displayStart = period.start.replace(/-0/g, "-");
        displayEnd = period.end.replace(/-0/g, "-");
        // -を/に変換
        displayStart = displayStart.replace(/-/g, " / ");
        displayEnd = displayEnd.replace(/-/g, " / ");
        // start,endが今年の場合、年を削除
        if (displayStart.substring(0, 4) == today.substring(0, 4)) {
          displayStart = displayStart.substring(6);
        }
        if (displayEnd.substring(0, 4) == today.substring(0, 4)) {
          displayEnd = displayEnd.substring(6);
        }
        // 実施期間を設定
        periodsOfGrades.value.push({
          start: displayStart,
          end: displayEnd
        });
        // 初期表示させる月（targetData）を設定
        if (targetDate == "" || targetDate > period.start) {
          targetDate = period.start;
          targetYear = targetDate.slice(0, 4);
          targetMonth = targetDate.slice(5, 7);
        }
      }
    }
  }

  if (targetMonth) {
    //targetMonthの１文字目が0の場合、0を削除
    if (targetMonth.slice(0, 1) == "0") {
      targetMonth = targetMonth.slice(1);
    }
    currentInfo.value.year = targetYear;
    currentInfo.value.month = targetMonth;
    props.selectedInfo.year = targetYear;
    props.selectedInfo.month = targetMonth;
  }
  // カレンダー生成 & 予約情報を取得
  _regenerateCalendar(currentInfo.value.year, currentInfo.value.month);
}

// --------------------------------------
//  親コンポーネントに公開するメソッドを定義
// --------------------------------------
defineExpose({
  getReservation
});

</script>

<template>
  <div class="bg-white calender" :class="page">
    <p v-if="page === 'reserve-flag-grade'" class="period-message">
      {{ selectedInfo.grade }}年生の担当期間は
      <ol class="period-list">
        <li v-for="(period, index) in periodsOfGrades" :key=index>
          <span class="period">{{ period.start }} 〜 {{ period.end }}</span>
        </li>
      </ol>
      です。
    </p>
    <!-- カレンダータイトル -->
    <div class="title">
      <button type="button" class="move-month move-month--prev" @click="movePrevMonth"><span></span></button>
      <span v-if="currentInfo">{{ currentInfo.year }} / {{ currentInfo.month }}</span>
      <button type="button" class="move-month move-month--next" @click="moveNextMonth"><span></span></button>
    </div>
    <!-- カレンダーメイン -->
    <ol class="body">
      <!-- カレンダー曜日 -->
      <li v-for="(day, index) in weeks" :key=index class="item item--day">{{ day }}<input v-if="page === 'exclude-date'" type="checkbox" :name="'checkbox' + index" :id="'checkbox' + index" class="line-checkbox" @change="checkLine"></li>
      <!-- カレンダー日付 -->
      <li v-for="(list, index) in calendar" :key="index" class="item" :class="{
        'is-able': list.isAble,
        'is-space': list.isSpace,
        'is-holiday': list.isHoliday,
        'is-exclusion': list.isExclusion,
        'is-reserved': list.isReserved,
        'is-target': list.isTarget,
      }">
        <button type="button" class="item--btn js-modal-trigger" :tabindex="list.tabIndex" @click="selectDate(list.fullDate)">
          <span class="item--date" :class="{ 'is-today': list.isToday }">{{ list.date }}</span>
          <span v-if="page.includes('reserve')" class="item--label">{{ list.label }}</span>
        </button>
        <input v-if="page === 'exclude-date'" type="checkbox" name="exclude" id="exclude" :checked="list.isExclusion" v-model="list.isExclusion">
      </li>
    </ol>
    <div class="button-area">
      <button v-if="page === 'exclude-date'" class="button" type="button" @click="excludeSetting">除外日設定</button>
    </div>
  </div>

</template>

<style scoped>
.calender {
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

/* タイトル（＜ 年/月 ＞） */
.title {
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  font-size: 1.4rem;
}

.move-month {
  margin: 0.5em;
  padding: 0 1em;
}

.move-month span {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-top: solid 1px var(--color-font-base);
  border-right: solid 1px var(--color-font-base);
  ;
}

.move-month--prev span {
  transform: translateY(-50%) rotate(-135deg);
}

.move-month--next span {
  transform: translateY(-50%) rotate(45deg);
}

/* カレンダー全体 */
.body {
  margin-top: 1rem;
  width: 100%;
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
  text-align: center;
}

.item {
  min-height: 3rem;
  text-align: center;
  background-color: var(--color-light-gray-shadow);
  border-bottom: solid 5px var(--color-light-gray-shadow);
  cursor: default;
  pointer-events: none;
}

/* カレンダー（曜日エリア） */
.item.item--day {
  min-height: 1.5rem;
  background-color: transparent;
  border-bottom: transparent;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5em;
}

/* カレンダー（日付エリア） */
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
.item.is-able {
  background-color: var(--color-light-gray);
  border-bottom: solid 5px var(--color-light-gray-shadow);
  border-radius: 8px;
}

.item.is-able.is-target {
  background-color: var(--color-light-orange);
  border-bottom: solid 5px var(--color-light-orange-shadow);
  font-weight: bold;
  opacity: 1;
}

.reserve-flag-grade .item,
.reserve-flag-support .item {
  opacity: 0.8;
}

.reserve-flag-grade .item.is-able,
.reserve-flag-support .item.is-able {
  cursor: pointer;
  pointer-events: initial;
}

@media (hover: hover) {

  .reserve-flag-grade .item.is-able:is(:hover, :focus-visible),
  .reserve-flag-support .item.is-able:is(:hover, :focus-visible) {
    opacity: 0.8;
  }
}

/* 選択NG */
.item:not(.is-able) {
  border-radius: 0;
  pointer-events: none;
}

/* 除外日 */
.item.is-exclusion {
  background-color: var(--color-light-gray-shadow);
  border-bottom: solid 5px var(--color-light-gray-shadow);
}

/* 予約済 */
.item.is-reserved {
  background-color: var(--color-light-gray-shadow);
  border-bottom: solid 5px var(--color-light-gray-shadow);
}

/* 日付なし */
.item.is-space {
  background-color: transparent;
  border-bottom: transparent;
}

.item.is-space input[type="checkbox"] {
  display: none;
}

/* 土曜日 */
.item:nth-of-type(7n-1):not(.is-space, .is-holiday, .item--day) {
  background-color: var(--color-light-blue-shadow);
  border-bottom: solid 5px var(--color-light-blue-shadow);
}

.item:nth-of-type(7n-1):is(.is-able):not(.is-space, .is-holiday, .item--day) {
  background-color: var(--color-light-blue);
  border-bottom: solid 5px var(--color-light-blue-shadow);
  ;
}

/* 日曜日 */
.item:nth-of-type(7n):not(.is-space, .item--day),
.item.is-holiday {
  background-color: var(--color-light-pink-shadow);
  border-bottom: solid 5px var(--color-light-pink-shadow);
}

.item:nth-of-type(7n):is(.is-able):not(.is-space, .item--day),
.item.is-holiday:not(.is-exclusion) {
  background-color: var(--color-light-pink);
  border-bottom: solid 5px var(--color-light-pink-shadow);
}

/* 日付 */
.item--date {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 1.7em;
  height: 1.7em;
  border-radius: 50%;
}

/* 本日 */
.item--date.is-today {
  background-color: #888;
  color: #fff;
}

/* チェックボックス */
input[type="checkbox"] {
  width: 1em;
  height: 1em;
  cursor: pointer;
  pointer-events: initial;
}
</style>
