<script setup>
import ModuleHeaderAdmin from "../modules/ModuleHeaderAdmin.vue";
</script>

<template>
  <header>
    <ModuleHeaderAdmin title="管理者用メニュー" />
  </header>
  <main>
    <div class="inner-xs">
      <ul class="menu-list">
        <li>
          <router-link :to="{ name: 'GradeSetting' }" class="button">【朝旗】学年情報設定</router-link>
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
  </main>
  <router-view></router-view>
</template>

<style scoped>
.menu-list li + li {
  margin-top: 2rem;
}
.comment {
  margin-top: 1rem;
  font-size: 0.8rem;
}
</style>
