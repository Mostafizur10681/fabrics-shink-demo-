import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
  },
});

export const getDashboardStats = () => api.get('/dashboard/stats');

export const getFabricRolls = (params = {}) => api.get('/fabric-rolls', { params });
export const getFabricRoll = (id) => api.get(`/fabric-rolls/${id}`);
export const createFabricRoll = (data) => api.post('/fabric-rolls', data);
export const updateFabricRoll = (id, data) => api.put(`/fabric-rolls/${id}`, data);
export const deleteFabricRoll = (id) => api.delete(`/fabric-rolls/${id}`);
export const previewClassification = (data) => api.post('/fabric-rolls/preview', data);
export const classifyAllRolls = (stage) => api.post('/fabric-rolls/classify-all', { stage });

export const getShrinkageCharts = (params = {}) => api.get('/shrinkage-charts', { params });
export const getShrinkageChart = (id) => api.get(`/shrinkage-charts/${id}`);
export const createShrinkageChart = (data) => api.post('/shrinkage-charts', data);
export const updateShrinkageChart = (id, data) => api.put(`/shrinkage-charts/${id}`, data);
export const deleteShrinkageChart = (id) => api.delete(`/shrinkage-charts/${id}`);
export const importShrinkageChart = (formData) => api.post('/shrinkage-charts/import', formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
});
export const seedShrinkageChartSample = () => api.post('/shrinkage-charts/seed-sample');

export const getExportFabricRollsUrl = (format = 'xlsx', stage = '', status = '') => {
  const base = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
  return `${base}/fabric-rolls/export?format=${format}&stage=${encodeURIComponent(stage)}&status=${encodeURIComponent(status)}`;
};

export const getExportShrinkageChartsUrl = (format = 'xlsx', stage = '') => {
  const base = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
  return `${base}/shrinkage-charts/export?format=${format}&stage=${encodeURIComponent(stage)}`;
};

export const getSampleCsvUrl = () => {
  const base = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
  return `${base}/shrinkage-charts/sample-csv`;
};

export default api;

