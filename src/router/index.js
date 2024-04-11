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
    path: "/admin-menu",
    name: "AdminMenu",
    component: () => import("../components/admin/Menu.vue"),
  },
  {
    path: "/admin-flag-exclude",
    name: "FlagExcludeDate",
    component: () => import("../components/admin/FlagExcludeDate.vue"),
  },
  {
    path: "/admin-grade-setting",
    name: "GradeSetting",
    component: () => import("../components/admin/GradeSetting.vue"),
  },
  {
    path: "/admin-patrol-exclude",
    name: "PatrolExcludeDate",
    component: () => import("../components/admin/patrolExcludeDate.vue"),
  },
  {
    path: "/admin-totalling",
    name: "Totalling",
    component: () => import("../components/admin/Totalling.vue"),
  },
  {
    path: "/admin-reservation-search",
    name: "ReservationSearch",
    component: () => import("../components/admin/ReservationSearch.vue"),
  },
  {
    path: "/admin-report-search",
    name: "ReportSearch",
    component: () => import("../components/admin/ReportSearch.vue"),
  },
  {
    path: "/admin-flag-point-setting",
    name: "FlagPointSetting",
    component: () => import("../components/admin/FlagPointSetting.vue"),
  },
  {
    path: "/admin-patrol-point-setting",
    name: "PatrolPointSetting",
    component: () => import("../components/admin/PatrolPointSetting.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export { router };
