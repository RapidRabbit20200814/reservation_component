<script setup>
import { ref, onMounted } from "vue";
import { supabase } from "../../lib/supabaseClient";

import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";

let selectFlg = ref(false);
const points = ref([]);

const selectPoint = ref({
  id: "",
  no: "",
  name: "",
  memo: "",
  map_x: "",
  map_y: "",
  deleted_flg: false,
});

// --------------------------------------
//  初期表示
// --------------------------------------
onMounted(async () => {
  _init();
});

// --------------------------------------
//  並び替え
// --------------------------------------
const _sort = () => {
  // pointsをpoint_noでソート
  points.value.sort(function (a, b) {
    if (a.point_no < b.point_no) return -1;
    if (a.point_no > b.point_no) return 1;
    return 0;
  });
};

// --------------------------------------
//  一覧クリック
// --------------------------------------
const editCopy = (item) => {
  selectPoint.value.id = item.point_id;
  selectPoint.value.no = item.point_no;
  selectPoint.value.name = item.point_name;
  selectPoint.value.memo = item.memo;
  selectPoint.value.map_x = item.map_x;
  selectPoint.value.map_y = item.map_y;
  selectPoint.value.deleted_flg = item.deleted_flg;
  selectFlg.value = true;

  // #editまでスクロール
  const edit = document.querySelector("#edit");
  edit.scrollIntoView({
    behavior: "smooth",
    block: "start",
  });
};

// --------------------------------------
//  登録 / 更新
// --------------------------------------
const register = async () => {
  console.log(selectPoint.value);
  // 必須チェック（番号、名称）
  if (!selectPoint.value.no || !selectPoint.value.name) {
    alert("番号と名称は必須項目です");
    return;
  }
  // point_no,map_x,map_yを数値に変換
  selectPoint.value.no = Number(selectPoint.value.no);
  selectPoint.value.map_x = Number(selectPoint.value.map_x);
  selectPoint.value.map_y = Number(selectPoint.value.map_y);

  if (selectPoint.value.id) {
    // 確認メッセージ
    if (!confirm("立ち位置情報を更新します。よろしいですか？")) {
      return;
    }
    // 更新
    await _updatePoint();
  } else {
    // 確認メッセージ
    if (!confirm("新しい立ち位置を登録します。よろしいですか？")) {
      return;
    }
    // 登録
    await _addPoint();
  }

  // 初期化
  _init();
};

// --------------------------------------
//  立ち位置登録
// --------------------------------------
const _addPoint = async () => {
  // 立ち位置登録
  const { data, error } = await supabase
    .from("flag_point")
    .insert([
      {
        point_no: selectPoint.value.no,
        point_name: selectPoint.value.name,
        memo: selectPoint.value.memo,
        map_x: selectPoint.value.map_x,
        map_y: selectPoint.value.map_y,
        deleted_flg: selectPoint.value.deleted_flg,
      },
    ])
    .select();
  if (error) {
    console.error("データの挿入エラー:", error);
  } else {
    // メッセージ表示
    alert("登録しました");
  }
};

// --------------------------------------
//  立ち位置更新
// --------------------------------------
const _updatePoint = async () => {
  // 立ち位置更新
  const { data, error } = await supabase
    .from("flag_point")
    .update({
      point_no: selectPoint.value.no,
      point_name: selectPoint.value.name,
      memo: selectPoint.value.memo,
      map_x: selectPoint.value.map_x,
      map_y: selectPoint.value.map_y,
      deleted_flg: selectPoint.value.deleted_flg,
    })
    .eq("point_id", selectPoint.value.id);
  if (error) {
    console.error("データの更新エラー:", error);
  } else {
    // メッセージ表示
    alert("更新しました");
  }
};

// --------------------------------------
//  初期化
// --------------------------------------
const _init = async () => {
  // 立ち位置情報取得
  const { data } = await supabase.from("flag_point").select();
  // pointsにデータセット
  points.value = data;

  // pointsをpoint_noでソート
  _sort();

  // selectPointを初期化
  clear();
};

// --------------------------------------
//  入力クリア
// --------------------------------------
const clear = () => {
  // selectPointを初期化
  selectPoint.value.id = "";
  selectPoint.value.no = "";
  selectPoint.value.name = "";
  selectPoint.value.memo = "";
  selectPoint.value.map_x = "";
  selectPoint.value.map_y = "";
  selectPoint.value.deleted_flg = false;
  selectFlg.value = false;
};

// --------------------------------------
//  立ち位置を削除
// --------------------------------------
const deletePoint = async () => {
  // 確認メッセージ
  if (!confirm("立ち位置データを削除します。よろしいですか？")) {
    return;
  }
  // 立ち位置を削除
  const { data, error } = await supabase.from("flag_point").delete().eq("point_id", selectPoint.value.id);
  if (error) {
    console.error("データの削除エラー:", error);
  } else {
    // メッセージ表示
    alert("削除しました");
    // 初期化
    _init();
  }
};
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="朝旗／立ち位置設定" />
  </header>

  <main>
    <div class="inner-s">
      <ul class="lead">
        <li>
          <p class="lead__ttl">新しく立ち位置を追加する場合</p>
          <p class="lead__txt">
            下の<a href="#edit" class="underlined">入力エリア</a>に立ち位置情報を入力してください。
          </p>
        </li>
        <li>
          <p class="lead__ttl">既存の立ち位置を変更する場合</p>
          <p class="lead__txt">一覧より変更したい立ち位置を選んでから、下の入力エリアを編集してください。</p>
        </li>
      </ul>
      <div class="point-list">
        <table class="table-primary">
          <tr class="point-list-head table-primary-head">
            <th>番号</th>
            <th>名称</th>
            <th>メモ</th>
            <th>表示</th>
            <th class="hidden">X座標</th>
            <th class="hidden">Y座標</th>
            <th class="hidden">id</th>
          </tr>
          <tr v-for="item in points" :key="item.id" class="point-list-body table-primary-body" @click="editCopy(item)">
            <td>{{ item.point_no }}</td>
            <td class="left">{{ item.point_name }}</td>
            <td class="left">{{ item.memo }}</td>
            <td><span v-if="item.deleted_flg">✕</span></td>
            <td class="hidden">{{ item.map_x }}</td>
            <td class="hidden">{{ item.map_y }}</td>
            <td class="hidden">{{ item.point_id }}</td>
          </tr>
        </table>
      </div>
    </div>
    <div class="inner-s">
      <div class="point-edit bg-white" id="edit">
        <h2>入力エリア</h2>
        <dl class="point-edit__list">
          <div class="point-edit__row">
            <dt class="point-edit__ttl">
              <label for="point_no" class="form__title"
                ><span>番号</span><span class="form__required">必須</span></label
              >
            </dt>
            <dd class="point-edit__data">
              <input
                type="number"
                name="point_no"
                id="point_no"
                v-model="selectPoint.no"
                min="0"
                max="99"
                class="form__input form__input--short"
              />
            </dd>
          </div>
          <div class="point-edit__row">
            <dt class="point-edit__ttl">
              <label for="point_name" class="form__title"
                ><span>名称</span><span class="form__required">必須</span></label
              >
            </dt>
            <dd class="point-edit__data">
              <input type="text" name="point_name" id="point_name" v-model="selectPoint.name" class="form__input" />
            </dd>
          </div>
          <div class="point-edit__row">
            <dt class="point-edit__ttl">
              <label for="memo" class="form__title">メモ</label>
            </dt>
            <dd class="point-edit__data">
              <input type="text" name="memo" id="memo" v-model="selectPoint.memo" class="form__input" />
            </dd>
          </div>
          <div class="point-edit__row form__checkbox-wrapper">
            <input
              type="checkbox"
              name="deleted_flg"
              id="deleted_flg"
              class="form__checkbox"
              v-model="selectPoint.deleted_flg"
            />
            <label for="deleted_flg" class="form__checkbox-label">非表示</label>
          </div>
        </dl>
        <div class="button-area button-area-edit">
          <button class="button button-white button-small button-clear" type="button" @click="clear">入力クリア</button>
          <button
            class="button button-white button-small button-delete"
            type="button"
            @click="deletePoint"
            :disabled="!selectFlg"
          >
            立ち位置を削除
          </button>
          <button class="button button-register" type="button" @click="register">登録 / 更新</button>
        </div>
      </div>
    </div>
    <div class="inner">
      <div class="button-area-lg button-area-flex">
        <router-link :to="{ name: 'AdminMenu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
        <router-link :to="{ name: 'PatrolPointSetting' }" class="button button-next button-white button-small"
          >【防パト】エリア設定へ</router-link
        >
      </div>
    </div>
  </main>
</template>

<style scoped>
.lead li + li {
  margin-top: 1rem;
}
.lead__ttl {
  position: relative;
  padding-left: 1em;
  font-weight: bold;
}
.lead__ttl::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 0;
  width: 0.5rem;
  height: 0.5rem;
  transform: translateY(-50%);
  clip-path: polygon(0 0, 0% 100%, 100% 50%);
  background-color: currentColor;
}
.point-list-body {
  cursor: pointer;
}
.point-list-head,
.point-list-body {
  display: grid;
  grid-template-columns: 50px 1fr 1fr 50px;
  align-items: center;
}
.left {
  text-align: left;
}
.point-edit {
  margin-top: 2rem;
}
.point-edit h2 {
  font-size: 1rem;
}
.point-edit__row {
  display: grid;
  grid-template-columns: 6rem 1fr;
  align-items: center;
}
.form__input {
  width: 100%;
}
.form__input.form__input--short {
  width: 60px;
}
.point-edit__row + .point-edit__row {
  margin-top: 1rem;
}
.button-area-edit {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  grid-template-areas:
    "register register"
    "clear delete";
  gap: 1rem;
  justify-content: center;
}
.button-clear {
  grid-area: clear;
}
.button-delete {
  grid-area: delete;
  margin-inline: auto 0;
}
.button-register {
  grid-area: register;
  margin-inline: auto;
}
.button-area-edit button {
  width: fit-content;
}
button[disabled] {
  opacity: 0.5;
  cursor: not-allowed;
}
/* ホバー */
@media (hover: hover) {
  .point-list-body:is(:hover, :focus-visible) {
    background-color: var(--color-light-blue);
  }
}
/* PC */
@media (min-width: 768px) {
  .button-area-edit {
    grid-template-columns: auto auto 1fr;
    grid-template-rows: 1fr;
    grid-template-areas: "clear delete register";
  }
  .button-register {
    margin-inline: auto 0;
  }
}
</style>
