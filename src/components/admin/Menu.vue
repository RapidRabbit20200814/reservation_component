<script setup>
import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="管理者用メニュー" />
  </header>
  <main>
    <div class="inner-xs">
      <div class="bg-white menu-wrapper">
        <h2 class="menu-title">年度初め</h2>
        <ul class="menu-list">
          <li>
            <router-link :to="{ name: 'GradeSetting' }" class="button">【共通】学年情報設定</router-link>
            <p class="comment">学年ごとの担当月と児童数を設定します</p>
          </li>
          <li>
            <router-link :to="{ name: 'FlagExcludeDate' }" class="button">【朝旗】除外日設定</router-link>
            <p class="comment">土日祝、長期休暇など、朝旗の除外日を設定します</p>
          </li>
          <li>
            <router-link :to="{ name: 'PatrolExcludeDate' }" class="button">【防パト】除外日設定</router-link>
            <p class="comment">土日祝、長期休暇など、防パトの除外日を設定します</p>
          </li>
        </ul>
      </div>
      <div class="bg-white menu-wrapper">
        <h2 class="menu-title">通年</h2>
        <ul class="menu-list">
          <li>
            <router-link :to="{ name: 'ReservationSearch' }" class="button">予約検索</router-link>
            <p class="comment">朝旗・防パトの予約情報を検索できます</p>
          </li>
          <li>
            <router-link :to="{ name: 'ReportSearch' }" class="button">報告検索</router-link>
            <p class="comment">朝旗・防パトの報告内容を検索できます</p>
          </li>
          <li>
            <router-link :to="{ name: 'Totalling' }" class="button">集計</router-link>
            <p class="comment">学年ごとの予約状況、実施率などを確認できます</p>
          </li>
        </ul>
      </div>
      <div class="bg-white menu-wrapper">
        <h2 class="menu-title">設定変更</h2>
        <ul class="menu-list">
          <li>
            <router-link :to="{ name: 'FlagPointSetting' }" class="button">【朝旗】立ち位置設定</router-link>
            <p class="comment">
              朝旗の立ち位置を変更・登録できます<span class="small">※ 通常は変更の必要はありません</span>
            </p>
          </li>
          <li>
            <router-link :to="{ name: 'PatrolPointSetting' }" class="button">【防パト】エリア設定</router-link>
            <p class="comment">
              防パトのエリア情報（集合場所、最大人数）を変更できます<span class="small"
                >※ 通常は変更の必要はありません</span
              >
            </p>
          </li>
        </ul>
      </div>
    </div>
  </main>
  <router-view></router-view>
</template>

<style scoped>
.menu-list {
  padding-left: 1rem;
}
.menu-title {
  font-size: 1rem;
}

.menu-wrapper {
  padding: 1rem;
}
.menu-wrapper + .menu-wrapper {
  margin-top: 2rem;
}
.menu-list li + li {
  margin-top: 1rem;
}
.button {
  min-width: 220px;
}
.comment {
  margin-top: 0.5rem;
  font-size: 0.8rem;
}
.small {
  display: block;
  font-size: 10px;
}
</style>
