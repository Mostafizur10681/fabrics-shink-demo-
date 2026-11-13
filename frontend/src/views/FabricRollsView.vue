<template>
  <div class="container-fluid px-4">
    <!-- Header & Action Toolbar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
      <div>
        <h3 class="fw-bold text-dark mb-1">Fabric Roll Shrinkage Testing</h3>
        <p class="text-secondary small mb-0">Record roll inspection readings, automatic 6-point averages, and group classification</p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" @click="openCreateModal">
          <i class="bi bi-plus-lg"></i> New Fabric Roll
        </button>
        <button class="btn btn-outline-secondary d-flex align-items-center gap-1" @click="batchReclassify" :disabled="reclassifying">
          <i class="bi bi-arrow-repeat" :class="{ 'spin': reclassifying }"></i> Re-classify All
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

    <!-- Feedback Notification -->
    <div v-if="successMsg" class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> {{ successMsg }}
      <button type="button" class="btn-close" @click="successMsg = ''"></button>
    </div>
    <div v-if="errorMsg" class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ errorMsg }}
      <button type="button" class="btn-close" @click="errorMsg = ''"></button>
    </div>

    <!-- Filters Bar -->
    <div class="card mb-4 border-0 shadow-sm bg-white p-3">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-secondary mb-1">Search Rolls</label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" v-model="filters.search" @input="debouncedFetch" placeholder="Roll #, Lot, Fabric, PO...">
          </div>
        </div>
        <div class="col-md-2 col-sm-6">
          <label class="form-label small fw-semibold text-secondary mb-1">Fabric Stage</label>
          <select class="form-select" v-model="filters.stage" @change="fetchRolls">
            <option value="All">All Stages</option>
            <option value="Washed">Washed</option>
            <option value="Finished">Finished</option>
            <option value="Greige">Greige</option>
          </select>
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="form-label small fw-semibold text-secondary mb-1">Classification Status</label>
          <select class="form-select" v-model="filters.status" @change="fetchRolls">
            <option value="All">All Statuses</option>
            <option value="MATCHED">Matched</option>
            <option value="NO_MATCH">No Matching Group</option>
            <option value="MULTIPLE_MATCHES">Multiple Matching Groups</option>
            <option value="PENDING">Pending</option>
          </select>
        </div>
        <div class="col-md-2 col-sm-6">
          <label class="form-label small fw-semibold text-secondary mb-1">Sort By</label>
          <select class="form-select" v-model="filters.sortBy" @change="fetchRolls">
            <option value="created_at">Test Date / Newest</option>
            <option value="roll_number">Roll Number</option>
            <option value="length_shrinkage_avg">Length Shrinkage</option>
            <option value="width_shrinkage_avg">Width Shrinkage</option>
          </select>
        </div>
        <div class="col-md-2 col-sm-6 d-flex gap-2">
          <button class="btn btn-light border w-100" @click="resetFilters">
            <i class="bi bi-x-circle me-1"></i> Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Fabric Rolls Table -->
    <div class="card">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
          <span class="fw-bold"><i class="bi bi-list-ul me-1 text-primary"></i> Fabric Roll Records</span>
          <span class="badge bg-secondary-subtle text-secondary ms-2">{{ pagination.total || 0 }} total</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">Per page:</span>
          <select class="form-select form-select-sm" style="width: 80px;" v-model="filters.perPage" @change="fetchRolls">
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Roll Details</th>
              <th>Stage</th>
              <th>Buyer / PO</th>
              <th class="text-center" style="background-color: #f1f5f9;">Length Readings (R1, R2, R3)</th>
              <th class="text-center" style="background-color: #eff6ff;">Length Avg</th>
              <th class="text-center" style="background-color: #f1f5f9;">Width Readings (R1, R2, R3)</th>
              <th class="text-center" style="background-color: #eff6ff;">Width Avg</th>
              <th>Classification</th>
              <th>Matched Group</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="roll in rolls" :key="roll.id">
              <td>
                <div class="fw-bold font-monospace text-primary">{{ roll.roll_number }}</div>
                <div class="small text-muted">{{ roll.fabric_type }} &bull; Lot: <span class="fw-semibold">{{ roll.lot_number }}</span></div>
              </td>
              <td>
                <span class="badge bg-light text-dark border">{{ roll.stage }}</span>
              </td>
              <td>
                <div class="small fw-semibold">{{ roll.buyer || 'N/A' }}</div>
                <div class="small text-muted">{{ roll.order_number || '-' }}</div>
              </td>
              <td class="text-center font-monospace small" style="background-color: #f8fafc;">
                {{ roll.length_reading_1 }}% | {{ roll.length_reading_2 }}% | {{ roll.length_reading_3 }}%
              </td>
              <td class="text-center font-monospace fw-bold" style="background-color: #f0f7ff;" :class="roll.length_shrinkage_avg < 0 ? 'text-primary' : 'text-dark'">
                {{ roll.length_shrinkage_avg }}%
              </td>
              <td class="text-center font-monospace small" style="background-color: #f8fafc;">
                {{ roll.width_reading_1 }}% | {{ roll.width_reading_2 }}% | {{ roll.width_reading_3 }}%
              </td>
              <td class="text-center font-monospace fw-bold" style="background-color: #f0f7ff;" :class="roll.width_shrinkage_avg < 0 ? 'text-primary' : 'text-dark'">
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
                  <i class="bi bi-intersect"></i> Multiple Matches
                </span>
                <span v-else class="badge badge-pending">Pending</span>
              </td>
              <td>
                <div v-if="roll.matched_group" class="fw-bold text-dark">{{ roll.matched_group }}</div>
                <div v-else class="small text-muted fst-italic">Out of Chart</div>
              </td>
              <td class="text-end">
                <div class="btn-group btn-group-sm">
                  <button class="btn btn-outline-secondary" @click="viewRollDetails(roll)" title="View Details">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-outline-primary" @click="openEditModal(roll)" title="Edit Readings">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-outline-danger" @click="confirmDelete(roll)" title="Delete Roll">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!rolls.length && !loading">
              <td colspan="10" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 opacity-25"></i>
                <p class="mt-2 mb-0">No fabric rolls found matching your filters.</p>
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="10" class="text-center py-5 text-muted">
                <div class="spinner-border text-primary spinner-border-sm me-2"></div> Loading fabric rolls...
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center py-3" v-if="pagination.last_page > 1">
        <span class="small text-muted">
          Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} records
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

    <!-- Create / Edit Fabric Roll Modal -->
    <div class="modal fade" id="rollModal" tabindex="-1" ref="rollModalRef">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">
              <i class="bi bi-journal-plus me-1 text-primary"></i>
              {{ isEditing ? 'Edit Fabric Roll & Readings' : 'New Fabric Roll Shrinkage Test' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form @submit.prevent="saveRoll">
            <div class="modal-body">
              <!-- Quick Preset Buttons -->
              <div class="mb-3 p-2 bg-light rounded border">
                <div class="small fw-bold text-secondary mb-1"><i class="bi bi-lightning-charge text-warning"></i> Quick Load Test Presets:</div>
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-xs btn-outline-primary" @click="loadPreset('group23')">
                    Group 23 Benchmark (-3.5% / -2.5%)
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-success" @click="loadPreset('boundary_upper')">
                    Exact Upper Boundary (-3.0% / -2.0%)
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-success" @click="loadPreset('boundary_lower')">
                    Exact Lower Boundary (-4.0% / -3.0%)
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-danger" @click="loadPreset('no_match')">
                    No Matching Group (-15.0% / -11.0%)
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-warning" @click="loadPreset('multiple')">
                    Multiple Matching Groups (-2.2% / -1.2%)
                  </button>
                </div>
              </div>

              <!-- General Roll Info -->
              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Roll Number *</label>
                  <input type="text" class="form-control" v-model="form.roll_number" required placeholder="e.g. R-2026-001">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Lot / Batch Number *</label>
                  <input type="text" class="form-control" v-model="form.lot_number" required placeholder="e.g. LOT-DNM-101">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Fabric Stage *</label>
                  <select class="form-select" v-model="form.stage" required @change="updatePreview">
                    <option value="Washed">Washed</option>
                    <option value="Finished">Finished</option>
                    <option value="Greige">Greige</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label small fw-bold">Fabric Type / Construction *</label>
                  <input type="text" class="form-control" v-model="form.fabric_type" required placeholder="e.g. Denim 12oz, Cotton Twill">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Buyer / Brand</label>
                  <input type="text" class="form-control" v-model="form.buyer" placeholder="e.g. Levi's, Zara, H&M">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Order / PO Number</label>
                  <input type="text" class="form-control" v-model="form.order_number" placeholder="e.g. PO-77291">
                </div>

                <div class="col-md-4">
                  <label class="form-label small fw-bold">Roll Length (meters)</label>
                  <input type="number" step="0.1" class="form-control" v-model="form.roll_length" placeholder="100.0">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Test Date *</label>
                  <input type="date" class="form-control" v-model="form.test_date" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Tested By / Inspector</label>
                  <input type="text" class="form-control" v-model="form.tested_by" placeholder="Tech / Lab Analyst">
                </div>
              </div>

              <!-- 6 Shrinkage Readings -->
              <h6 class="fw-bold text-dark border-bottom pb-2 mt-4">
                <i class="bi bi-rulers text-primary me-1"></i> Six Shrinkage Readings (%)
              </h6>
              <div class="row g-3 mb-3">
                <!-- Length 3 Readings -->
                <div class="col-md-6 border-end pe-md-4">
                  <div class="fw-semibold text-primary small mb-2"><i class="bi bi-arrow-down-up"></i> Lengthwise (Warp) Readings (%)</div>
                  <div class="row g-2">
                    <div class="col-4">
                      <label class="form-label small text-muted">Reading 1</label>
                      <input type="number" step="0.01" class="form-control reading-input" v-model.number="form.length_reading_1" @input="updatePreview" required>
                    </div>
                    <div class="col-4">
                      <label class="form-label small text-muted">Reading 2</label>
                      <input type="number" step="0.01" class="form-control reading-input" v-model.number="form.length_reading_2" @input="updatePreview" required>
                    </div>
                    <div class="col-4">
                      <label class="form-label small text-muted">Reading 3</label>
                      <input type="number" step="0.01" class="form-control reading-input" v-model.number="form.length_reading_3" @input="updatePreview" required>
                    </div>
                  </div>
                </div>

                <!-- Width 3 Readings -->
                <div class="col-md-6 ps-md-4">
                  <div class="fw-semibold text-primary small mb-2"><i class="bi bi-arrow-left-right"></i> Widthwise (Weft) Readings (%)</div>
                  <div class="row g-2">
                    <div class="col-4">
                      <label class="form-label small text-muted">Reading 1</label>
                      <input type="number" step="0.01" class="form-control reading-input" v-model.number="form.width_reading_1" @input="updatePreview" required>
                    </div>
                    <div class="col-4">
                      <label class="form-label small text-muted">Reading 2</label>
                      <input type="number" step="0.01" class="form-control reading-input" v-model.number="form.width_reading_2" @input="updatePreview" required>
                    </div>
                    <div class="col-4">
                      <label class="form-label small text-muted">Reading 3</label>
                      <input type="number" step="0.01" class="form-control reading-input" v-model.number="form.width_reading_3" @input="updatePreview" required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Live Automatic Calculations & Group Preview Card -->
              <div class="card bg-light border p-3 mt-3">
                <div class="row align-items-center">
                  <div class="col-md-5">
                    <div class="small text-muted fw-semibold">Calculated 6-Point Averages:</div>
                    <div class="d-flex align-items-center gap-3 mt-1">
                      <div>
                        <span class="small text-secondary">Length Avg:</span>
                        <span class="fs-5 fw-bold font-monospace ms-1 text-primary">{{ computedLengthAvg }}%</span>
                      </div>
                      <div class="border-start ps-3">
                        <span class="small text-secondary">Width Avg:</span>
                        <span class="fs-5 fw-bold font-monospace ms-1 text-primary">{{ computedWidthAvg }}%</span>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-7 border-start ps-md-3">
                    <div class="small text-muted fw-semibold">Classification Preview ({{ form.stage }}):</div>
                    <div class="mt-1 d-flex align-items-center gap-2">
                      <span v-if="previewResult.status === 'MATCHED'" class="badge badge-matched fs-6 py-2 px-3">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ previewResult.matched_group }}
                        <span v-if="previewResult.is_boundary_match" class="badge bg-purple text-white ms-2" style="background-color: #6f42c1;">Exact Boundary Match</span>
                      </span>
                      <span v-else-if="previewResult.status === 'NO_MATCH'" class="badge badge-no-match fs-6 py-2 px-3">
                        <i class="bi bi-x-circle-fill me-1"></i> No Matching Group (Out of bounds)
                      </span>
                      <span v-else-if="previewResult.status === 'MULTIPLE_MATCHES'" class="badge badge-multiple fs-6 py-2 px-3">
                        <i class="bi bi-intersect me-1"></i> Multiple Matches: {{ previewResult.matched_group }}
                      </span>
                      <span v-else class="text-muted small">Enter 6 readings to preview classification.</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-3">
                <label class="form-label small fw-bold">Inspection Notes</label>
                <textarea class="form-control" rows="2" v-model="form.notes" placeholder="Optional inspector remarks or test condition notes..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                {{ isEditing ? 'Update & Re-classify' : 'Save & Classify Roll' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Roll Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" ref="detailsModalRef">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" v-if="selectedRoll">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">
              <i class="bi bi-file-earmark-text text-primary me-1"></i>
              Inspection Details: {{ selectedRoll.roll_number }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded" style="background-color: #f1f5f9;">
              <div>
                <span class="small text-muted d-block">Classification Status</span>
                <span v-if="selectedRoll.classification_status === 'MATCHED'" class="badge badge-matched fs-6 mt-1">
                  Matched
                </span>
                <span v-else-if="selectedRoll.classification_status === 'NO_MATCH'" class="badge badge-no-match fs-6 mt-1">
                  No Matching Group
                </span>
                <span v-else-if="selectedRoll.classification_status === 'MULTIPLE_MATCHES'" class="badge badge-multiple fs-6 mt-1">
                  Multiple Matches
                </span>
              </div>
              <div class="text-end">
                <span class="small text-muted d-block">Assigned Group</span>
                <span class="fs-5 fw-bold text-dark">{{ selectedRoll.matched_group || 'None' }}</span>
              </div>
            </div>

            <table class="table table-bordered table-sm small">
              <tbody>
                <tr>
                  <th class="bg-light" style="width: 40%;">Lot Number</th>
                  <td>{{ selectedRoll.lot_number }}</td>
                </tr>
                <tr>
                  <th class="bg-light">Fabric Stage</th>
                  <td><span class="badge bg-light text-dark border">{{ selectedRoll.stage }}</span></td>
                </tr>
                <tr>
                  <th class="bg-light">Fabric Type</th>
                  <td>{{ selectedRoll.fabric_type }}</td>
                </tr>
                <tr>
                  <th class="bg-light">Buyer & PO</th>
                  <td>{{ selectedRoll.buyer || 'N/A' }} ({{ selectedRoll.order_number || '-' }})</td>
                </tr>
                <tr>
                  <th class="bg-light">Test Date</th>
                  <td>{{ selectedRoll.test_date }}</td>
                </tr>
                <tr>
                  <th class="bg-light">Tested By</th>
                  <td>{{ selectedRoll.tested_by || 'Quality Tech' }}</td>
                </tr>
                <tr>
                  <th class="bg-light">Length Readings</th>
                  <td class="font-monospace">{{ selectedRoll.length_reading_1 }}%, {{ selectedRoll.length_reading_2 }}%, {{ selectedRoll.length_reading_3 }}%</td>
                </tr>
                <tr>
                  <th class="bg-light">Length Average</th>
                  <td class="font-monospace fw-bold text-primary">{{ selectedRoll.length_shrinkage_avg }}%</td>
                </tr>
                <tr>
                  <th class="bg-light">Width Readings</th>
                  <td class="font-monospace">{{ selectedRoll.width_reading_1 }}%, {{ selectedRoll.width_reading_2 }}%, {{ selectedRoll.width_reading_3 }}%</td>
                </tr>
                <tr>
                  <th class="bg-light">Width Average</th>
                  <td class="font-monospace fw-bold text-primary">{{ selectedRoll.width_shrinkage_avg }}%</td>
                </tr>
                <tr>
                  <th class="bg-light">Boundary Match</th>
                  <td>{{ selectedRoll.is_boundary_match ? 'Yes (Exact threshold hit)' : 'No (Interior match)' }}</td>
                </tr>
                <tr v-if="selectedRoll.matching_details">
                  <th class="bg-light">Rule Info</th>
                  <td>
                    <pre class="mb-0 small" style="white-space: pre-wrap;">{{ JSON.stringify(selectedRoll.matching_details, null, 2) }}</pre>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  getFabricRolls,
  createFabricRoll,
  updateFabricRoll,
  deleteFabricRoll,
  previewClassification,
  classifyAllRolls,
  getExportFabricRollsUrl
} from '../services/api';
import Modal from 'bootstrap/js/dist/modal';

const rolls = ref([]);
const loading = ref(false);
const saving = ref(false);
const reclassifying = ref(false);
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
  status: 'All',
  sortBy: 'created_at',
  perPage: 15,
});

const form = ref({
  id: null,
  roll_number: '',
  lot_number: '',
  fabric_type: 'Denim Cotton 12oz',
  buyer: "Levi's",
  order_number: 'PO-98432',
  stage: 'Washed',
  roll_length: 100.0,
  test_date: new Date().toISOString().substring(0, 10),
  tested_by: 'QA Officer',
  length_reading_1: -3.50,
  length_reading_2: -3.40,
  length_reading_3: -3.60,
  width_reading_1: -2.50,
  width_reading_2: -2.40,
  width_reading_3: -2.60,
  notes: '',
});

const isEditing = ref(false);
const previewResult = ref({});
const selectedRoll = ref(null);

const rollModalRef = ref(null);
const detailsModalRef = ref(null);
let rollModalInstance = null;
let detailsModalInstance = null;

const computedLengthAvg = computed(() => {
  const l1 = Number(form.value.length_reading_1) || 0;
  const l2 = Number(form.value.length_reading_2) || 0;
  const l3 = Number(form.value.length_reading_3) || 0;
  return Number(((l1 + l2 + l3) / 3).toFixed(2));
});

const computedWidthAvg = computed(() => {
  const w1 = Number(form.value.width_reading_1) || 0;
  const w2 = Number(form.value.width_reading_2) || 0;
  const w3 = Number(form.value.width_reading_3) || 0;
  return Number(((w1 + w2 + w3) / 3).toFixed(2));
});

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

const exportUrl = (format) => {
  return getExportFabricRollsUrl(format, filters.value.stage, filters.value.status);
};

let debounceTimer = null;
const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchRolls(1);
  }, 300);
};

const fetchRolls = async (page = 1) => {
  loading.value = true;
  errorMsg.value = '';
  try {
    const params = {
      page,
      per_page: filters.value.perPage,
      search: filters.value.search,
      stage: filters.value.stage,
      status: filters.value.status,
      sort_by: filters.value.sortBy,
    };
    const res = await getFabricRolls(params);
    rolls.value = res.data.data;
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total,
      from: res.data.from,
      to: res.data.to,
    };
  } catch (err) {
    errorMsg.value = 'Failed to load fabric rolls from backend.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page === '...' || page < 1 || page > pagination.value.last_page) return;
  fetchRolls(page);
};

const resetFilters = () => {
  filters.value = {
    search: '',
    stage: 'All',
    status: 'All',
    sortBy: 'created_at',
    perPage: 15,
  };
  fetchRolls(1);
};

const updatePreview = async () => {
  try {
    const res = await previewClassification({
      length_reading_1: form.value.length_reading_1,
      length_reading_2: form.value.length_reading_2,
      length_reading_3: form.value.length_reading_3,
      width_reading_1: form.value.width_reading_1,
      width_reading_2: form.value.width_reading_2,
      width_reading_3: form.value.width_reading_3,
      stage: form.value.stage,
      fabric_type: form.value.fabric_type,
    });
    previewResult.value = res.data.classification;
  } catch (err) {
    console.error(err);
  }
};

const loadPreset = (type) => {
  if (type === 'group23') {
    form.value.roll_number = 'R-G23-DEMO-' + Math.floor(Math.random() * 1000);
    form.value.stage = 'Washed';
    form.value.length_reading_1 = -3.50;
    form.value.length_reading_2 = -3.40;
    form.value.length_reading_3 = -3.60;
    form.value.width_reading_1 = -2.50;
    form.value.width_reading_2 = -2.40;
    form.value.width_reading_3 = -2.60;
  } else if (type === 'boundary_upper') {
    form.value.roll_number = 'R-BND-UP-' + Math.floor(Math.random() * 1000);
    form.value.stage = 'Washed';
    form.value.length_reading_1 = -3.00;
    form.value.length_reading_2 = -3.00;
    form.value.length_reading_3 = -3.00;
    form.value.width_reading_1 = -2.00;
    form.value.width_reading_2 = -2.00;
    form.value.width_reading_3 = -2.00;
  } else if (type === 'boundary_lower') {
    form.value.roll_number = 'R-BND-LO-' + Math.floor(Math.random() * 1000);
    form.value.stage = 'Washed';
    form.value.length_reading_1 = -4.00;
    form.value.length_reading_2 = -4.00;
    form.value.length_reading_3 = -4.00;
    form.value.width_reading_1 = -3.00;
    form.value.width_reading_2 = -3.00;
    form.value.width_reading_3 = -3.00;
  } else if (type === 'no_match') {
    form.value.roll_number = 'R-NOMATCH-' + Math.floor(Math.random() * 1000);
    form.value.stage = 'Washed';
    form.value.length_reading_1 = -15.00;
    form.value.length_reading_2 = -15.50;
    form.value.length_reading_3 = -14.50;
    form.value.width_reading_1 = -11.00;
    form.value.width_reading_2 = -10.80;
    form.value.width_reading_3 = -11.20;
  } else if (type === 'multiple') {
    form.value.roll_number = 'R-MULTI-' + Math.floor(Math.random() * 1000);
    form.value.stage = 'Washed';
    form.value.length_reading_1 = -2.20;
    form.value.length_reading_2 = -2.20;
    form.value.length_reading_3 = -2.20;
    form.value.width_reading_1 = -1.20;
    form.value.width_reading_2 = -1.20;
    form.value.width_reading_3 = -1.20;
  }
  updatePreview();
};

const openCreateModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    roll_number: 'R-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000),
    lot_number: 'LOT-' + Math.floor(100 + Math.random() * 900),
    fabric_type: 'Denim Cotton 12oz',
    buyer: "Levi's",
    order_number: 'PO-' + Math.floor(10000 + Math.random() * 90000),
    stage: 'Washed',
    roll_length: 100.0,
    test_date: new Date().toISOString().substring(0, 10),
    tested_by: 'QA Officer',
    length_reading_1: -3.50,
    length_reading_2: -3.40,
    length_reading_3: -3.60,
    width_reading_1: -2.50,
    width_reading_2: -2.40,
    width_reading_3: -2.60,
    notes: '',
  };
  updatePreview();
  if (!rollModalInstance) {
    rollModalInstance = new Modal(rollModalRef.value);
  }
  rollModalInstance.show();
};

const openEditModal = (roll) => {
  isEditing.value = true;
  form.value = {
    id: roll.id,
    roll_number: roll.roll_number,
    lot_number: roll.lot_number,
    fabric_type: roll.fabric_type,
    buyer: roll.buyer,
    order_number: roll.order_number,
    stage: roll.stage,
    roll_length: roll.roll_length,
    test_date: roll.test_date ? roll.test_date.substring(0, 10) : '',
    tested_by: roll.tested_by,
    length_reading_1: roll.length_reading_1,
    length_reading_2: roll.length_reading_2,
    length_reading_3: roll.length_reading_3,
    width_reading_1: roll.width_reading_1,
    width_reading_2: roll.width_reading_2,
    width_reading_3: roll.width_reading_3,
    notes: roll.notes,
  };
  updatePreview();
  if (!rollModalInstance) {
    rollModalInstance = new Modal(rollModalRef.value);
  }
  rollModalInstance.show();
};

const viewRollDetails = (roll) => {
  selectedRoll.value = roll;
  if (!detailsModalInstance) {
    detailsModalInstance = new Modal(detailsModalRef.value);
  }
  detailsModalInstance.show();
};

const saveRoll = async () => {
  saving.value = true;
  errorMsg.value = '';
  try {
    if (isEditing.value) {
      await updateFabricRoll(form.value.id, form.value);
      successMsg.value = `Roll ${form.value.roll_number} updated and classified successfully!`;
    } else {
      await createFabricRoll(form.value);
      successMsg.value = `Roll ${form.value.roll_number} recorded and classified successfully!`;
    }
    rollModalInstance.hide();
    fetchRolls(pagination.value.current_page);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to save fabric roll.';
    console.error(err);
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (roll) => {
  if (confirm(`Are you sure you want to delete roll ${roll.roll_number}?`)) {
    try {
      await deleteFabricRoll(roll.id);
      successMsg.value = `Roll ${roll.roll_number} deleted.`;
      fetchRolls(pagination.value.current_page);
    } catch (err) {
      errorMsg.value = 'Failed to delete fabric roll.';
    }
  }
};

const batchReclassify = async () => {
  reclassifying.value = true;
  try {
    const res = await classifyAllRolls(filters.value.stage);
    successMsg.value = res.data.message || 'All rolls reclassified!';
    fetchRolls(pagination.value.current_page);
  } catch (err) {
    errorMsg.value = 'Failed to reclassify rolls.';
  } finally {
    reclassifying.value = false;
  }
};

onMounted(() => {
  fetchRolls();
});
</script>

<style scoped>
.btn-xs {
  padding: 0.2rem 0.5rem;
  font-size: 0.75rem;
}
</style>

