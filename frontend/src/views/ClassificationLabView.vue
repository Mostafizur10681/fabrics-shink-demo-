<template>
  <div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
      <div>
        <h3 class="fw-bold text-dark mb-1">Interactive Classification Lab</h3>
        <p class="text-secondary small mb-0">Direct testing engine for 6-point shrinkage readings, boundary verification, and pattern group allocation</p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <router-link to="/rolls" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-collection me-1"></i> Fabric Rolls Log
        </router-link>
        <router-link to="/chart" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-table me-1"></i> View 400+ Rules
        </router-link>
      </div>
    </div>

    <!-- Quick Workflow Verification Cards -->
    <div class="row g-3 mb-4">
      <!-- Card 1: Group 23 Worked Example -->
      <div class="col-md-6 col-xl-2 col-sm-6">
        <div class="card h-100 p-3 border-primary shadow-sm hover-card" @click="runPreset('group23')">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-primary text-white">Requirement #10</span>
            <i class="bi bi-play-circle-fill text-primary fs-5"></i>
          </div>
          <h6 class="fw-bold mb-1 text-dark">Group 23 Worked Example</h6>
          <small class="text-muted d-block mb-2">Target benchmark test</small>
          <div class="small font-monospace text-primary bg-light p-1 rounded">
            L: -3.50% | W: -2.50%
          </div>
          <div class="mt-2 text-success small fw-semibold">
            <i class="bi bi-check2"></i> Expects: Group 23
          </div>
        </div>
      </div>

      <!-- Card 2: Upper Boundary Matching -->
      <div class="col-md-6 col-xl-2 col-sm-6">
        <div class="card h-100 p-3 border-purple shadow-sm hover-card" style="border-color: #6f42c1;" @click="runPreset('boundary_upper')">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge text-white" style="background-color: #6f42c1;">Requirement #13</span>
            <i class="bi bi-play-circle-fill fs-5" style="color: #6f42c1;"></i>
          </div>
          <h6 class="fw-bold mb-1 text-dark">Upper Boundary Match</h6>
          <small class="text-muted d-block mb-2">Exact upper threshold hit</small>
          <div class="small font-monospace bg-light p-1 rounded" style="color: #6f42c1;">
            L: -3.00% | W: -2.00%
          </div>
          <div class="mt-2 text-purple small fw-semibold" style="color: #6f42c1;">
            <i class="bi bi-bounding-box"></i> Boundary: Group 23
          </div>
        </div>
      </div>

      <!-- Card 3: Lower Boundary Matching -->
      <div class="col-md-6 col-xl-2 col-sm-6">
        <div class="card h-100 p-3 border-purple shadow-sm hover-card" style="border-color: #6f42c1;" @click="runPreset('boundary_lower')">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge text-white" style="background-color: #6f42c1;">Requirement #13</span>
            <i class="bi bi-play-circle-fill fs-5" style="color: #6f42c1;"></i>
          </div>
          <h6 class="fw-bold mb-1 text-dark">Lower Boundary Match</h6>
          <small class="text-muted d-block mb-2">Exact lower threshold hit</small>
          <div class="small font-monospace bg-light p-1 rounded" style="color: #6f42c1;">
            L: -4.00% | W: -3.00%
          </div>
          <div class="mt-2 text-purple small fw-semibold" style="color: #6f42c1;">
            <i class="bi bi-bounding-box"></i> Boundary: Group 23
          </div>
        </div>
      </div>

      <!-- Card 4: No Matching Group -->
      <div class="col-md-6 col-xl-3 col-sm-6">
        <div class="card h-100 p-3 border-danger shadow-sm hover-card" @click="runPreset('no_match')">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-danger text-white">Requirement #11</span>
            <i class="bi bi-play-circle-fill text-danger fs-5"></i>
          </div>
          <h6 class="fw-bold mb-1 text-dark">No Matching Group</h6>
          <small class="text-muted d-block mb-2">Excessive shrinkage out of spec</small>
          <div class="small font-monospace text-danger bg-light p-1 rounded">
            L: -15.00% | W: -11.00%
          </div>
          <div class="mt-2 text-danger small fw-semibold">
            <i class="bi bi-x-circle"></i> Expects: NO_MATCH
          </div>
        </div>
      </div>

      <!-- Card 5: Multiple Matching Groups -->
      <div class="col-md-6 col-xl-3 col-sm-6">
        <div class="card h-100 p-3 border-warning shadow-sm hover-card" @click="runPreset('multiple')">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge bg-warning text-dark">Requirement #12</span>
            <i class="bi bi-play-circle-fill text-warning fs-5"></i>
          </div>
          <h6 class="fw-bold mb-1 text-dark">Multiple Matching Groups</h6>
          <small class="text-muted d-block mb-2">Overlapping group rules</small>
          <div class="small font-monospace text-warning bg-light p-1 rounded">
            L: -2.20% | W: -1.20%
          </div>
          <div class="mt-2 text-warning small fw-semibold">
            <i class="bi bi-intersect"></i> Expects: Group 99-A, 99-B
          </div>
        </div>
      </div>
    </div>

    <!-- Interactive Testing Workbench -->
    <div class="row g-4">
      <!-- Input Panel -->
      <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-sliders me-1 text-primary"></i> Shrinkage Test Inputs</span>
            <button class="btn btn-sm btn-light border" @click="resetToDefaults">Reset Inputs</button>
          </div>
          <div class="card-body">
            <!-- Stage Selection -->
            <div class="mb-4">
              <label class="form-label fw-bold small text-secondary">1. Select Fabric Stage *</label>
              <div class="d-flex gap-2">
                <button
                  type="button"
                  class="btn flex-fill"
                  :class="stage === 'Washed' ? 'btn-primary' : 'btn-outline-secondary'"
                  @click="setStage('Washed')"
                >
                  <i class="bi bi-droplet-half me-1"></i> Washed
                </button>
                <button
                  type="button"
                  class="btn flex-fill"
                  :class="stage === 'Finished' ? 'btn-primary' : 'btn-outline-secondary'"
                  @click="setStage('Finished')"
                >
                  <i class="bi bi-patch-check me-1"></i> Finished
                </button>
                <button
                  type="button"
                  class="btn flex-fill"
                  :class="stage === 'Greige' ? 'btn-primary' : 'btn-outline-secondary'"
                  @click="setStage('Greige')"
                >
                  <i class="bi bi-box me-1"></i> Greige
                </button>
              </div>
            </div>

            <!-- Length Readings -->
            <div class="mb-4 p-3 rounded bg-light border">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="fw-bold small text-primary mb-0">
                  <i class="bi bi-arrow-down-up me-1"></i> 2. Length (Warp) 3 Readings (%)
                </label>
                <span class="small font-monospace text-muted">Avg: <strong class="text-primary">{{ computedLengthAvg }}%</strong></span>
              </div>
              <div class="row g-2">
                <div class="col-4">
                  <label class="small text-muted">Reading 1</label>
                  <input type="number" step="0.01" class="form-control reading-input" v-model.number="l1" @input="runClassification">
                </div>
                <div class="col-4">
                  <label class="small text-muted">Reading 2</label>
                  <input type="number" step="0.01" class="form-control reading-input" v-model.number="l2" @input="runClassification">
                </div>
                <div class="col-4">
                  <label class="small text-muted">Reading 3</label>
                  <input type="number" step="0.01" class="form-control reading-input" v-model.number="l3" @input="runClassification">
                </div>
              </div>
            </div>

            <!-- Width Readings -->
            <div class="mb-4 p-3 rounded bg-light border">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="fw-bold small text-primary mb-0">
                  <i class="bi bi-arrow-left-right me-1"></i> 3. Width (Weft) 3 Readings (%)
                </label>
                <span class="small font-monospace text-muted">Avg: <strong class="text-primary">{{ computedWidthAvg }}%</strong></span>
              </div>
              <div class="row g-2">
                <div class="col-4">
                  <label class="small text-muted">Reading 1</label>
                  <input type="number" step="0.01" class="form-control reading-input" v-model.number="w1" @input="runClassification">
                </div>
                <div class="col-4">
                  <label class="small text-muted">Reading 2</label>
                  <input type="number" step="0.01" class="form-control reading-input" v-model.number="w2" @input="runClassification">
                </div>
                <div class="col-4">
                  <label class="small text-muted">Reading 3</label>
                  <input type="number" step="0.01" class="form-control reading-input" v-model.number="w3" @input="runClassification">
                </div>
              </div>
            </div>

            <button class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2" @click="runClassification" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm"></span>
              <i v-else class="bi bi-cpu"></i>
              Calculate Averages & Classify Group
            </button>
          </div>
        </div>
      </div>

      <!-- Classification Result Report Panel -->
      <div class="col-lg-6">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-clipboard-check me-1 text-success"></i> Classification Engine Report</span>
            <span class="badge bg-light text-dark border">Auto-calculated</span>
          </div>
          <div class="card-body d-flex flex-column justify-content-between">
            <div v-if="result.status">
              <!-- Result Status Header Banner -->
              <div
                class="p-4 rounded mb-4 text-center"
                :class="{
                  'bg-success-subtle text-success border border-success': result.status === 'MATCHED',
                  'bg-danger-subtle text-danger border border-danger': result.status === 'NO_MATCH',
                  'bg-warning-subtle text-warning border border-warning': result.status === 'MULTIPLE_MATCHES'
                }"
              >
                <div class="fs-1 mb-2">
                  <i v-if="result.status === 'MATCHED'" class="bi bi-check-circle-fill"></i>
                  <i v-else-if="result.status === 'NO_MATCH'" class="bi bi-x-octagon-fill"></i>
                  <i v-else class="bi bi-intersect"></i>
                </div>
                <h4 class="fw-bold mb-1">
                  <span v-if="result.status === 'MATCHED'">Matched: {{ result.matched_group }}</span>
                  <span v-else-if="result.status === 'NO_MATCH'">No Matching Group</span>
                  <span v-else>Multiple Matching Groups</span>
                </h4>
                <div class="small">
                  <span v-if="result.is_boundary_match" class="badge bg-purple text-white px-2 py-1" style="background-color: #6f42c1;">
                    <i class="bi bi-bounding-box me-1"></i> Exact Boundary Threshold Hit
                  </span>
                  <span v-else-if="result.status === 'MATCHED'" class="badge bg-success text-white px-2 py-1">
                    Standard Tolerance Range
                  </span>
                  <span v-else-if="result.status === 'NO_MATCH'" class="badge bg-danger text-white px-2 py-1">
                    Out of All Specification Boundaries
                  </span>
                  <span v-else class="badge bg-warning text-dark px-2 py-1">
                    Rule Overlap Warning
                  </span>
                </div>
              </div>

              <!-- Technical Details Breakdown -->
              <div class="card border bg-light mb-3 p-3">
                <h6 class="fw-bold text-dark mb-2">Calculation & Rule Match Breakdown:</h6>
                <div class="row g-2 small">
                  <div class="col-6">
                    <span class="text-muted">Length Average:</span>
                    <strong class="font-monospace ms-1 text-primary">{{ computedLengthAvg }}%</strong>
                  </div>
                  <div class="col-6">
                    <span class="text-muted">Width Average:</span>
                    <strong class="font-monospace ms-1 text-primary">{{ computedWidthAvg }}%</strong>
                  </div>
                  <div class="col-6">
                    <span class="text-muted">Stage Evaluated:</span>
                    <strong class="ms-1">{{ stage }}</strong>
                  </div>
                  <div class="col-6">
                    <span class="text-muted">Status Code:</span>
                    <strong class="ms-1 font-monospace">{{ result.status }}</strong>
                  </div>
                </div>

                <div v-if="result.matching_details" class="mt-3 pt-2 border-top">
                  <div class="text-muted small mb-1">Matching Details:</div>
                  <div class="p-2 bg-white rounded border small font-monospace">
                    {{ result.matching_details }}
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-5 text-muted my-auto">
              <i class="bi bi-cpu fs-1 opacity-25"></i>
              <p class="mt-2 mb-0">Select a preset above or enter 6 shrinkage readings to run classification.</p>
            </div>

            <!-- Quick Action: Save to Database -->
            <div class="mt-auto pt-3 border-top" v-if="result.status">
              <button class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm" @click="saveAsOfficialRoll" :disabled="savingRoll">
                <span v-if="savingRoll" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-save"></i>
                Save this Test as Official Fabric Roll
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast / Notification -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
      <div v-if="toastMsg" class="alert alert-success shadow d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> {{ toastMsg }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { previewClassification, createFabricRoll } from '../services/api';

const stage = ref('Washed');
const l1 = ref(-3.50);
const l2 = ref(-3.40);
const l3 = ref(-3.60);
const w1 = ref(-2.50);
const w2 = ref(-2.40);
const w3 = ref(-2.60);

const loading = ref(false);
const savingRoll = ref(false);
const toastMsg = ref('');
const result = ref({});

const computedLengthAvg = computed(() => {
  return Number(((l1.value + l2.value + l3.value) / 3).toFixed(2));
});

const computedWidthAvg = computed(() => {
  return Number(((w1.value + w2.value + w3.value) / 3).toFixed(2));
});

const setStage = (s) => {
  stage.value = s;
  runClassification();
};

const runClassification = async () => {
  loading.value = true;
  try {
    const res = await previewClassification({
      length_reading_1: l1.value,
      length_reading_2: l2.value,
      length_reading_3: l3.value,
      width_reading_1: w1.value,
      width_reading_2: w2.value,
      width_reading_3: w3.value,
      stage: stage.value,
    });
    result.value = res.data.classification;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const runPreset = (type) => {
  if (type === 'group23') {
    stage.value = 'Washed';
    l1.value = -3.50; l2.value = -3.40; l3.value = -3.60;
    w1.value = -2.50; w2.value = -2.40; w3.value = -2.60;
  } else if (type === 'boundary_upper') {
    stage.value = 'Washed';
    l1.value = -3.00; l2.value = -3.00; l3.value = -3.00;
    w1.value = -2.00; w2.value = -2.00; w3.value = -2.00;
  } else if (type === 'boundary_lower') {
    stage.value = 'Washed';
    l1.value = -4.00; l2.value = -4.00; l3.value = -4.00;
    w1.value = -3.00; w2.value = -3.00; w3.value = -3.00;
  } else if (type === 'no_match') {
    stage.value = 'Washed';
    l1.value = -15.00; l2.value = -15.50; l3.value = -14.50;
    w1.value = -11.00; w2.value = -10.80; w3.value = -11.20;
  } else if (type === 'multiple') {
    stage.value = 'Washed';
    l1.value = -2.20; l2.value = -2.20; l3.value = -2.20;
    w1.value = -1.20; w2.value = -1.20; w3.value = -1.20;
  }
  runClassification();
};

const resetToDefaults = () => {
  runPreset('group23');
};

const saveAsOfficialRoll = async () => {
  savingRoll.value = true;
  try {
    const rollNumber = 'R-LAB-' + Math.floor(1000 + Math.random() * 9000);
    await createFabricRoll({
      roll_number: rollNumber,
      lot_number: 'LOT-LAB-01',
      fabric_type: 'Lab Test Denim',
      buyer: "Quality Lab",
      stage: stage.value,
      roll_length: 100.0,
      test_date: new Date().toISOString().substring(0, 10),
      tested_by: 'Lab Specialist',
      length_reading_1: l1.value,
      length_reading_2: l2.value,
      length_reading_3: l3.value,
      width_reading_1: w1.value,
      width_reading_2: w2.value,
      width_reading_3: w3.value,
      notes: 'Generated from Classification Lab test run',
    });
    toastMsg.value = `Successfully saved roll ${rollNumber} to database!`;
    setTimeout(() => { toastMsg.value = ''; }, 3500);
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to save test roll.');
  } finally {
    savingRoll.value = false;
  }
};

onMounted(() => {
  runPreset('group23');
});
</script>

<style scoped>
.hover-card {
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.1) !important;
}
</style>

