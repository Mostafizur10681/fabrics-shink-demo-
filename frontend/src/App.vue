<template>
  <div class="d-flex flex-column min-vh-100">
    <!-- Main Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-2">
      <div class="container-fluid px-4">
        <router-link to="/" class="navbar-brand d-flex align-items-center gap-2">
          <div class="bg-primary rounded p-1 d-flex align-items-center justify-content-center text-white" style="width: 34px; height: 34px;">
            <i class="bi bi-rulers fs-5"></i>
          </div>
          <div>
            <span class="fw-bold fs-5 tracking-tight">FabricShrink</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 text-uppercase" style="font-size: 0.65rem;">v1.0 Pro</span>
          </div>
        </router-link>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
            <li class="nav-item">
              <router-link to="/" class="nav-link px-3" exact-active-class="active fw-semibold text-white">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/rolls" class="nav-link px-3" active-class="active fw-semibold text-white">
                <i class="bi bi-collection me-1"></i> Fabric Rolls
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/chart" class="nav-link px-3" active-class="active fw-semibold text-white">
                <i class="bi bi-table me-1"></i> Shrinkage Master Chart (400+)
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/classifier" class="nav-link px-3" active-class="active fw-semibold text-white">
                <i class="bi bi-cpu me-1"></i> Classification Lab
              </router-link>
            </li>
          </ul>

          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle d-flex align-items-center gap-1 px-2 py-2">
              <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
              API Connected
            </span>
            <router-link to="/classifier" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
              <i class="bi bi-play-circle"></i> Test Group 23
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Content Router Area -->
    <main class="flex-grow-1 py-4">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 mt-auto">
      <div class="container-fluid px-4 d-flex flex-wrap justify-content-between align-items-center text-muted small">
        <div>
          <strong>Fabric Shrinkage Management System</strong> &bull; Textile Quality & Pattern Engineering
        </div>
        <div class="d-flex align-items-center gap-3">
          <span>Backend: Laravel 10 (PHP 8.3)</span>
          <span>Database: MySQL (MariaDB 10.4)</span>
          <span>Frontend: Vue.js 3 + Vite</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
</script>

<style scoped>
.nav-link {
  transition: all 0.2s ease;
  border-radius: 6px;
}
.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.08);
  color: #fff !important;
}
</style>

