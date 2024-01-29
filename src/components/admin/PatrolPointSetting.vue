<script setup>
import { ref, onMounted } from "vue";
import { supabase } from "../../lib/supabaseClient";

import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";

let selectFlg = ref(false);
const points = ref([]);

const selectPoint = ref({
  id: "",
  name: "",
  meeting_place: "",
  max_number: "",
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
  // pointsをpoint_idでソート
  points.value.sort(function (a, b) {
    if (a.point_id < b.point_id) return -1;
    if (a.point_id > b.point_id) return 1;
    return 0;
  });
};

// --------------------------------------
//  一覧クリック
// --------------------------------------
const editCopy = (item) => {
  selectPoint.value.id = item.point_id;
  selectPoint.value.name = item.point_name;
  selectPoint.value.meeting_place = item.meeting_place;
  selectPoint.value.max_number = item.max_number;
  selectFlg.value = true;

  // #editまでスクロール
  const edit = document.querySelector("#edit");
  edit.scrollIntoView({
    behavior: "smooth",
    block: "start",
  });
};

// --------------------------------------
//  更新
// --------------------------------------
const register = async () => {
  console.log(selectPoint.value);
  // 必須チェック
  if (!selectPoint.value.name || !selectPoint.value.meeting_place || !selectPoint.value.max_number) {
    alert("入力されていない項目があります");
    return;
  }
  // max_numberを数値に変換
  selectPoint.value.max_number = Number(selectPoint.value.max_number);

  // 確認メッセージ
  if (!confirm("エリア情報を更新します。よろしいですか？")) {
    return;
  }
  // 更新
  await _updatePoint();

  // 初期化
  _init();
};

// --------------------------------------
//  エリア更新
// --------------------------------------
const _updatePoint = async () => {
  // エリア更新
  const { data, error } = await supabase
    .from("patrol_point")
    .update({
      point_name: selectPoint.value.name,
      meeting_place: selectPoint.value.meeting_place,
      max_number: selectPoint.value.max_number,
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
  // エリア情報取得
  const { data } = await supabase.from("patrol_point").select();
  // pointsにデータセット
  points.value = data;

  // pointsをpoint_idでソート
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
  selectPoint.value.name = "";
  selectPoint.value.meeting_place = "";
  selectPoint.value.max_number = "";
  selectPoint.value.map_y = "";
  selectPoint.value.deleted_flg = false;
  selectFlg.value = false;
};
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="防パト／エリア設定" />
  </header>

  <main>
    <div class="inner-s">
      <p class="lead">一覧より変更したいエリアを選んでください</p>
      <div class="point-list">
        <table class="table-primary">
          <tr class="point-list-head table-primary-head">
            <th>エリア名</th>
            <th>集合場所</th>
            <th>最大人数</th>
            <th class="hidden">id</th>
          </tr>
          <tr v-for="item in points" :key="item.id" class="point-list-body table-primary-body" @click="editCopy(item)">
            <td class="left">{{ item.point_name }}</td>
            <td class="left">{{ item.meeting_place }}</td>
            <td>{{ item.max_number }}人</td>
            <td class="hidden">{{ item.point_id }}</td>
          </tr>
        </table>
      </div>
    </div>
    <div class="inner-s">
      <div v-if="selectFlg" class="point-edit bg-white" id="edit">
        <dl class="point-edit__list">
          <div class="point-edit__row">
            <dt class="point-edit__ttl">
              <label for="point_name" class="form__title"
                ><span>エリア名</span><span class="form__required">必須</span></label
              >
            </dt>
            <dd class="point-edit__data">
              <input type="text" name="point_name" id="point_name" v-model="selectPoint.name" class="form__input" />
            </dd>
          </div>
          <div class="point-edit__row">
            <dt class="point-edit__ttl">
              <label for="meeting_place" class="form__title"
                ><span>集合場所</span><span class="form__required">必須</span></label
              >
            </dt>
            <dd class="point-edit__data">
              <input
                type="text"
                name="meeting_place"
                id="meeting_place"
                v-model="selectPoint.meeting_place"
                class="form__input"
              />
            </dd>
          </div>
          <div class="point-edit__row">
            <dt class="point-edit__ttl">
              <label for="max_number" class="form__title"
                ><span>最大人数</span><span class="form__required">必須</span></label
              >
            </dt>
            <dd class="point-edit__data">
              <input
                type="number"
                name="max_number"
                id="max_number"
                v-model="selectPoint.max_number"
                min="1"
                max="99"
                class="form__input form__input--short"
              />
            </dd>
          </div>
        </dl>
        <div class="button-area">
          <button class="button button-register" type="button" @click="register">更新</button>
        </div>
      </div>
    </div>
    <div class="inner">
      <div class="button-area-lg button-area-flex">
        <router-link :to="{ name: 'AdminMenu' }" class="button button-return button-white button-small"
          >メニューへ戻る</router-link
        >
        <router-link :to="{ name: 'FlagPointSetting' }" class="button button-next button-white button-small"
          >【朝旗】立ち位置設定へ</router-link
        >
      </div>
    </div>
  </main>
</template>

<style scoped>
.lead {
  margin-bottom: 1rem;
}
.point-list-body {
  cursor: pointer;
}
.point-list-head,
.point-list-body {
  display: grid;
  grid-template-columns: 100px 1fr 80px;
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
  grid-template-columns: 140px 1fr;
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

.button-area {
  text-align: center;
}

/* ホバー */
@media (hover: hover) {
  .point-list-body:is(:hover, :focus-visible) {
    background-color: var(--color-light-blue);
  }
}
</style>
