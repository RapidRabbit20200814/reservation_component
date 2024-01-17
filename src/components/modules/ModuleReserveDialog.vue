<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import { supabase } from '../../lib/supabaseClient';

// 学年
const grades = ref(["1", "2", "3", "4", "5", "6"]);
// クラス
const classes = ref(["未定", "1", "2", "3", "4", "5", "6"]);
// 出席番号(1~40)
const numbers = ref(["未定"]);
for (let i = 1; i <= 40; i++) {
  numbers.value.push(i.toString());
}

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object
});
// 親コンポーネントへ渡す値を定義
const emits = defineEmits();

// 学年が選択済みの場合は、学年をdisabledにする
const gradeDisabled = ref(false);
if (props.selectedInfo.grade) {
  gradeDisabled.value = true;
}

// --------------------------------------
//  モーダルを閉じる
// --------------------------------------
const closeModal = () => {
  const modal = document.querySelector("#reserve-modal");
  modal.close();
}

// --------------------------------------
//  Local Storageにデータ追加
// --------------------------------------
const storageAdd = () => {
    // local storageから既存のデータを取得
  let storageData = JSON.parse(localStorage.getItem("myReservations"));

  // データがまだ存在しない場合は、新しい配列を作成
  if (!storageData) {
    storageData = [];
  }

  // 新しいデータを追加
  const jsonData = {
    id: props.selectedInfo.id,
    point_id: props.selectedInfo.pointID,
    point_no: props.selectedInfo.pointNo,
    point_name: props.selectedInfo.pointName,
    year: props.selectedInfo.year,
    month: props.selectedInfo.month,
    day: props.selectedInfo.day,
    grade: props.selectedInfo.grade,
    class: props.selectedInfo.class,
    student_no: props.selectedInfo.number
  }
  storageData.push(jsonData);

  // データを再度保存
  localStorage.setItem("myReservations", JSON.stringify(storageData));
}

// --------------------------------------
//  予約
// --------------------------------------
const reserve = async () => {
  // 必須チェック
  if (!props.selectedInfo.grade || !props.selectedInfo.class || !props.selectedInfo.number) {
    alert('学年、クラス、出席番号を入力してください');
    return;
  }

  // 確認メッセージ
  if (!confirm('予約しますか？')) {
    return;
  }

  // 既存データとconflictしないかチェック
  let point_id = props.selectedInfo.pointID;
  let year = props.selectedInfo.year;
  let month = props.selectedInfo.month;
  let day = props.selectedInfo.day;
  // １文字目が0の場合は、２文字目を取得
  if (month.substr(0, 1) === '0') {
    month = month.substr(1, 1);
  }
  if (day.substr(0, 1) === '0') {
    day = day.substr(1, 1);
  }
  const { data } = await supabase
      .from('flag_reservation')
      .select()
      .eq('point_id', `${point_id}`)
      .eq('year', `${year}`)
      .eq('month', `${month}`)
      .eq('day', `${day}`);
  console.log(data);
  // 既存データがある場合は、エラー
  if (data.length > 0) {
    alert('予約がいっぱいになりました。お手数ですが別の日程で再度ご予約ください。');
    // 親コンポーネントにカスタムイベントを伝える
    emits('conflict', true);
  } else {
    // 予約データを登録
    const { data, error } = await supabase
      .from('flag_reservation')
      .upsert([
        {
          point_id: props.selectedInfo.pointID,
          year: props.selectedInfo.year,
          month: props.selectedInfo.month,
          day: props.selectedInfo.day,
          grade: props.selectedInfo.grade,
          class: props.selectedInfo.class,
          student_no: props.selectedInfo.number
        },
      ])
      .select();

    if (error) {
      console.error('データの挿入エラー:', error);
    } else {
      // 予約IDを設定
      props.selectedInfo.id = data[0].id;

      // Local Storageにデータ追加
      storageAdd();
    }

    // メッセージを表示
    alert('予約しました');
  }

  // モーダルを閉じる
  closeModal();
}

</script>

<template>
    <dialog id="reserve-modal" class="modal">
      <button type="button" class="modal__close" @click="closeModal()">✕</button>
      <form class="form">
        <p class="form__label">{{props.selectedInfo.pointNo}}：{{props.selectedInfo.pointName}}</p>
        <p class="form__label">{{props.selectedInfo.year}}-{{props.selectedInfo.month}}-{{props.selectedInfo.day}}（{{props.selectedInfo.weekDay}}）</p>
        <input type="hidden" name="point" id="point" :value="props.selectedInfo.pointID" class="form__input">
        <hr class="form__divide">
        <dl>
          <div class="form__row">
            <dt class="form__head"><span class="form__ttl">学年</span></dt>
            <dd class="form__data">
              <select id="grade" v-model="props.selectedInfo.grade" :disabled="gradeDisabled">
                <option v-for="label, index in grades" :value="label" :key="index">{{ label }}</option>
              </select> 年
            </dd>
          </div>
          <div class="form__row">
            <dt class="form__head"><span class="form__ttl">クラス</span></dt>
            <dd class="form__data">
              <select id="class" v-model="props.selectedInfo.class">
                <option v-for="label, index in classes" :value="label" :key="index">{{ label }}</option>
              </select> 組
            </dd>
          </div>
          <div class="form__row">
            <dt class="form__head"><span class="form__ttl">出席番号</span></dt>
            <dd class="form__data">
              <select id="student_no" v-model="props.selectedInfo.number">
                <option v-for="label, index in numbers" :value="label" :key="index">{{ label }}</option>
              </select> 番
            </dd>
          </div>
        </dl>
        <p class="undecided-message">クラス・出席番号が決まっていない場合は「未定」を選んでください。</p>
        <div class="form__attention">
          <p class="form__attention-head">必ずお読みください</p>
          <ul class="form__attention-list">
            <li>
              当日急遽都合が悪くなった場合は無理に実施する必要はありません。連絡も不要です。
            </li>
            <li>
              お子様連れも可能ですが、くれぐれも無理のない範囲でご参加ください。
            </li>
            <li>
              学校が休校の場合は、防パト／朝旗は中止とします（中止の際は役員会からご連絡します）。
            </li>
          </ul>
        </div>
        <div class="form__btn">
          <button type="button" class="button js-reserve" @click="reserve()"><span>予約する</span></button>
        </div>
      </form>
    </dialog>
</template>

<style scoped>

.modal {
  position: relative;
  padding: 2rem;
  border: none;
}
.modal__close {
  position: absolute;
  top: 0;
  right: 0;
  display: block;
  width: 2rem;
  height: 2rem;
  background-color: var(--color-accent);
  color: #fff;
}

.form {
  margin-top: 2rem;
}
.form__row {
  display: grid;
  grid-template-columns: 6rem 1fr;
  align-items: center;
}
.form__label + .form__label{
  margin-top: 1rem;
}
.form__row + .form__row {
  margin-top: 1.5rem;
}
.form__divide {
  margin-block: 1.5rem;
}
.form__btn {
  margin-top: 2rem;
  text-align: center;
}
.form__attention {
  margin-top: 2rem;
  padding: 1em;
  border: 1px solid red;
  max-width: 500px;
  border-radius: 0.5em;
}
.form__attention-head {
  margin-bottom: 1em;
  font-weight: bold;
  color: red;
}
.form__attention-list{
  list-style: disc;
  padding-left: 1em;
}
.form__attention-list li{
  line-height: 1.6;
}
.form__attention-list li + li {
  margin-top: 0.7em;
}
.undecided-message {
  margin-top: 1rem;
}
/* PC */
@media (min-width: 768px) {
  .modal {
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%);
  }
}
</style>
