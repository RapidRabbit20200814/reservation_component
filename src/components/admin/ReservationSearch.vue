<script setup>
import { ref } from "vue";
import { supabase } from "../../lib/supabaseClient";

import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";
import ModuleGrades from "../modules/ModuleGrades.vue";
import ModuleClasses from "../modules/ModuleClasses.vue";
import ModuleStudentNumbers from "../modules/ModuleStudentNumbers.vue";
import ModuleFlagPoints from "../modules/ModuleFlagPoints.vue";
import ModulePatrolPoints from "../modules/ModulePatrolPoints.vue";

let searchFlg = ref(false);
let searchType = ref("");
const type = ref("1");
const implementationDate1 = ref("");
const implementationDate2 = ref("");
const memoCheck = ref(false);
const undecided = ref(true);
let dateAscSort = ref(true);
let pointAscSort = ref(true);
let gradeAscSort = ref(true);
let classAscSort = ref(true);
let numberAscSort = ref(true);
let selectedID = ref("");
let modalAdminMemo = ref("");
const selectedInfo = ref({
  grade: "",
  class: "",
  number: "",
  pointID: "",
});

// --------------------------------------
//  検索
// --------------------------------------
const reserve = ref([]);
const search = async () => {
  // 必須チェック（実施日）
  if (!implementationDate1.value && !implementationDate2.value) {
    alert("実施日を入力してください");
    return;
  }

  // 日付を年・月・日に分割
  let data1 = [];
  let data2 = [];
  data1 = implementationDate1.value.split("-");
  data2 = implementationDate2.value.split("-");

  // データ取得条件を作成
  let matchFilter = {
    ...(selectedInfo.value.pointID && { point_id: selectedInfo.value.pointID }),
    ...(implementationDate1.value && !implementationDate2.value && { year: data1[0], month: data1[1], day: data1[2] }),
    ...(!implementationDate1.value && implementationDate2.value && { year: data2[0], month: data2[1], day: data2[2] }),
    ...(selectedInfo.value.grade && { grade: selectedInfo.value.grade }),
    ...(selectedInfo.value.class && { class: selectedInfo.value.class }),
    ...(selectedInfo.value.number && { student_no: selectedInfo.value.number }),
  };

  // テーブル名を設定
  let reserveTable = "flag_reservation";
  let pointTable = "flag_point";
  if (type.value !== "1") {
    reserveTable = "patrol_reservation";
    pointTable = "patrol_point";
  }

  // reserveテーブルよりデータ取得
  const reserveQuery = supabase.from(reserveTable).select().match(matchFilter);

  // 実施日の範囲指定
  if (implementationDate1.value && implementationDate2.value) {
    reserveQuery.gte("year", data1[0]).lte("year", data2[0]);
  }

  // チェックボックスの条件指定
  if (memoCheck.value) {
    reserveQuery.neq("admin_memo", "");
  }

  const { data: reserveData, error: reserveError } = await reserveQuery;

  // エラーハンドリングとログ
  if (reserveError) {
    console.error(`${reserveTable} データの取得エラー:`, reserveError);
    return;
  }

  let reserveDataFiltered = [];
  // reserveDataの年月日を結合して実施預貸日の範囲と比較
  if (implementationDate1.value && implementationDate2.value) {
    reserveDataFiltered = reserveData.filter((item) => {
      // 月・日を0埋め
      if (item.month < 10) {
        item.month = "0" + item.month;
      }
      if (item.day < 10) {
        item.day = "0" + item.day;
      }
      const reserveDate = `${item.year}-${item.month}-${item.day}`;
      return reserveDate >= implementationDate1.value && reserveDate <= implementationDate2.value;
    });
  } else {
    reserveDataFiltered = reserveData;
  }

  // 1000件以上の場合はメッセージ表示
  if (reserveDataFiltered.length >= 1000) {
    alert("検索結果が1000件を超えました。\n検索条件を変更してください。");
    return;
  }

  // 予約データから取得した point_id の配列を作成
  const pointIds = reserveDataFiltered.map((reserveItem) => reserveItem.point_id).filter(Boolean);

  const pointQuery = supabase.from(pointTable).select().in("point_id", pointIds);

  const { data: pointData, error: pointError } = await pointQuery;

  // エラーハンドリングとログ
  if (pointError) {
    console.error(`${pointTable} データの取得エラー:`, pointError);
    return;
  }

  // 予約データと対応するテーブルのデータを point_id をキーにして結合
  const mergedData = reserveDataFiltered.map((reserveItem) => {
    const matchingPoint = pointData.find((pointItem) => pointItem.point_id === reserveItem.point_id);
    return { ...reserveItem, ...matchingPoint };
  });

  // ①実施予定日の年・月・日（降順）、②立ち位置（昇順）、③学年、④クラス、⑤番号の順にソート
  mergedData.sort((a, b) => {
    if (a.year < b.year) {
      return 1;
    }
    if (a.year > b.year) {
      return -1;
    }
    if (a.month < b.month) {
      return 1;
    }
    if (a.month > b.month) {
      return -1;
    }
    if (a.day < b.day) {
      return 1;
    }
    if (a.day > b.day) {
      return -1;
    }
    if (a.point_id > b.point_id) {
      return 1;
    }
    if (a.point_id < b.point_id) {
      return -1;
    }
    if (a.grade > b.grade) {
      return 1;
    }
    if (a.grade < b.grade) {
      return -1;
    }
    if (a.class > b.class) {
      return 1;
    }
    if (a.class < b.class) {
      return -1;
    }
    if (a.student_no > b.student_no) {
      return 1;
    }
    if (a.student_no < b.student_no) {
      return -1;
    }
    if (a.student_no == "未定" || b.student_no == "未定") {
      return a.student_no == "未定" ? 1 : -1;
    } else {
      if (Number(a.student_no) < Number(b.student_no)) {
        return -1;
      }
      if (Number(a.student_no) > Number(b.student_no)) {
        return 1;
      }
    }
    return 0;
  });

  // 最終的に reserve.value に予約データを格納
  reserve.value = mergedData;
  // データセット
  searchFlg.value = true;
  searchType.value = type.value;
  dateAscSort.value = true;
  pointAscSort.value = true;
  gradeAscSort.value = true;
  classAscSort.value = true;
  numberAscSort.value = true;
};

// --------------------------------------
//  並び替え
// --------------------------------------

const sortDate = (asc) => {
  reserve.value.sort((a, b) => {
    if (asc) {
      if (a.year < b.year) {
        return -1;
      }
      if (a.year > b.year) {
        return 1;
      }
      if (a.month < b.month) {
        return -1;
      }
      if (a.month > b.month) {
        return 1;
      }
      if (a.day < b.day) {
        return -1;
      }
      if (a.day > b.day) {
        return 1;
      }
    } else {
      if (a.year < b.year) {
        return 1;
      }
      if (a.year > b.year) {
        return -1;
      }
      if (a.month < b.month) {
        return 1;
      }
      if (a.month > b.month) {
        return -1;
      }
      if (a.day < b.day) {
        return 1;
      }
      if (a.day > b.day) {
        return -1;
      }
    }
    return 0;
  });
  dateAscSort.value = !asc;
};
const sortPoint = (asc) => {
  reserve.value.sort((a, b) => {
    if (asc) {
      if (a.point_id < b.point_id) {
        return -1;
      }
      if (a.point_id > b.point_id) {
        return 1;
      }
    } else {
      if (a.point_id < b.point_id) {
        return 1;
      }
      if (a.point_id > b.point_id) {
        return -1;
      }
    }
    return 0;
  });
  pointAscSort.value = !asc;
};
const sortGrade = (asc) => {
  reserve.value.sort((a, b) => {
    if (asc) {
      if (a.grade < b.grade) {
        return -1;
      }
      if (a.grade > b.grade) {
        return 1;
      }
      if (a.class < b.class) {
        return -1;
      }
      if (a.class > b.class) {
        return 1;
      }
      if (a.student_no == "未定" || b.student_no == "未定") {
        return a.student_no == "未定" ? 1 : -1;
      } else {
        if (Number(a.student_no) < Number(b.student_no)) {
          return -1;
        }
        if (Number(a.student_no) > Number(b.student_no)) {
          return 1;
        }
      }
    } else {
      if (a.grade < b.grade) {
        return 1;
      }
      if (a.grade > b.grade) {
        return -1;
      }
      if (a.class < b.class) {
        return 1;
      }
      if (a.class > b.class) {
        return -1;
      }
      if (a.student_no == "未定" || b.student_no == "未定") {
        return a.student_no == "未定" ? -1 : 1;
      } else {
        if (Number(a.student_no) < Number(b.student_no)) {
          return 1;
        }
        if (Number(a.student_no) > Number(b.student_no)) {
          return -1;
        }
      }
    }
    return 0;
  });
  gradeAscSort.value = !asc;
};
const sortClass = (asc) => {
  reserve.value.sort((a, b) => {
    if (asc) {
      if (a.class < b.class) {
        return -1;
      }
      if (a.class > b.class) {
        return 1;
      }
      if (a.student_no == "未定" || b.student_no == "未定") {
        return a.student_no == "未定" ? 1 : -1;
      } else {
        if (Number(a.student_no) < Number(b.student_no)) {
          return -1;
        }
        if (Number(a.student_no) > Number(b.student_no)) {
          return 1;
        }
      }
    } else {
      if (a.class < b.class) {
        return 1;
      }
      if (a.class > b.class) {
        return -1;
      }
      if (a.student_no == "未定" || b.student_no == "未定") {
        return a.student_no == "未定" ? -1 : 1;
      } else {
        if (Number(a.student_no) < Number(b.student_no)) {
          return 1;
        }
        if (Number(a.student_no) > Number(b.student_no)) {
          return -1;
        }
      }
    }
    return 0;
  });
  classAscSort.value = !asc;
};
const sortNumber = (asc) => {
  reserve.value.sort((a, b) => {
    if (asc) {
      if (a.student_no == "未定" || b.student_no == "未定") {
        return a.student_no == "未定" ? 1 : -1;
      } else {
        if (Number(a.student_no) < Number(b.student_no)) {
          return -1;
        }
        if (Number(a.student_no) > Number(b.student_no)) {
          return 1;
        }
      }
    } else {
      if (a.student_no == "未定" || b.student_no == "未定") {
        return a.student_no == "未定" ? -1 : 1;
      } else {
        if (Number(a.student_no) < Number(b.student_no)) {
          return 1;
        }
        if (Number(a.student_no) > Number(b.student_no)) {
          return -1;
        }
      }
    }
    return 0;
  });
  numberAscSort.value = !asc;
};
const sortAdminMemo = () => {
  reserve.value.sort((a, b) => {
    // 空白を後に表示
    if (!a.admin_memo && b.admin_memo) {
      return 1;
    }
    if (a.admin_memo && !b.admin_memo) {
      return -1;
    }

    // 昇順ソート
    if (a.admin_memo < b.admin_memo) {
      return -1;
    }
    if (a.admin_memo > b.admin_memo) {
      return 1;
    }
    return 0;
  });
};

// --------------------------------------
//  メモモーダルを開く
// --------------------------------------
const openModal = (id, memo) => {
  selectedID.value = id;
  modalAdminMemo.value = memo;
  const memoModal = document.querySelector("#memo-modal");
  memoModal.showModal();
};

// --------------------------------------
//  メモモーダルを閉じる
// --------------------------------------
const closeModal = () => {
  const memoModal = document.querySelector("#memo-modal");
  memoModal.close();
};

// --------------------------------------
//  メモ登録
// --------------------------------------
const entryMemo = async () => {
  //予約テーブル名を設定
  let reserveTable = "flag_reservation";
  if (type.value !== "1") {
    reserveTable = "patrol_reservation";
  }
  // メモを更新
  const { data, error } = await supabase
    .from(reserveTable)
    .update({ admin_memo: modalAdminMemo.value })
    .match({ id: selectedID.value });

  // エラーハンドリングとログ
  if (error) {
    console.error("メモの更新エラー:", error);
    return;
  }

  // メモモーダルを閉じる
  closeModal();

  // 検索結果からidが一致するデータを更新
  reserve.value = reserve.value.map((item) => {
    if (item.id === selectedID.value) {
      item.admin_memo = modalAdminMemo.value;
    }
    return item;
  });
};

// --------------------------------------
//  予約データ削除
// --------------------------------------
const deleteReserve = async (id) => {
  // 確認メッセージ
  if (!confirm("削除しますか？")) {
    return;
  }

  // 予約テーブル名を設定
  let reserveTable = "flag_reservation";
  if (type.value !== "1") {
    reserveTable = "patrol_reservation";
  }

  // 予約データを削除
  const { data, error } = await supabase.from(reserveTable).delete().match({ id: id });

  // エラーハンドリングとログ
  if (error) {
    console.error("予約データの削除エラー:", error);
    return;
  }

  // 検索結果からidが一致するデータを削除
  reserve.value = reserve.value.filter((item) => item.id !== id);
};
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="予約検索" />
  </header>

  <main>
    <div class="inner">
      <div class="bg-white form-wrapper">
        <div class="form__group">
          <div class="form__row">
            <dt class="form__head">
              <div class="form__title">種別</div>
              <span class="form__required">必須</span>
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
              <label for="point" class="form__title">立ち位置</label>
            </dt>
            <dd class="form__data">
              <ModuleFlagPoints :selectedInfo="selectedInfo" :blank="true" />
            </dd>
          </div>
          <div class="form__row" v-else>
            <dt class="form__head">
              <label for="point" class="form__title">エリア</label>
            </dt>
            <dd class="form__data">
              <ModulePatrolPoints :selectedInfo="selectedInfo" :displayDetail="false" :blank="true" />
            </dd>
          </div>
        </div>
        <div class="form__group">
          <div class="form__row">
            <dt class="form__head">
              <label for="implementation-date1" class="form__title">実施予定日</label>
              <span class="form__required">必須</span>
            </dt>
            <dd class="form__data">
              <input type="date" name="implementation-date1" id="implementation-date1" v-model="implementationDate1" />
              〜
              <input type="date" name="implementation-date2" id="implementation-date2" v-model="implementationDate2" />
            </dd>
          </div>
          <div class="form__row">
            <dt class="form__head">
              <label for="grade" class="form__title">クラス・番号</label>
            </dt>
            <dd class="form__data">
              <ModuleGrades :selectedInfo="selectedInfo" :blank="true" />
              <ModuleClasses :selectedInfo="selectedInfo" :undecided="undecided" :blank="true" />
              <ModuleStudentNumbers :selectedInfo="selectedInfo" :undecided="undecided" :blank="true" />
            </dd>
          </div>
        </div>
        <div class="form__group">
          <div class="form__row2">
            <div class="form__checkbox-wrapper">
              <input type="checkbox" name="memo-check" id="memo-check" class="form__checkbox" v-model="memoCheck" />
              <label for="memo-check" class="form__checkbox-label">管理者メモあり</label>
            </div>
          </div>
        </div>
        <div class="form__btn button-area">
          <button type="button" class="button button-icon" @click="search()">
            <img src="../../assets/img/search.svg" alt="" width="16" height="16" /><span>検索</span>
          </button>
        </div>
      </div>
      <div v-if="searchFlg" class="result">
        <p class="result-length">{{ reserve.length }}件</p>
        <table v-if="reserve.length != 0" class="table-primary">
          <tr class="result-head table-primary-head">
            <th class="table-date">
              <button type="button" class="underlined" @click="sortDate(dateAscSort)">実施予定日</button>
            </th>
            <th v-if="searchType === '1'" class="table-point">
              <button type="button" class="underlined" @click="sortPoint(pointAscSort)">立ち位置</button>
            </th>
            <th v-else class="table-point">
              <button type="button" class="underlined" @click="sortPoint(pointAscSort)">エリア</button>
            </th>
            <th class="table-grade">
              <button type="button" class="underlined" @click="sortGrade(gradeAscSort)">学年</button>
            </th>
            <th class="table-class">
              <button type="button" class="underlined" @click="sortClass(classAscSort)">クラス</button>
            </th>
            <th class="table-number">
              <button type="button" class="underlined" @click="sortNumber(numberAscSort)">番号</button>
            </th>
            <th class="table-admin-memo">
              <button type="button" class="underlined" @click="sortAdminMemo()">管理者メモ</button>
            </th>
            <th class="table-delete">削除</th>
          </tr>
          <tr v-for="item in reserve" :key="item.id" class="result-body table-primary-body">
            <td class="table-date">{{ item.year }}-{{ item.month }}-{{ item.day }}</td>
            <td v-if="searchType == '1'" class="table-point">{{ item.point_no }}：{{ item.point_name }}</td>
            <td v-else class="table-point">{{ item.point_name }}</td>
            <td class="table-grade">{{ item.grade }}</td>
            <td class="table-class">{{ item.class }}</td>
            <td class="table-number">{{ item.student_no }}</td>
            <td class="table-admin-memo">
              <span>{{ item.admin_memo }}</span>
              <button
                type="button"
                class="button button-white button-edit"
                @click="openModal(item.id, item.admin_memo)"
              >
                <img src="../../assets/img/pencil.svg" alt="編集ボタン" width="16" height="16" />
              </button>
            </td>
            <td class="table-delete">
              <button type="button" class="button button-white button-delete" @click="deleteReserve(item.id)">✕</button>
            </td>
          </tr>
        </table>
        <p v-else class="no-data">該当データがありません</p>
      </div>
      <dialog id="memo-modal" class="modal">
        <button type="button" class="modal__close" @click="closeModal()">✕</button>
        <form class="form">
          <p class="form__message">情報共有のため、わかりやすくメモを残してください。</p>
          <textarea
            name="modal_admin_memo"
            id="modal_admin_memo"
            placeholder="【2024/1/23 名前】メモの内容"
            class="form__textarea"
            v-model="modalAdminMemo"
          ></textarea>
          <div class="form__btn">
            <button type="button" class="button" @click="entryMemo()"><span>登録</span></button>
          </div>
        </form>
      </dialog>
      <div class="button-area-lg button-area-flex">
        <router-link :to="{ name: 'AdminMenu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
        <router-link :to="{ name: 'ReportSearch' }" class="button button-next button-white button-small"
          >報告検索へ</router-link
        >
      </div>
    </div>
  </main>
</template>

<style scoped>
.form__group + .form__group {
  margin-top: 1rem;
}
.form__row + .form__row,
.form__row2 + .form__row {
  margin-top: 1rem;
}
.form__row2 {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1rem;
}
.form__head {
  display: flex;
  align-items: center;
}
.form__required {
  margin-left: 1em;
}

textarea {
  margin-top: 1rem;
  height: 15rem;
}
.form__btn {
  margin-top: 1rem;
  text-align: center;
}
.button-icon {
  margin-inline: auto;
}
.result {
  margin-top: 2rem;
  margin-inline: auto;
  width: 100%;
  max-width: 90vw;
  overflow-x: auto;
}
.result-length {
  text-align: right;
}
.result-head,
.result-body {
  display: grid;
  grid-template-columns: 120px 200px 60px 60px 60px minmax(200px, 1fr) 60px;
  grid-template-rows: auto;
  grid-template-areas: "date point grade class number admin-memo delete";
}
.table-date {
  grid-area: date;
}
.table-point {
  grid-area: point;
}
.table-grade {
  grid-area: grade;
}
.table-class {
  grid-area: class;
}
.table-number {
  grid-area: number;
}
.table-admin-memo {
  grid-area: admin-memo;
  display: grid;
  grid-template-columns: 1fr auto;
  grid-template-rows: auto;
}
.table-delete {
  grid-area: delete;
}
.result-body .table-point,
.result-body .table-comment,
.result-body .table-admin-memo {
  text-align: left;
}
.result-body .table-comment,
.result-body .table-admin-memo span {
  font-size: 0.8rem;
}
.table-not-implementation,
.table-date,
.table-point,
.table-grade,
.table-class,
.table-number,
.table-urgency,
.table-comment,
.table-admin-memo,
.table-delete {
  padding: 0.5rem;
  border: 1px solid #eee;
}
.button-edit,
.button-delete {
  min-width: initial;
  padding: 0.3em;
  height: fit-content;
  line-height: 1;
}
.button-edit img {
  width: 1em;
}
/* PC */
@media (min-width: 768px) {
  .form__group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  .form__row {
    display: grid;
    grid-template-columns: 130px 1fr;
    align-items: center;
    gap: 1rem;
  }
  .form__row + .form__row,
  .form__row2 + .form__row {
    margin-top: 0;
  }
}
</style>
