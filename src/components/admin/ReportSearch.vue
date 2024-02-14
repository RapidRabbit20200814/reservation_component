<script setup>
import { ref } from "vue";
import { supabase } from "../../lib/supabaseClient";

import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";
import ModuleGrades from "../modules/ModuleGrades.vue";
import ModuleClasses from "../modules/ModuleClasses.vue";
import ModuleStudentNumbers from "../modules/ModuleStudentNumbers.vue";
import ModuleFlagPoints from "../modules/ModuleFlagPoints.vue";
import ModulePatrolPoints from "../modules/ModulePatrolPoints.vue";
import ModuleUrgencies from "../modules/ModuleUrgencies.vue";

let searchFlg = ref(false);
let searchType = ref("");
const type = ref("1");
const implementationDate1 = ref("");
const implementationDate2 = ref("");
const commentCheck = ref(false);
const memoCheck = ref(false);
const notImplementCheck = ref(false);
const undecided = ref(false);
let dateAscSort = ref(true);
let pointAscSort = ref(true);
let gradeAscSort = ref(true);
let classAscSort = ref(true);
let numberAscSort = ref(true);
let urgencyAscSort = ref(false);
let selectedID = ref("");
let modalAdminMemo = ref("");
const selectedInfo = ref({
  grade: "",
  class: "",
  number: "",
  pointID: "",
  urgency: "",
});

// --------------------------------------
//  検索
// --------------------------------------
const report = ref([]);
const search = async () => {
  // 必須チェック（実施日）
  if (!implementationDate1.value && !implementationDate2.value) {
    alert("実施日を入力してください");
    return;
  }

  // データ取得条件を作成
  let matchFilter = {
    work_type: type.value,
    ...(selectedInfo.value.pointID && { point_id: selectedInfo.value.pointID }),
    ...(implementationDate1.value && !implementationDate2.value && { implementation_date: implementationDate1.value }),
    ...(!implementationDate1.value && implementationDate2.value && { implementation_date: implementationDate2.value }),
    ...(selectedInfo.value.grade && { grade: selectedInfo.value.grade }),
    ...(selectedInfo.value.class && { class: selectedInfo.value.class }),
    ...(selectedInfo.value.number && { student_no: selectedInfo.value.number }),
    ...(notImplementCheck.value && { not_implementation: true }),
    ...(selectedInfo.value.urgency && { urgency: selectedInfo.value.urgency }),
  };

  // reportテーブルよりデータ取得
  const reportQuery = supabase.from("report").select().match(matchFilter);

  // 実施日の範囲指定
  if (implementationDate1.value && implementationDate2.value) {
    reportQuery
      .gte("implementation_date", implementationDate1.value)
      .lte("implementation_date", implementationDate2.value);
  }

  // チェックボックスの条件指定
  if (commentCheck.value) {
    reportQuery.neq("comment", "");
  }
  if (memoCheck.value) {
    reportQuery.neq("admin_memo", "");
  }

  const { data: reportData, error: reportError } = await reportQuery;

  // エラーハンドリングとログ
  if (reportError) {
    console.error("報告データの取得エラー:", reportError);
    return;
  }

  // 1000件以上の場合はメッセージ表示
  if (reportData.length >= 1000) {
    alert("検索結果が1000件を超えました。\n検索条件を変更してください。");
    return;
  }

  // 報告データから取得した point_id の配列を作成
  const pointIds = reportData.map((reportItem) => reportItem.point_id).filter(Boolean);

  // point_id を使用して 立ち位置情報（防パトエリア情報）を取得
  let pointTable = "flag_point";
  if (type.value !== "1") {
    pointTable = "patrol_point";
  }
  const pointQuery = supabase.from(pointTable).select().in("point_id", pointIds);

  const { data: pointData, error: pointError } = await pointQuery;

  // エラーハンドリングとログ
  if (pointError) {
    console.error(`${pointTable} データの取得エラー:`, pointError);
    return;
  }

  // 報告データと対応するテーブルのデータを point_id をキーにして結合
  const mergedData = reportData.map((reportItem) => {
    const matchingPoint = pointData.find((pointItem) => pointItem.point_id === reportItem.point_id);
    return { ...reportItem, ...matchingPoint };
  });

  // ①実施日（降順）、②立ち位置（昇順）にソート
  mergedData.sort((a, b) => {
    if (a.implementation_date < b.implementation_date) {
      return 1;
    }
    if (a.implementation_date > b.implementation_date) {
      return -1;
    }
    if (a.point_id < b.point_id) {
      return -1;
    }
    if (a.point_id > b.point_id) {
      return 1;
    }
    return 0;
  });

  // 最終的に report.value に報告データを格納
  report.value = mergedData;
  // データセット
  searchFlg.value = true;
  searchType.value = type.value;
  dateAscSort.value = true;
  pointAscSort.value = true;
  gradeAscSort.value = true;
  classAscSort.value = true;
  numberAscSort.value = true;
  urgencyAscSort.value = false;
};

// --------------------------------------
//  並び替え
// --------------------------------------

const sortNotImplementation = () => {
  report.value.sort((a, b) => {
    if (a.not_implementation && !b.not_implementation) {
      return -1;
    }
    if (!a.not_implementation && b.not_implementation) {
      return 1;
    }
    return 0;
  });
};
const sortDate = (asc) => {
  report.value.sort((a, b) => {
    if (asc) {
      if (a.implementation_date < b.implementation_date) {
        return -1;
      }
      if (a.implementation_date > b.implementation_date) {
        return 1;
      }
    } else {
      if (a.implementation_date < b.implementation_date) {
        return 1;
      }
      if (a.implementation_date > b.implementation_date) {
        return -1;
      }
    }
    return 0;
  });
  dateAscSort.value = !asc;
};
const sortPoint = (asc) => {
  report.value.sort((a, b) => {
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
  report.value.sort((a, b) => {
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
      if (Number(a.student_no) < Number(b.student_no)) {
        return -1;
      }
      if (Number(a.student_no) > Number(b.student_no)) {
        return 1;
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
      if (Number(a.student_no) < Number(b.student_no)) {
        return 1;
      }
      if (Number(a.student_no) > Number(b.student_no)) {
        return -1;
      }
    }
    return 0;
  });
  gradeAscSort.value = !asc;
};
const sortClass = (asc) => {
  report.value.sort((a, b) => {
    if (asc) {
      if (a.class < b.class) {
        return -1;
      }
      if (a.class > b.class) {
        return 1;
      }
      if (Number(a.student_no) < Number(b.student_no)) {
        return -1;
      }
      if (Number(a.student_no) > Number(b.student_no)) {
        return 1;
      }
    } else {
      if (a.class < b.class) {
        return 1;
      }
      if (a.class > b.class) {
        return -1;
      }
      if (Number(a.student_no) < Number(b.student_no)) {
        return 1;
      }
      if (Number(a.student_no) > Number(b.student_no)) {
        return -1;
      }
    }
    return 0;
  });
  classAscSort.value = !asc;
};
const sortNumber = (asc) => {
  report.value.sort((a, b) => {
    if (asc) {
      if (Number(a.student_no) < Number(b.student_no)) {
        return -1;
      }
      if (Number(a.student_no) > Number(b.student_no)) {
        return 1;
      }
    } else {
      if (Number(a.student_no) < Number(b.student_no)) {
        return 1;
      }
      if (Number(a.student_no) > Number(b.student_no)) {
        return -1;
      }
    }
    return 0;
  });
  numberAscSort.value = !asc;
};
const sortComment = () => {
  report.value.sort((a, b) => {
    // 空白を後に表示
    if (a.comment === "" && b.comment !== "") {
      return 1;
    }
    if (a.comment !== "" && b.comment === "") {
      return -1;
    }

    // 昇順ソート
    if (a.comment < b.comment) {
      return -1;
    }
    if (a.comment > b.comment) {
      return 1;
    }
    return 0;
  });
};
const sortUrgency = (asc) => {
  report.value.sort((a, b) => {
    // 空白を後に表示
    if (!a.urgency && b.urgency) {
      return 1;
    }
    if (a.urgency && !b.urgency) {
      return -1;
    }
    if (asc) {
      // 昇順ソート
      if (a.urgency < b.urgency) {
        return -1;
      }
      if (a.urgency > b.urgency) {
        return 1;
      }
    } else {
      // 降順ソート
      if (a.urgency > b.urgency) {
        return -1;
      }
      if (a.urgency < b.urgency) {
        return 1;
      }
    }
    return 0;
  });
  urgencyAscSort.value = !asc;
};
const sortAdminMemo = () => {
  report.value.sort((a, b) => {
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
  // メモを更新
  const { data, error } = await supabase
    .from("report")
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
  report.value = report.value.map((item) => {
    if (item.id === selectedID.value) {
      item.admin_memo = modalAdminMemo.value;
    }
    return item;
  });
};

// --------------------------------------
//  報告データ削除
// --------------------------------------
const deleteReport = async (id) => {
  // 確認メッセージ
  if (!confirm("削除しますか？")) {
    return;
  }

  // 報告データを削除
  const { data, error } = await supabase.from("report").delete().match({ id: id });

  // エラーハンドリングとログ
  if (error) {
    console.error("報告データの削除エラー:", error);
    return;
  }

  // 検索結果からidが一致するデータを削除
  report.value = report.value.filter((item) => item.id !== id);
};
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="報告検索" />
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
              <label for="implementation-date1" class="form__title">実施日</label>
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
              <input
                type="checkbox"
                name="comment-check"
                id="comment-check"
                class="form__checkbox"
                v-model="commentCheck"
              />
              <label for="comment-check" class="form__checkbox-label">コメントあり</label>
            </div>
            <div class="form__checkbox-wrapper">
              <input type="checkbox" name="memo-check" id="memo-check" class="form__checkbox" v-model="memoCheck" />
              <label for="memo-check" class="form__checkbox-label">管理者メモあり</label>
            </div>
            <div class="form__checkbox-wrapper">
              <input
                type="checkbox"
                name="not-implementation"
                id="not-implementation"
                class="form__checkbox"
                v-model="notImplementCheck"
              />
              <label for="not-implementation" class="form__checkbox-label">未実施</label>
            </div>
          </div>
          <div class="form__row">
            <dt class="form__head">
              <label for="urgency" class="form__title">危険度</label>
            </dt>
            <dd class="form__data">
              <ModuleUrgencies :selectedInfo="selectedInfo" :blank="true" />
            </dd>
          </div>
        </div>
        <div class="form__btn button-area">
          <button type="button" class="button button-icon" @click="search()">
            <img src="../../assets/img/search.svg" alt="" width="16" height="16" /><span>検索</span>
          </button>
        </div>
      </div>
      <div v-if="searchFlg" class="result">
        <p class="result-length">{{ report.length }}件</p>
        <table v-if="report.length != 0" class="table-primary">
          <tr class="result-head table-primary-head">
            <th class="table-not-implementation">
              <button type="button" class="underlined" @click="sortNotImplementation()">未</button>
            </th>
            <th class="table-date">
              <button type="button" class="underlined" @click="sortDate(dateAscSort)">実施日</button>
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
            <th class="table-comment">
              <button type="button" class="underlined" @click="sortComment()">コメント</button>
            </th>
            <th class="table-urgency">
              <button type="button" class="underlined" @click="sortUrgency(urgencyAscSort)">危険</button>
            </th>
            <th class="table-admin-memo">
              <button type="button" class="underlined" @click="sortAdminMemo()">管理者メモ</button>
            </th>
            <th class="table-delete">削除</th>
          </tr>
          <tr v-for="item in report" :key="item.id" class="result-body table-primary-body">
            <td class="table-not-implementation"><span v-if="item.not_implementation">未</span></td>
            <td class="table-date">{{ item.implementation_date }}</td>
            <td v-if="searchType == '1'" class="table-point">{{ item.point_no }}：{{ item.point_name }}</td>
            <td v-else class="table-point">{{ item.point_name }}</td>
            <td class="table-grade">{{ item.grade }}</td>
            <td class="table-class">{{ item.class }}</td>
            <td class="table-number">{{ item.student_no }}</td>
            <td class="table-comment">
              {{ item.comment }}
            </td>
            <td class="table-urgency">
              {{ item.urgency }}
            </td>
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
              <button type="button" class="button button-white button-delete" @click="deleteReport(item.id)">✕</button>
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
            placeholder="【2024/1/23 名前】学校（◯◯先生）に連絡済"
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
        <router-link :to="{ name: 'ReservationSearch' }" class="button button-next button-white button-small"
          >予約検索へ</router-link
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
  grid-template-columns: 30px 120px 200px 40px 40px 40px minmax(200px, 1fr) 30px 200px 40px;
  grid-template-rows: auto;
  grid-template-areas: "not-implementation date point grade class number comment urgency admin-memo delete";
}
.result-head th {
  display: flex;
  justify-content: center;
  align-items: center;
}
.table-not-implementation {
  grid-area: not-implementation;
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
.table-urgency {
  grid-area: urgency;
}
.table-comment {
  grid-area: comment;
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
