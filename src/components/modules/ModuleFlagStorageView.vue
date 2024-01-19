<script setup>
import { ref, defineProps, defineEmits, onMounted, watch } from "vue";
import { supabase } from "../../lib/supabaseClient";

let storageData = ref([]);

// 親コンポーネントから受け取る値を定義
const props = defineProps({
  selectedInfo: Object,
});
// 親コンポーネントへ渡す値を定義
const emits = defineEmits();

// --------------------------------------
//  予約データ削除
// --------------------------------------
const deleteData = async (event) => {
  // 確認メッセージを表示
  const result = confirm("予約を取り消しますか？");
  if (!result) {
    return;
  }
  try {
    // 選択された行のindexを取得
    const index = event.target.parentNode.parentNode.rowIndex;
    // local storageからデータ削除
    const deleteID = _deleteStorageData(index);
    // local storageから既存のデータを取得
    _storageView();
    // DBよりデータ削除
    await _deleteDBData(deleteID);
    // selectedInfoを更新
    props.selectedInfo.id = "";
    props.selectedInfo.day = "";
    props.selectedInfo.weekDay = "";
    // 親コンポーネントにカスタムイベントを伝える
    emits("delete", true);
    // メッセージ表示
    alert("予約を取り消しました。");
  } catch (error) {
    console.log(error);
  }
};

// --------------------------------------
//  データ監視
// --------------------------------------
watch(
  () => props.selectedInfo.id,
  (newValue, oldValue) => {
    // local storageからデータ取得
    _storageView();
  }
);

// --------------------------------------
//  マウント
// --------------------------------------
onMounted(() => {
  // local storageからデータ取得
  _storageView();
});

// --------------------------------------
//  Local Storageデータ取得
// --------------------------------------
const _storageView = () => {
  // local storageから既存のデータを取得
  storageData.value = JSON.parse(localStorage.getItem("myFlagReservations"));
};

// --------------------------------------
//  Local Storageデータ削除
// --------------------------------------
const _deleteStorageData = (index) => {
  let deleteID = null;
  // local storageから既存のデータを取得
  storageData.value = JSON.parse(localStorage.getItem("myFlagReservations"));
  if (storageData.length === 1) {
    // 削除データのIDを取得
    deleteID = storageData.value[0].id;
    // データが1つしかない場合は、データを削除
    localStorage.removeItem("myFlagReservations");
  } else {
    // 削除データのIDを取得
    deleteID = storageData.value[index - 1].id;
    // データが複数ある場合は、選択された行のデータを削除
    storageData.value.splice(index - 1, 1);
    // データを再度保存
    localStorage.setItem("myFlagReservations", JSON.stringify(storageData.value));
  }
  return deleteID;
};

// --------------------------------------
//  データベースからデータ削除
// --------------------------------------
const _deleteDBData = async (deleteID) => {
  try {
    // supabaseからデータを削除
    const response = await supabase.from("flag_reservation").delete().eq("id", `${deleteID}`);
    console.log(response);
    // 非同期関数なので、処理が完了したことを呼び出し元に返す
    return response;
  } catch (error) {
    console.log(error);
    throw error;
  }
};
</script>

<template>
  <hr />

  <h2 class="title">あなたの予約情報</h2>

  <table v-if="storageData.length != 0">
    <tr class="head">
      <th class="class"><span>クラス</span><span>・番号</span></th>
      <th class="point">立ち位置</th>
      <th class="date">実施予定日</th>
      <th class="delete">取消</th>
    </tr>
    <tr v-for="(item, index) in storageData" :key="index">
      <td class="class no-wrap">
        <span v-if="item.class == '未定'"> {{ item.grade }}年 </span>
        <span v-else> {{ item.grade }}-{{ item.class }} {{ item.student_no }}番 </span>
      </td>
      <td class="point">{{ item.point_no }}：{{ item.point_name }}</td>
      <td class="date no-wrap">{{ item.year }}-{{ item.month }}-{{ item.day }}</td>
      <td class="delete no-wrap">
        <button type="button" @click="deleteData" class="button">✕</button>
      </td>
    </tr>
  </table>
  <p v-else class="reserve-message">予約情報はありません</p>
</template>

<style scoped>
hr {
  margin-top: 4rem;
  margin-bottom: 4rem;
}
table {
  width: 100%;
}
tr {
  display: grid;
  grid-template-columns: 90px 1fr auto;
  grid-template-rows: auto auto;
  grid-template-areas:
    "class point delete"
    "class date delete";
  align-items: center;
}
tr + tr {
  border-top: 1px solid #ccc;
}
.class {
  grid-area: class;
}
.head .class {
  display: flex;
  flex-direction: column;
}
.point {
  grid-area: point;
  text-align: left;
}
.date {
  grid-area: date;
  text-align: left;
}
.delete {
  grid-area: delete;
}
.title {
  text-align: center;
}
.button {
  padding: 0.5em;
  min-width: auto;
}
.no-wrap {
  white-space: nowrap;
}
.reserve-message {
  text-align: center;
}
/* PC */
@media (min-width: 768px) {
  table {
    width: auto;
  }
  tr {
    grid-template-columns: 130px 1fr 130px 70px;
    grid-template-rows: auto;
    grid-template-areas: "class point date delete";
  }
  .head .class {
    flex-direction: row;
  }
  .point {
    text-align: center;
  }
  .date {
    text-align: center;
  }
}
</style>
