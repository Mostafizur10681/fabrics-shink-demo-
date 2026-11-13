import { createRouter, createWebHistory } from 'vue-router';
import DashboardView from '../views/DashboardView.vue';
import FabricRollsView from '../views/FabricRollsView.vue';
import ShrinkageChartView from '../views/ShrinkageChartView.vue';
import ClassificationLabView from '../views/ClassificationLabView.vue';

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: DashboardView,
  },
  {
    path: '/rolls',
    name: 'FabricRolls',
    component: FabricRollsView,
  },
  {
    path: '/chart',
    name: 'ShrinkageChart',
    component: ShrinkageChartView,
  },
  {
    path: '/classifier',
    name: 'ClassificationLab',
    component: ClassificationLabView,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  linkActiveClass: 'active',
});

export default router;

