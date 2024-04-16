<script setup>
import ModuleHeader from "./modules/ModuleHeader.vue";
</script>

<template>
  <header>
    <ModuleHeader title="メニュー" />
  </header>
  <main>
    <div class="inner-xs">
      <ol class="menu-list">
        <li>
          <a href="https://tisetusyou-pta.org/flag-patrol-procedure" target="_blank" rel="noopener" class="button"
            >1. 実施手順を確認する</a
          >
          <p class="comment">
            朝旗・防パトの実施手順です。<br /><span class="underlined">実施前に確認してください。</span>
          </p>
        </li>
        <li>
          <router-link :to="{ name: 'FlagReserve' }" class="button">2. 朝旗の予約をする</router-link>
          <p class="comment">
            朝のあいさつ旗当番の予約をします。<br />学年の担当月に何度でも実施していただけます。<br />児童が安全に登校できるよう、ご協力よろしくお願いいたします。
          </p>
          <div class="support">
            <router-link :to="{ name: 'FlagReserveSupport' }" class="button button-white button-next button-small"
              >つどさぽはこちら</router-link
            >
          </div>
        </li>
        <li>
          <router-link :to="{ name: 'PatrolReserve' }" class="button">3. 防パトの予約をする</router-link>
          <p class="comment">
            防犯パトロールの予約をします。<br />学年の担当月に何度でも実施していただけます。<br />協力して地域の安全を見守りましょう。
          </p>
        </li>
        <li>
          <router-link :to="{ name: 'Report' }" class="button">4. 朝旗・防パトの報告をする</router-link>
          <p class="comment">朝旗・防パトの実施報告をします。<br />実施するごとに入力してください。</p>
        </li>
      </ol>
    </div>
  </main>
  <router-view></router-view>
</template>

<style scoped>
.menu-list li + li {
  margin-top: 2rem;
}
.button:not(.button-white) {
  min-width: 260px;
  text-align: left;
}
.comment {
  margin-top: 1rem;
  padding-left: 1em;
  font-size: 0.8rem;
}
.support {
  margin-top: 0.5rem;
  padding-left: 1em;
}
</style>
