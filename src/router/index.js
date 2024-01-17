import { createRouter, createWebHistory } from "vue-router";

const routes = [
  {
    path: "/",
    name: "Menu",
    component: () => import("../components/Menu.vue"),
  },
  {
    path: "/flag-reserve",
    name: "FlagReserve",
    component: () => import("../components/FlagReserve.vue"),
  },
  {
    path: "/flag-reserve/support",
    name: "FlagReserveSupport",
    component: () => import("../components/FlagReserveSupport.vue"),
  },
  {
    path: "/patrol-reserve",
    name: "PatrolReserve",
    component: () => import("../components/PatrolReserve.vue"),
  },
  {
    path: "/report",
    name: "Report",
    component: () => import("../components/Report.vue"),
  },
  {
    path: "/admin",
    name: "AdminMenu",
    component: () => import("../components/admin/Menu.vue"),
  },
  {
    path: "/admin/flag-exclude",
    name: "FlagExcludeDate",
    component: () => import("../components/admin/FlagExcludeDate.vue"),
  },
  {
    path: "/admin/grade-setting",
    name: "GradeSetting",
    component: () => import("../components/admin/GradeSetting.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export { router };
