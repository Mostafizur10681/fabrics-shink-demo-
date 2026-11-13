<template>
  <div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
      <div>
        <h3 class="fw-bold text-dark mb-1">Quality Control & Shrinkage Dashboard</h3>
        <p class="text-secondary small mb-0">Real-time fabric roll testing, 6-point shrinkage averages, and automatic group classification</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary btn-sm" @click="fetchData" :disabled="loading">
          <i class="bi bi-arrow-clockwise" :class="{ 'spin': loading }"></i> Refresh
        </button>
        <router-link to="/classifier" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
          <i class="bi bi-cpu"></i> Open Classification Lab
        </router-link>
        <router-link to="/rolls" class="btn btn-success btn-sm d-flex align-items-center gap-1 shadow-sm">
          <i class="bi bi-plus-circle"></i> New Fabric Roll
        </router-link>
      </div>
    </div>

    <!-- Alert Message -->
    <div v-if="error" class="alert alert-danger d-flex align-items-center justify-content-between">
      <div><i class="bi bi-exclamation-triangle-fill me-2"></i> {{ error }}</div>
      <button class="btn btn-sm btn-outline-danger" @click="fetchData">Retry</button>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-xl-2">
        <div class="card h-100 p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">Total Rolls</span>
              <h3 class="fw-bold mb-0 mt-1">{{ summary.total_rolls || 0 }}</h3>
              <small class="text-success"><i class="bi bi-check2"></i> Tested lots</small>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary">
              <i class="bi bi-stack"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-2">
        <div class="card h-100 p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">Chart Rules</span>
              <h3 class="fw-bold mb-0 mt-1">{{ summary.total_chart_rules || 0 }}</h3>
              <small class="text-primary"><i class="bi bi-shield-check"></i> Master rules</small>
            </div>
            <div class="stat-icon bg-info-subtle text-info">
              <i class="bi bi-diagram-3"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-2">
        <div class="card h-100 p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">Matched Groups</span>
              <h3 class="fw-bold text-success mb-0 mt-1">{{ summary.matched_count || 0 }}</h3>
              <small class="text-success fw-semibold">{{ summary.match_rate || 0 }}% match rate</small>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
              <i class="bi bi-check-circle"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-2">
        <div class="card h-100 p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">No Matching</span>
              <h3 class="fw-bold text-danger mb-0 mt-1">{{ summary.no_match_count || 0 }}</h3>
              <small class="text-danger"><i class="bi bi-x-circle"></i> Out of bounds</small>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger">
              <i class="bi bi-exclamation-octagon"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-2">
        <div class="card h-100 p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">Multiple Matches</span>
              <h3 class="fw-bold text-warning mb-0 mt-1">{{ summary.multiple_matches_count || 0 }}</h3>
              <small class="text-warning"><i class="bi bi-intersect"></i> Overlaps</small>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning">
              <i class="bi bi-layers"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-2">
        <div class="card h-100 p-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-semibold text-uppercase">Boundary Hits</span>
              <h3 class="fw-bold text-purple mb-0 mt-1" style="color: #6f42c1;">{{ summary.boundary_match_count || 0 }}</h3>
              <small class="text-secondary"><i class="bi bi-bounding-box"></i> Exact bounds</small>
            </div>
            <div class="stat-icon" style="background-color: #f3e8ff; color: #6f42c1;">
              <i class="bi bi-bounding-box-circles"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
      <!-- Top Groups Bar Chart -->
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold"><i class="bi bi-bar-chart me-1 text-primary"></i> Top Matched Shrinkage Groups</span>
            <span class="badge bg-light text-dark border">Roll frequency</span>
          </div>
          <div class="card-body">
            <div v-if="groupChartData.labels.length" style="height: 280px; position: relative;">
              <Bar :data="groupChartData" :options="groupChartOptions" />
            </div>
            <div v-else class="text-center py-5 text-muted">
              <i class="bi bi-bar-chart fs-1 opacity-25"></i>
              <p class="mt-2 mb-0">No classified rolls recorded yet.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Stage Distribution Doughnut Chart -->
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold"><i class="bi bi-pie-chart me-1 text-info"></i> Rolls by Stage</span>
            <span class="badge bg-light text-dark border">Washed / Finished / Greige</span>
          </div>
          <div class="card-body d-flex flex-column justify-content-center">
            <div v-if="stageChartData.labels.length" style="height: 260px; position: relative;">
              <Doughnut :data="stageChartData" :options="stageChartOptions" />
            </div>
            <div v-else class="text-center py-5 text-muted">
              <i class="bi bi-pie-chart fs-1 opacity-25"></i>
              <p class="mt-2 mb-0">No stage data available.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Inspections Table -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center py-3">
        <div>
          <span class="fw-bold"><i class="bi bi-clock-history me-1 text-primary"></i> Recent Fabric Roll Inspections</span>
          <span class="badge bg-secondary-subtle text-secondary ms-2">Latest 6</span>
        </div>
        <router-link to="/rolls" class="btn btn-sm btn-outline-primary">
          View All Fabric Rolls <i class="bi bi-arrow-right"></i>
        </router-link>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Roll Number</th>
              <th>Lot Number</th>
              <th>Fabric Type</th>
              <th>Stage</th>
              <th class="text-center">Length Avg (%)</th>
              <th class="text-center">Width Avg (%)</th>
              <th>Classification Status</th>
              <th>Matched Group</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="roll in recentRolls" :key="roll.id">
              <td class="fw-bold font-monospace text-primary">{{ roll.roll_number }}</td>
              <td>{{ roll.lot_number }}</td>
              <td>{{ roll.fabric_type }}</td>
              <td>
                <span class="badge bg-light text-dark border">{{ roll.stage }}</span>
              </td>
              <td class="text-center font-monospace fw-semibold" :class="roll.length_shrinkage_avg < 0 ? 'text-primary' : 'text-dark'">
                {{ roll.length_shrinkage_avg }}%
              </td>
              <td class="text-center font-monospace fw-semibold" :class="roll.width_shrinkage_avg < 0 ? 'text-primary' : 'text-dark'">
                {{ roll.width_shrinkage_avg }}%
              </td>
              <td>
                <span v-if="roll.classification_status === 'MATCHED'" class="badge badge-matched d-inline-flex align-items-center gap-1">
                  <i class="bi bi-check-circle-fill"></i> Matched
                  <span v-if="roll.is_boundary_match" class="badge bg-purple text-white ms-1" style="font-size: 0.65rem; background-color: #6f42c1;">Boundary</span>
                </span>
                <span v-else-if="roll.classification_status === 'NO_MATCH'" class="badge badge-no-match d-inline-flex align-items-center gap-1">
                  <i class="bi bi-x-circle-fill"></i> No Match
                </span>
                <span v-else-if="roll.classification_status === 'MULTIPLE_MATCHES'" class="badge badge-multiple d-inline-flex align-items-center gap-1">
                  <i class="bi bi-intersect"></i> Multiple
                </span>
                <span v-else class="badge badge-pending">Pending</span>
              </td>
              <td class="fw-bold">
                <span v-if="roll.matched_group" class="text-dark">{{ roll.matched_group }}</span>
                <span v-else class="text-muted fst-italic">None</span>
              </td>
              <td class="text-end">
                <router-link :to="`/rolls?search=${roll.roll_number}`" class="btn btn-sm btn-light border">
                  Inspect
                </router-link>
              </td>
            </tr>
            <tr v-if="!recentRolls.length">
              <td colspan="9" class="text-center py-4 text-muted">
                No fabric rolls tested yet. Click "New Fabric Roll" to start.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { getDashboardStats } from '../services/api';
import { Bar, Doughnut } from 'vue-chartjs';

const loading = ref(false);
const error = ref('');
const summary = ref({});
const recentRolls = ref([]);

const groupChartData = ref({
  labels: [],
  datasets: [{
    label: 'Fabric Rolls in Group',
    backgroundColor: '#0d6efd',
    borderRadius: 6,
    data: [],
  }],
});

const groupChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
  },
  scales: {
    y: { beginAtZero: true, ticks: { precision: 0 } },
  },
};

const stageChartData = ref({
  labels: [],
  datasets: [{
    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#6f42c1'],
    data: [],
  }],
});

const stageChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' },
  },
};

const fetchData = async () => {
  loading.value = true;
  error.value = '';
  try {
    const res = await getDashboardStats();
    summary.value = res.data.summary || {};
    recentRolls.value = res.data.recent_rolls || [];

    // Format Group Distribution Chart
    if (res.data.group_distribution && res.data.group_distribution.length) {
      groupChartData.value = {
        labels: res.data.group_distribution.map(g => g.matched_group),
        datasets: [{
          label: 'Fabric Rolls Count',
          backgroundColor: '#0d6efd',
          borderRadius: 6,
          data: res.data.group_distribution.map(g => g.count),
        }],
      };
    }

    // Format Stage Breakdown Chart
    if (res.data.stage_distribution && res.data.stage_distribution.length) {
      stageChartData.value = {
        labels: res.data.stage_distribution.map(s => s.stage),
        datasets: [{
          backgroundColor: ['#0d6efd', '#10b981', '#f59e0b', '#8b5cf6'],
          data: res.data.stage_distribution.map(s => s.count),
        }],
      };
    }
  } catch (err) {
    error.value = 'Failed to load dashboard data. Please ensure the backend is running.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.spin {
  animation: rotation 1s infinite linear;
}
@keyframes rotation {
  from { transform: rotate(0deg); }
  to { transform: rotate(359deg); }
}
</style>

