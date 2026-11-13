<template>
  <div class="container-fluid px-4">
    <!-- Header & Action Toolbar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
      <div>
        <h3 class="fw-bold text-dark mb-1">Shrinkage Master Chart (400+ Rules)</h3>
        <p class="text-secondary small mb-0">Pattern shrinkage group boundaries, stage rules, CSV/Excel batch import and management</p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" @click="openCreateModal">
          <i class="bi bi-plus-lg"></i> Add Group Rule
        </button>
        <button class="btn btn-outline-primary d-flex align-items-center gap-2" @click="openImportModal">
          <i class="bi bi-upload"></i> Import CSV / Excel (400+)
        </button>
        <a :href="sampleCsvUrl" class="btn btn-outline-secondary d-flex align-items-center gap-1" download>
          <i class="bi bi-download"></i> Sample CSV (400+ Records)
        </a>
        <button class="btn btn-outline-warning d-flex align-items-center gap-1" @click="seedSample" :disabled="seeding">
          <i class="bi bi-arrow-counterclockwise" :class="{ 'spin': seeding }"></i> Seed / Reset 420 Rules
        </button>
        <div class="dropdown">
          <button class="btn btn-outline-success dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-file-earmark-arrow-down"></i> Export
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" :href="exportUrl('xlsx')" target="_blank"><i class="bi bi-file-earmark-excel text-success me-2"></i> Export Excel (.xlsx)</a></li>
            <li><a class="dropdown-item" :href="exportUrl('csv')" target="_blank"><i class="bi bi-filetype-csv text-primary me-2"></i> Export CSV</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Notification Alert -->
    <div v-if="successMsg" class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> {{ successMsg }}
      <button type="button" class="btn-close" @click="successMsg = ''"></button>
    </div>
    <div v-if="errorMsg" class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ errorMsg }}
      <button type="button" class="btn-close" @click="errorMsg = ''"></button>
    </div>

    <!-- Benchmark Highlight Card for Group 23 -->
    <div class="card mb-4 border-primary-subtle bg-primary-subtle bg-opacity-10 shadow-sm p-3">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-primary text-white rounded p-2 fs-4">
            <i class="bi bi-award"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-0 text-primary">Benchmark Group 23 Specification (Worked Example Target)</h6>
            <div class="small text-secondary mt-1">
              Stage: <strong class="text-dark">Washed</strong> &bull;
              Length Bounds: <strong class="text-dark font-monospace">[-4.00%, -3.00%]</strong> &bull;
              Width Bounds: <strong class="text-dark font-monospace">[-3.00%, -2.00%]</strong> &bull;
              Pattern: <strong class="text-dark">PTN-W23</strong>
            </div>
          </div>
        </div>
        <router-link to="/classifier" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
          <i class="bi bi-play-fill"></i> Test Worked Example in Lab
        </router-link>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="card mb-4 border-0 shadow-sm bg-white p-3">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-secondary mb-1">Search Group Rules</label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" v-model="filters.search" @input="debouncedFetch" placeholder="Search Group 23, GRP-023, pattern...">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-secondary mb-1">Fabric Stage</label>
          <select class="form-select" v-model="filters.stage" @change="fetchCharts">
            <option value="All">All Stages</option>
            <option value="Washed">Washed</option>
            <option value="Finished">Finished</option>
            <option value="Greige">Greige</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-secondary mb-1">Sort Rules</label>
          <select class="form-select" v-model="filters.sortBy" @change="fetchCharts">
            <option value="id">Rule ID (Ascending)</option>
            <option value="group_name">Group Name</option>
            <option value="length_min">Length Minimum</option>
            <option value="width_min">Width Minimum</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-light border w-100" @click="resetFilters">
            <i class="bi bi-x-circle me-1"></i> Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Master Chart Table -->
    <div class="card">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
          <span class="fw-bold"><i class="bi bi-table me-1 text-primary"></i> Shrinkage Specification Chart</span>
          <span class="badge bg-primary-subtle text-primary border ms-2">{{ pagination.total || 0 }} rules active</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">Per page:</span>
          <select class="form-select form-select-sm" style="width: 80px;" v-model="filters.perPage" @change="fetchCharts">
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 70px;">ID</th>
              <th>Group Name</th>
              <th>Group Code</th>
              <th>Stage</th>
              <th>Fabric Type</th>
              <th class="text-center" style="background-color: #f1f5f9;">Length Bounds [Min, Max]</th>
              <th class="text-center" style="background-color: #eff6ff;">Width Bounds [Min, Max]</th>
              <th>Pattern Code</th>
              <th>Tolerance</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rule in charts" :key="rule.id" :class="{ 'table-primary': rule.group_name === 'Group 23' && rule.stage === 'Washed' }">
              <td class="text-muted small font-monospace">#{{ rule.id }}</td>
              <td class="fw-bold">
                <span class="text-dark">{{ rule.group_name }}</span>
                <span v-if="rule.group_name === 'Group 23' && rule.stage === 'Washed'" class="badge bg-primary ms-1">Benchmark</span>
              </td>
              <td><span class="badge bg-light text-dark border font-monospace">{{ rule.group_code || '-' }}</span></td>
              <td><span class="badge bg-light text-dark border">{{ rule.stage }}</span></td>
              <td class="small">{{ rule.fabric_type || 'All' }}</td>
              <td class="text-center font-monospace fw-semibold" style="background-color: #f8fafc;">
                [{{ rule.length_min }}%, {{ rule.length_max }}%]
              </td>
              <td class="text-center font-monospace fw-semibold" style="background-color: #f0f7ff;">
                [{{ rule.width_min }}%, {{ rule.width_max }}%]
              </td>
              <td class="font-monospace small">{{ rule.pattern_code || '-' }}</td>
              <td class="small">{{ rule.tolerance }}%</td>
              <td class="text-end">
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-primary" @click="openEditModal(rule)" title="Edit Rule">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-outline-danger" @click="confirmDelete(rule)" title="Delete Rule">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!charts.length && !loading">
              <td colspan="10" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 opacity-25"></i>
                <p class="mt-2 mb-0">No chart rules found. Click "Seed 420 Rules" or "Import CSV" to populate.</p>
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="10" class="text-center py-5 text-muted">
                <div class="spinner-border text-primary spinner-border-sm me-2"></div> Loading shrinkage chart rules...
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center py-3" v-if="pagination.last_page > 1">
        <span class="small text-muted">
          Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} rules
        </span>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
            <button class="page-link" @click="changePage(pagination.current_page - 1)">Previous</button>
          </li>
          <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === pagination.current_page }">
            <button class="page-link" @click="changePage(page)">{{ page }}</button>
          </li>
          <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
            <button class="page-link" @click="changePage(pagination.current_page + 1)">Next</button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Import CSV / Excel Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" ref="importModalRef">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up text-primary me-2"></i> Import 400+ Chart Rules</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form @submit.prevent="submitImport">
            <div class="modal-body">
              <p class="small text-muted mb-3">
                Upload a CSV or Excel (.xlsx) file containing shrinkage group specifications. The system automatically reads headers:
                <code>group_name, stage, length_min, length_max, width_min, width_max, pattern_code</code>.
              </p>

              <div class="mb-3">
                <label class="form-label small fw-bold">Select Chart File (.csv or .xlsx)</label>
                <input type="file" class="form-control" ref="fileInputRef" accept=".csv,.xlsx,.xls" required @change="onFileChange">
              </div>

              <div class="p-3 bg-light rounded border text-muted small">
                <i class="bi bi-info-circle text-primary me-1"></i>
                <strong>Tip:</strong> Need the pre-built 400+ records file?
                <a :href="sampleCsvUrl" download class="fw-bold ms-1 text-primary">Download Sample CSV File</a>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="importing || !selectedFile">
                <span v-if="importing" class="spinner-border spinner-border-sm me-1"></span>
                {{ importing ? 'Importing 400+ Records...' : 'Start Import' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Create / Edit Rule Modal -->
    <div class="modal fade" id="ruleModal" tabindex="-1" ref="ruleModalRef">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">
              <i class="bi bi-table text-primary me-1"></i>
              {{ isEditing ? 'Edit Shrinkage Group Rule' : 'New Shrinkage Group Rule' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form @submit.prevent="saveRule">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Group Name *</label>
                  <input type="text" class="form-control" v-model="form.group_name" required placeholder="e.g. Group 23">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Group Code</label>
                  <input type="text" class="form-control" v-model="form.group_code" placeholder="e.g. GRP-023">
                </div>

                <div class="col-md-6">
                  <label class="form-label small fw-bold">Fabric Stage *</label>
                  <select class="form-select" v-model="form.stage" required>
                    <option value="Washed">Washed</option>
                    <option value="Finished">Finished</option>
                    <option value="Greige">Greige</option>
                    <option value="All">All Stages</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Fabric Type</label>
                  <input type="text" class="form-control" v-model="form.fabric_type" placeholder="e.g. Denim Cotton">
                </div>

                <!-- Bounds -->
                <div class="col-6">
                  <label class="form-label small fw-bold text-primary">Length Min (%) *</label>
                  <input type="number" step="0.01" class="form-control font-monospace" v-model.number="form.length_min" required placeholder="-4.00">
                </div>
                <div class="col-6">
                  <label class="form-label small fw-bold text-primary">Length Max (%) *</label>
                  <input type="number" step="0.01" class="form-control font-monospace" v-model.number="form.length_max" required placeholder="-3.00">
                </div>

                <div class="col-6">
                  <label class="form-label small fw-bold text-primary">Width Min (%) *</label>
                  <input type="number" step="0.01" class="form-control font-monospace" v-model.number="form.width_min" required placeholder="-3.00">
                </div>
                <div class="col-6">
                  <label class="form-label small fw-bold text-primary">Width Max (%) *</label>
                  <input type="number" step="0.01" class="form-control font-monospace" v-model.number="form.width_max" required placeholder="-2.00">
                </div>

                <div class="col-md-6">
                  <label class="form-label small fw-bold">Pattern / Marker Code</label>
                  <input type="text" class="form-control" v-model="form.pattern_code" placeholder="e.g. PTN-W23">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Tolerance (%)</label>
                  <input type="number" step="0.01" class="form-control" v-model.number="form.tolerance">
                </div>

                <div class="col-12">
                  <label class="form-label small fw-bold">Notes</label>
                  <textarea class="form-control" rows="2" v-model="form.notes" placeholder="Rule notes..."></textarea>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                {{ isEditing ? 'Update Rule' : 'Save Rule' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  getShrinkageCharts,
  createShrinkageChart,
  updateShrinkageChart,
  deleteShrinkageChart,
  importShrinkageChart,
  seedShrinkageChartSample,
  getExportShrinkageChartsUrl,
  getSampleCsvUrl
} from '../services/api';
import Modal from 'bootstrap/js/dist/modal';

const charts = ref([]);
const loading = ref(false);
const saving = ref(false);
const importing = ref(false);
const seeding = ref(false);
const successMsg = ref('');
const errorMsg = ref('');

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
});

const filters = ref({
  search: '',
  stage: 'All',
  sortBy: 'id',
  perPage: 25,
});

const form = ref({
  id: null,
  group_name: '',
  group_code: '',
  stage: 'Washed',
  fabric_type: 'Denim Cotton',
  length_min: -4.00,
  length_max: -3.00,
  width_min: -3.00,
  width_max: -2.00,
  pattern_code: '',
  tolerance: 0.00,
  notes: '',
  is_active: true,
});

const isEditing = ref(false);
const selectedFile = ref(null);

const ruleModalRef = ref(null);
const importModalRef = ref(null);
const fileInputRef = ref(null);
let ruleModalInstance = null;
let importModalInstance = null;

const sampleCsvUrl = computed(() => getSampleCsvUrl());

const exportUrl = (format) => {
  return getExportShrinkageChartsUrl(format, filters.value.stage);
};

const visiblePages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const delta = 2;
  const range = [];
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i);
  }
  if (current - delta > 2) range.unshift('...');
  if (current + delta < last - 1) range.push('...');
  range.unshift(1);
  if (last > 1) range.push(last);
  return range.filter((v, i, a) => a.indexOf(v) === i);
});

let debounceTimer = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchCharts(1);
  }, 300);
};

const fetchCharts = async (page = 1) => {
  loading.value = true;
  errorMsg.value = '';
  try {
    const params = {
      page,
      per_page: filters.value.perPage,
      search: filters.value.search,
      stage: filters.value.stage,
      sort_by: filters.value.sortBy,
    };
    const res = await getShrinkageCharts(params);
    charts.value = res.data.data;
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total,
      from: res.data.from,
      to: res.data.to,
    };
  } catch (err) {
    errorMsg.value = 'Failed to load shrinkage charts.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page === '...' || page < 1 || page > pagination.value.last_page) return;
  fetchCharts(page);
};

const resetFilters = () => {
  filters.value = {
    search: '',
    stage: 'All',
    sortBy: 'id',
    perPage: 25,
  };
  fetchCharts(1);
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    group_name: '',
    group_code: '',
    stage: 'Washed',
    fabric_type: 'Denim Cotton',
    length_min: -4.00,
    length_max: -3.00,
    width_min: -3.00,
    width_max: -2.00,
    pattern_code: '',
    tolerance: 0.00,
    notes: '',
    is_active: true,
  };
  if (!ruleModalInstance) {
    ruleModalInstance = new Modal(ruleModalRef.value);
  }
  ruleModalInstance.show();
};

const openEditModal = (rule) => {
  isEditing.value = true;
  form.value = { ...rule };
  if (!ruleModalInstance) {
    ruleModalInstance = new Modal(ruleModalRef.value);
  }
  ruleModalInstance.show();
};

const openImportModal = () => {
  selectedFile.value = null;
  if (fileInputRef.value) fileInputRef.value.value = '';
  if (!importModalInstance) {
    importModalInstance = new Modal(importModalRef.value);
  }
  importModalInstance.show();
};

const onFileChange = (e) => {
  selectedFile.value = e.target.files[0] || null;
};

const submitImport = async () => {
  if (!selectedFile.value) return;
  importing.value = true;
  errorMsg.value = '';
  try {
    const fd = new FormData();
    fd.append('file', selectedFile.value);
    const res = await importShrinkageChart(fd);
    successMsg.value = res.data.message || 'File imported successfully!';
    importModalInstance.hide();
    fetchCharts(1);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Import failed. Please check file format.';
    console.error(err);
  } finally {
    importing.value = false;
  }
};

const saveRule = async () => {
  saving.value = true;
  errorMsg.value = '';
  try {
    if (isEditing.value) {
      await updateShrinkageChart(form.value.id, form.value);
      successMsg.value = `Rule ${form.value.group_name} updated successfully.`;
    } else {
      await createShrinkageChart(form.value);
      successMsg.value = `Rule ${form.value.group_name} created successfully.`;
    }
    ruleModalInstance.hide();
    fetchCharts(pagination.value.current_page);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to save rule.';
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (rule) => {
  if (confirm(`Delete rule ${rule.group_name} for ${rule.stage}?`)) {
    try {
      await deleteShrinkageChart(rule.id);
      successMsg.value = `Rule ${rule.group_name} deleted.`;
      fetchCharts(pagination.value.current_page);
    } catch (err) {
      errorMsg.value = 'Failed to delete rule.';
    }
  }
};

const seedSample = async () => {
  seeding.value = true;
  try {
    const res = await seedShrinkageChartSample();
    successMsg.value = res.data.message || 'Seeded 420 rules successfully!';
    fetchCharts(1);
  } catch (err) {
    errorMsg.value = 'Failed to seed sample rules.';
  } finally {
    seeding.value = false;
  }
};

onMounted(() => {
  fetchCharts();
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

