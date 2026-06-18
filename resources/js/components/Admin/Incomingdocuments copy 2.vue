<template>
  <div class="card border-0 shadow-md">
    <!-- ===== HEADER ===== -->
    <div class="card-header py-3" style="background: #0f766e">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center">
          <div class="step-header-icon bg-white bg-opacity-20 p-2 rounded">
            <i :class="headerIcon" style="color: rgb(46, 125, 50); font-size: 2rem"></i>
          </div>
          <div class="ms-3">
            <h5 class="mb-0 fw-semibold text">{{ headerTitle }}</h5>
            <small class="text-50 d-none d-sm-inline">{{ headerSubtitle }}</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="bg-light bg-opacity-90 px-3 py-2 rounded-3 shadow-sm">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-clock" style="color: rgb(46, 125, 50)"></i>
              <span class="fw-semibold" style="color: rgb(46, 125, 50)">{{ currentDateTime }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== BODY ===== -->
    <div class="card-body" style="position: relative; min-height: 300px">
      <!-- Notification Toast -->
      <div v-if="notification.show" :class="['notification-toast', notification.type]">
        <div class="notification-content">
          <i :class="notification.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'"></i>
          <span>{{ notification.message }}</span>
        </div>
        <button @click="notification.show = false" class="notification-close">
          <i class="bi bi-x"></i>
        </button>
      </div>

      <!-- Vertical Tabs Design -->
      <div class="tabs-vertical-container">
        <div class="tabs-vertical-nav">
          <button class="tab-vertical-button" :class="{ active: activeTab === 'in-progress' }" @click="switchTab('in-progress')">
            <div class="tab-vertical-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" :stroke="activeTab === 'in-progress' ? '#ffffff' : '#059669'" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">IN PROGRESS</span>
              <span class="tab-vertical-count">{{ inProgress.total || 0 }}</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>

          <button class="tab-vertical-button" :class="{ active: activeTab === 'for-release' }" @click="switchTab('for-release')">
            <div class="tab-vertical-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" :stroke="activeTab === 'for-release' ? '#ffffff' : '#059669'" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="12" y1="12" x2="12" y2="18" />
                <polyline points="9 15 12 18 15 15" />
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">FOR RELEASE</span>
              <span class="tab-vertical-count">{{ forReleaseDocuments.length }}</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>

          <button class="tab-vertical-button" :class="{ active: activeTab === 'released' }" @click="switchTab('released')">
            <div class="tab-vertical-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" :stroke="activeTab === 'released' ? '#ffffff' : '#059669'" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">RELEASED</span>
              <span class="tab-vertical-count">{{ releasedDocuments.length }}</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>
        </div>

        <div class="tabs-vertical-body">
          <!-- ==================== IN PROGRESS TAB ==================== -->
          <div v-show="activeTab === 'in-progress'">
            <div class="filter-controls">
              <div class="search-filter-row">
                <div class="search-box-wrapper">
                  <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" v-model="inProgressSearch" @input="applyInProgressFilters" class="search-input" placeholder="Search by tracking no, subject, sender..." />
                    <button v-if="inProgressSearch" @click="clearInProgressSearch" class="search-clear-btn">
                      <i class="bi bi-x-circle"></i>
                    </button>
                  </div>
                </div>
                <div class="per-page-wrapper">
                  <span class="per-page-label">Show</span>
                  <select v-model="inProgressPerPage" @change="changeInProgressPerPage" class="per-page-select">
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                  </select>
                  <span class="per-page-label">entries</span>
                </div>
                <div class="filter-wrapper">
                  <div class="filter-box">
                    <i class="bi bi-funnel filter-icon"></i>
                    <select v-model="inProgressDocTypeFilter" @change="applyInProgressFilters" class="filter-select">
                      <option value="">All Document Types</option>
                      <option v-for="type in documentTypes" :key="type" :value="type">{{ type }}</option>
                    </select>
                  </div>
                </div>

                <div class="dropdown">
                  <button class="btn-create-document dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                      <line x1="12" y1="5" x2="12" y2="19" />
                      <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    My Document
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li>
                      <a class="dropdown-item py-2" href="javascript:void(0)" @click="openMyTrackingModal">
                        <i class="bi bi-bookmark-check me-2 text-success"></i>My Tracking
                      </a>
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li>
                      <a class="dropdown-item py-2" href="javascript:void(0)" @click="openCreateModal">
                        <i class="bi bi-plus-circle me-2 text-success"></i>Create New Tracking
                      </a>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="active-filters" v-if="inProgressSearch || inProgressDocTypeFilter">
                <span class="active-filters-label">Active Filters:</span>
                <span v-if="inProgressSearch" class="filter-tag">
                  <i class="bi bi-search"></i> "{{ inProgressSearch }}"
                  <button @click="clearInProgressSearch" class="filter-tag-close"><i class="bi bi-x"></i></button>
                </span>
                <span v-if="inProgressDocTypeFilter" class="filter-tag">
                  <i class="bi bi-funnel"></i> {{ inProgressDocTypeFilter }}
                  <button @click="clearInProgressDocTypeFilter" class="filter-tag-close"><i class="bi bi-x"></i></button>
                </span>
                <button @click="clearAllInProgressFilters" class="clear-all-filters">Clear All</button>
              </div>

              <div class="results-summary">
                <span class="results-count">{{ inProgress.total }}</span> document(s) found in <strong>In Progress</strong>
              </div>
            </div>

            <div class="table-responsive">
              <table class="office-table">
                <thead>
                  <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 15%">Tracking No.</th>
                    <th style="width: 15%">Document Classification</th>
                    <th style="width: 18%">Document Type</th>
                    <th style="width: 22%">Subject/Title</th>
                    <th style="width: 15%">Sender/Origin</th>
                    <th style="width: 15%">Date Received</th>
                    <th style="width: 10%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="inProgressLoading && inProgress.data.length === 0">
                    <td colspan="8" class="text-center">
                      <div class="loader-spinner"></div>
                      Loading...
                    </td>
                  </tr>
                  <tr v-else-if="!inProgressLoading && inProgress.data.length === 0">
                    <td colspan="8" class="text-center">
                      <div class="empty-state">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #9ca3af"></i>
                        <p class="mt-2 text-muted">
                          {{ inProgressSearch || inProgressDocTypeFilter ? "No documents match your filters" : "No In Progress Data Found" }}
                        </p>
                      </div>
                    </td>
                  </tr>
                  <tr v-for="(progress, index) in inProgress.data" :key="progress.id">
                    <td class="text-center">
                      <span class="row-number">{{ (inProgress.current_page - 1) * inProgress.per_page + index + 1 }}</span>
                    </td>
                    <td><span class="tracking-number">{{ progress.tracking_number }}</span></td>
                    <td>{{ progress.document_classification }}</td>
                    <td><span class="doc-type-badge">{{ progress.document_type?.document_type_name || progress.document_type }}</span></td>
                    <td><div class="subject-text">{{ progress.subject }}</div></td>
                    <td><div class="sender-text"><i class="bi bi-person-circle sender-icon"></i> {{ progress.sender_name }}</div></td>
                    <td>
                      <div class="date-received">
                        <i class="bi bi-calendar3 date-icon"></i>{{ formatDate(progress.date_receive) }} At {{ formatTime(progress.time_receive) }}
                      </div>
                    </td>
                    <td>
                      <div class="action-buttons">
                        <button class="btn-action btn-view" @click="viewDocument(progress)" title="View Details"><i class="bi bi-eye"></i></button>
                        <button class="btn-action btn-download" @click="downloadDocument(progress)" title="Download"><i class="bi bi-download"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Reusable Pagination Component -->
            <PaginationComponent
              :current-page="Number(inProgress.current_page)"
              :total-pages="Number(inProgress.last_page)"
              :total="Number(inProgress.total)"
              :per-page="Number(inProgress.per_page)"
              :from="Number(inProgress.from)"
              :to="Number(inProgress.to)"
              @page-change="changeInProgressPage"
            />
          </div>

          <!-- ==================== FOR RELEASE TAB ==================== -->
          <div v-show="activeTab === 'for-release'">
            <div class="filter-controls">
              <div class="search-filter-row">
                <div class="search-box-wrapper">
                  <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" v-model="forReleaseSearch" @input="forReleaseCurrentPage = 1" class="search-input" placeholder="Search by tracking no, subject, sender..." />
                    <button v-if="forReleaseSearch" @click="forReleaseSearch = ''" class="search-clear-btn"><i class="bi bi-x-circle"></i></button>
                  </div>
                </div>
                <div class="per-page-wrapper">
                  <span class="per-page-label">Show</span>
                  <select v-model="forReleasePerPage" @change="forReleaseCurrentPage = 1" class="per-page-select">
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                  </select>
                  <span class="per-page-label">entries</span>
                </div>
                <div class="filter-wrapper">
                  <div class="filter-box">
                    <i class="bi bi-funnel filter-icon"></i>
                    <select v-model="forReleaseDocTypeFilter" @change="forReleaseCurrentPage = 1" class="filter-select">
                      <option value="">All Document Types</option>
                      <option v-for="type in documentTypes" :key="type" :value="type">{{ type }}</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="active-filters" v-if="forReleaseSearch || forReleaseDocTypeFilter">
                <span class="active-filters-label">Active Filters:</span>
                <span v-if="forReleaseSearch" class="filter-tag"><i class="bi bi-search"></i> "{{ forReleaseSearch }}"<button @click="forReleaseSearch = ''" class="filter-tag-close"><i class="bi bi-x"></i></button></span>
                <span v-if="forReleaseDocTypeFilter" class="filter-tag"><i class="bi bi-funnel"></i> {{ forReleaseDocTypeFilter }}<button @click="forReleaseDocTypeFilter = ''" class="filter-tag-close"><i class="bi bi-x"></i></button></span>
                <button @click="forReleaseSearch = ''; forReleaseDocTypeFilter = ''" class="clear-all-filters">Clear All</button>
              </div>
              <div class="results-summary">
                <span class="results-count">{{ filteredForRelease.length }}</span> document(s) found in <strong>For Release</strong>
              </div>
            </div>
            <div class="table-responsive">
              <table class="office-table">
                <thead>
                  <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 15%">Tracking No.</th>
                    <th style="width: 18%">Document Type</th>
                    <th style="width: 22%">Subject/Title</th>
                    <th style="width: 15%">Sender/Origin</th>
                    <th style="width: 15%">Date Received</th>
                    <th style="width: 10%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="forReleaseLoading && forReleaseDocuments.length === 0">
                    <td colspan="7" class="text-center py-5">
                      <div class="loader-spinner"></div>
                      <div class="mt-2">Loading documents...</div>
                    </td>
                  </tr>
                  <tr v-else-if="!forReleaseLoading && filteredForRelease.length === 0">
                    <td colspan="7" class="text-center py-5">
                      <div class="empty-state">
                        <i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #9ca3af"></i>
                        <p class="mt-2 text-muted">{{ forReleaseSearch || forReleaseDocTypeFilter ? "No documents match your filters" : "No For Release Documents" }}</p>
                      </div>
                    </td>
                  </tr>
                  <tr v-for="(doc, index) in paginatedForRelease" :key="doc.id">
                    <td class="text-center"><span class="row-number">{{ (forReleaseCurrentPage - 1) * forReleasePerPage + index + 1 }}</span></td>
                    <td><span class="tracking-number">{{ doc.tracking_number }}</span></td>
                    <td><span class="doc-type-badge">{{ doc.document_type }}</span></td>
                    <td><div class="subject-text">{{ doc.subject || doc.title }}</div></td>
                    <td><div class="sender-text"><i class="bi bi-person-circle sender-icon"></i>{{ doc.sender_name || doc.origin }}</div></td>
                    <td><div class="date-received"><i class="bi bi-calendar3 date-icon"></i>{{ formatDate(doc.date_received || doc.created_at) }}</div></td>
                    <td>
                      <div class="action-buttons">
                        <button class="btn-action btn-view" @click="viewDocument(doc)" title="View Details"><i class="bi bi-eye"></i></button>
                        <button class="btn-action btn-release" @click="releaseDocument(doc)" title="Release Document"><i class="bi bi-send-check"></i></button>
                        <button class="btn-action btn-download" @click="downloadDocument(doc)" title="Download"><i class="bi bi-download"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Reusable Pagination Component -->
            <PaginationComponent
              :current-page="forReleaseCurrentPage"
              :total-pages="forReleaseTotalPages"
              :total="filteredForRelease.length"
              :per-page="forReleasePerPage"
              :from="forReleaseStartItem"
              :to="forReleaseEndItem"
              @page-change="forReleaseCurrentPage = $event"
            />
          </div>

          <!-- ==================== RELEASED TAB ==================== -->
          <div v-show="activeTab === 'released'">
            <div class="filter-controls">
              <div class="search-filter-row">
                <div class="search-box-wrapper">
                  <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" v-model="releasedSearch" @input="releasedCurrentPage = 1" class="search-input" placeholder="Search by tracking no, subject, sender..." />
                    <button v-if="releasedSearch" @click="releasedSearch = ''" class="search-clear-btn"><i class="bi bi-x-circle"></i></button>
                  </div>
                </div>
                <div class="per-page-wrapper">
                  <span class="per-page-label">Show</span>
                  <select v-model="releasedPerPage" @change="releasedCurrentPage = 1" class="per-page-select">
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                  </select>
                  <span class="per-page-label">entries</span>
                </div>
                <div class="filter-wrapper">
                  <div class="filter-box">
                    <i class="bi bi-funnel filter-icon"></i>
                    <select v-model="releasedDocTypeFilter" @change="releasedCurrentPage = 1" class="filter-select">
                      <option value="">All Document Types</option>
                      <option v-for="type in documentTypes" :key="type" :value="type">{{ type }}</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="active-filters" v-if="releasedSearch || releasedDocTypeFilter">
                <span class="active-filters-label">Active Filters:</span>
                <span v-if="releasedSearch" class="filter-tag"><i class="bi bi-search"></i> "{{ releasedSearch }}"<button @click="releasedSearch = ''" class="filter-tag-close"><i class="bi bi-x"></i></button></span>
                <span v-if="releasedDocTypeFilter" class="filter-tag"><i class="bi bi-funnel"></i> {{ releasedDocTypeFilter }}<button @click="releasedDocTypeFilter = ''" class="filter-tag-close"><i class="bi bi-x"></i></button></span>
                <button @click="releasedSearch = ''; releasedDocTypeFilter = ''" class="clear-all-filters">Clear All</button>
              </div>
              <div class="results-summary">
                <span class="results-count">{{ filteredReleased.length }}</span> document(s) found in <strong>Released</strong>
              </div>
            </div>
            <div class="table-responsive">
              <table class="office-table">
                <thead>
                  <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 15%">Tracking No.</th>
                    <th style="width: 18%">Document Type</th>
                    <th style="width: 22%">Subject/Title</th>
                    <th style="width: 15%">Sender/Origin</th>
                    <th style="width: 15%">Date Received</th>
                    <th style="width: 10%">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="releasedLoading && releasedDocuments.length === 0">
                    <td colspan="7" class="text-center py-5">
                      <div class="loader-spinner"></div>
                      <div class="mt-2">Loading documents...</div>
                    </td>
                  </tr>
                  <tr v-else-if="!releasedLoading && filteredReleased.length === 0">
                    <td colspan="7" class="text-center py-5">
                      <div class="empty-state">
                        <i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #9ca3af"></i>
                        <p class="mt-2 text-muted">{{ releasedSearch || releasedDocTypeFilter ? "No documents match your filters" : "No Released Documents" }}</p>
                      </div>
                    </td>
                  </tr>
                  <tr v-for="(doc, index) in paginatedReleased" :key="doc.id">
                    <td class="text-center"><span class="row-number">{{ (releasedCurrentPage - 1) * releasedPerPage + index + 1 }}</span></td>
                    <td><span class="tracking-number">{{ doc.tracking_number }}</span></td>
                    <td><span class="doc-type-badge">{{ doc.document_type }}</span></td>
                    <td><div class="subject-text">{{ doc.subject || doc.title }}</div></td>
                    <td><div class="sender-text"><i class="bi bi-person-circle sender-icon"></i>{{ doc.sender_name || doc.origin }}</div></td>
                    <td><div class="date-received"><i class="bi bi-calendar3 date-icon"></i>{{ formatDate(doc.date_received || doc.created_at) }}</div></td>
                    <td>
                      <div class="action-buttons">
                        <button class="btn-action btn-view" @click="viewDocument(doc)" title="View Details"><i class="bi bi-eye"></i></button>
                        <button class="btn-action btn-download" @click="downloadDocument(doc)" title="Download"><i class="bi bi-download"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Reusable Pagination Component -->
            <PaginationComponent
              :current-page="releasedCurrentPage"
              :total-pages="releasedTotalPages"
              :total="filteredReleased.length"
              :per-page="releasedPerPage"
              :from="releasedStartItem"
              :to="releasedEndItem"
              @page-change="releasedCurrentPage = $event"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- ================= MY TRACKING MODAL ================= -->
    <div v-if="showMyTrackingModal" class="modal-overlay" @click.self="closeMyTrackingModal">
      <div class="modal-dialog enhanced-modal tracking-modal" style="max-width: 1500px">
        <div class="modal-content square-modal">
          <div class="modal-header-enhanced square-header" style="background: #2d6a4f">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon">
                <i class="bi bi-bookmark-check"></i>
              </div>
              <div>
                <h5 class="modal-title">My Tracking</h5>
                <small class="modal-subtitle">Documents assigned to your tracking</small>
              </div>
            </div>
            <button type="button" class="btn-close-custom square-close" @click="closeMyTrackingModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body-enhanced tracking-modal-body">
            <div class="my-tracking-controls sticky-top-controls">
              <div class="search-box-wrapper" style="flex: 1; max-width: 800px">
                <div class="search-box">
                  <i class="bi bi-search search-icon"></i>
                  <input type="text" v-model="myTrackingSearch" @input="applyTrackingFilters" class="search-input" placeholder="Search tracking documents..." />
                  <button v-if="myTrackingSearch" @click="clearTrackingSearch" class="search-clear-btn"><i class="bi bi-x-circle"></i></button>
                </div>
              </div>
              <button type="button" class="btn-reserve-tracking-forest" @click="reserveTracking" :disabled="reserving">
                <span v-if="reserving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1">
                  <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                </svg>
                {{ reserving ? "Reserving..." : "Reserve Tracking" }}
              </button>
            </div>

            <div class="tracking-table-scroll">
              <div class="table-responsive">
                <table class="office-table">
                  <thead>
                    <tr>
                      <th style="width: 5%">#</th>
                      <th style="width: 15%">Tracking No.</th>
                      <th style="width: 10%">Status</th>
                      <th style="width: 10%">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="trackingLoading && trackings.data.length === 0">
                      <td colspan="4" class="text-center">
                        <div class="loader-spinner"></div>
                        Loading...
                      </td>
                    </tr>
                    <tr v-else-if="!trackingLoading && trackings.data.length === 0">
                      <td colspan="4" class="text-center">
                        <div class="empty-state">
                          <i class="bi bi-inbox" style="font-size: 3rem; color: #9ca3af"></i>
                          <p class="mt-2 text-muted">No Reserve Tracking Found</p>
                        </div>
                      </td>
                    </tr>
                    <tr v-for="(tracking, index) in trackings.data" :key="tracking.id">
                      <td class="text-center">
                        <span class="row-number">{{ (trackings.current_page - 1) * trackings.per_page + index + 1 }}</span>
                      </td>
                      <td><span class="tracking-number">{{ tracking.tracking_number }}</span></td>
                      <td><span :class="['status-badge', getStatusClass(tracking.status)]">{{ tracking.status }}</span></td>
                      <td>
                        <a :href="`/dts_denr/create-incoming/${tracking.id}`" class="btn-action btn-view" title="View Details">
                          <i class="bi bi-plus-circle me-1"></i> Create
                        </a>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Reusable Pagination Component for Tracking -->
            <div class="tracking-pagination">
              <PaginationComponent
                :current-page="trackings.current_page"
                :total-pages="trackings.last_page"
                :total="trackings.total"
                :per-page="trackings.per_page"
                :from="trackings.from"
                :to="trackings.to"
                @page-change="changeTrackingPage"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= CREATE DOCUMENT MODAL ================= -->
    <div v-if="showCreateModal" class="modal-overlay" @click.self="closeCreateModal">
      <div class="modal-dialog enhanced-modal">
        <div class="modal-content square-modal">
          <div class="modal-header-enhanced square-header">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon">
                <i class="bi bi-file-earmark-plus"></i>
              </div>
              <div>
                <h5 class="modal-title">Create Incoming Document</h5>
                <small class="modal-subtitle">Register a new incoming document record</small>
              </div>
            </div>
            <button type="button" class="btn-close-custom square-close" @click="closeCreateModal" :disabled="creating">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body-enhanced">
            <div v-if="formErrors.show" class="error-msg" role="alert">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none" />
              </svg>
              <span>{{ formErrors.message }}</span>
            </div>
            <form @submit.prevent="submitCreateForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="tracking_number" class="form-label-enhanced">Tracking Number <span class="required-star">*</span></label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                        <line x1="7" y1="7" x2="7.01" y2="7" />
                      </svg>
                    </span>
                    <input type="text" id="tracking_number" v-model="createForm.tracking_number" class="form-input" :class="{ 'is-invalid': errors.tracking_number }" placeholder="e.g., TRK-2024-001" :disabled="creating" />
                  </div>
                  <div v-if="errors.tracking_number" class="invalid-feedback d-block">{{ errors.tracking_number }}</div>
                </div>
                <div class="col-md-6">
                  <label for="document_type" class="form-label-enhanced">Document Type <span class="required-star">*</span></label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                      </svg>
                    </span>
                    <select id="document_type" v-model="createForm.document_type" class="form-input" :class="{ 'is-invalid': errors.document_type }" :disabled="creating" style="padding-left: 42px">
                      <option value="">Select Document Type</option>
                      <option v-for="type in documentTypes" :key="type" :value="type">{{ type }}</option>
                    </select>
                  </div>
                  <div v-if="errors.document_type" class="invalid-feedback d-block">{{ errors.document_type }}</div>
                </div>
              </div>
              <div class="mt-3">
                <label for="subject" class="form-label-enhanced">Subject/Title <span class="required-star">*</span></label>
                <div class="input-wrap">
                  <span class="input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                      <polyline points="14 2 14 8 20 8" />
                      <line x1="16" y1="13" x2="8" y2="13" />
                      <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                  </span>
                  <input type="text" id="subject" v-model="createForm.subject" class="form-input" :class="{ 'is-invalid': errors.subject }" placeholder="Enter document subject or title" :disabled="creating" />
                </div>
                <div v-if="errors.subject" class="invalid-feedback d-block">{{ errors.subject }}</div>
              </div>
              <div class="row g-3 mt-3">
                <div class="col-md-6">
                  <label for="sender_name" class="form-label-enhanced">Sender/Origin <span class="required-star">*</span></label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                      </svg>
                    </span>
                    <input type="text" id="sender_name" v-model="createForm.sender_name" class="form-input" :class="{ 'is-invalid': errors.sender_name }" placeholder="Name of sender or origin office" :disabled="creating" />
                  </div>
                  <div v-if="errors.sender_name" class="invalid-feedback d-block">{{ errors.sender_name }}</div>
                </div>
                <div class="col-md-6">
                  <label for="date_received" class="form-label-enhanced">Date Received <span class="required-star">*</span></label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                      </svg>
                    </span>
                    <input type="datetime-local" id="date_received" v-model="createForm.date_received" class="form-input" :class="{ 'is-invalid': errors.date_received }" :disabled="creating" />
                  </div>
                  <div v-if="errors.date_received" class="invalid-feedback d-block">{{ errors.date_received }}</div>
                </div>
              </div>
              <div class="mt-3">
                <label for="description" class="form-label-enhanced">Description</label>
                <div class="input-wrap">
                  <span class="input-icon" style="top: 14px">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                      <polyline points="14 2 14 8 20 8" />
                      <line x1="16" y1="13" x2="8" y2="13" />
                      <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                  </span>
                  <textarea id="description" v-model="createForm.description" class="form-input form-textarea" rows="3" placeholder="Brief description of the document" :disabled="creating"></textarea>
                </div>
              </div>
              <div class="modal-actions">
                <button type="button" class="btn btn-outline-secondary square-btn" @click="resetCreateForm" :disabled="creating">
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </button>
                <div class="d-flex gap-3">
                  <button type="button" class="btn btn-light square-btn" @click="closeCreateModal" :disabled="creating">Cancel</button>
                  <button type="submit" class="btn btn-save square-btn" :disabled="creating">
                    <span v-if="creating" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    <i v-else class="bi bi-check2-circle me-1"></i>
                    {{ creating ? "Creating..." : "Create Document" }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= ENHANCED DOCUMENT VIEWER MODAL ================= -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="closeViewModal">
      <div class="modal-dialog enhanced-modal document-view-modal">
        <div class="modal-content square-modal">
          <div class="modal-header-enhanced square-header document-header">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon document-icon">
                <i class="bi bi-file-earmark-pdf"></i>
              </div>
              <div>
                <h5 class="modal-title">Document Viewer</h5>
                <small class="modal-subtitle">
                  <span class="tracking-badge">{{ selectedDocument?.tracking_number }}</span>
                  <span :class="['status-pill', getStatusClass(selectedDocument?.status)]">{{ selectedDocument?.status }}</span>
                </small>
              </div>
            </div>
            <div class="header-actions">
              <a class="btn-header-action btn-header-update" :href="`/dts_denr/view-document/${selectedDocument?.token}`" title="Update Document">
                <i class="bi bi-pencil-square"></i>
              </a>
              <button type="button" class="btn-close-custom square-close" @click="closeViewModal">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>

          <div class="modal-body-enhanced document-viewer-body">
            <!-- Document Viewer Tabs -->
            <div class="document-viewer-tabs">
              <button 
                class="viewer-tab-btn" 
                :class="{ active: viewerActiveTab === 'details' }" 
                @click="viewerActiveTab = 'details'"
              >
                <i class="bi bi-info-circle-fill"></i>
                <span>Document Information</span>
              </button>
              <button 
                class="viewer-tab-btn" 
                :class="{ active: viewerActiveTab === 'history' }" 
                @click="switchToHistoryTab"
              >
                <i class="bi bi-clock-history"></i>
                <span>Route History</span>
              </button>
            </div>

            <!-- Document Information Tab - Shows Details + PDF side by side -->
            <div v-show="viewerActiveTab === 'details'" class="document-viewer-layout">
              <!-- Left Panel: Document Details -->
              <div class="details-panel">
                <div class="details-panel-header">
                  <i class="bi bi-info-circle-fill"></i>
                  <span>Document Information</span>
                </div>
                
                <div class="details-content" v-if="selectedDocument">
                  <div class="detail-card">
                    <div class="detail-icon-wrapper"><i class="bi bi-upc-scan"></i></div>
                    <div class="detail-info">
                      <label>Tracking Number</label>
                      <span class="tracking-number-large">{{ selectedDocument.tracking_number }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper type-icon"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="detail-info">
                      <label>Document Type</label>
                      <span class="doc-type-badge-large">{{ selectedDocument.document_type?.document_type_name || selectedDocument.document_type }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper subject-icon"><i class="bi bi-journal-text"></i></div>
                    <div class="detail-info">
                      <label>Subject / Title</label>
                      <span class="detail-value">{{ selectedDocument.subject || selectedDocument.title }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper sender-icon-card"><i class="bi bi-person-badge"></i></div>
                    <div class="detail-info">
                      <label>Sender / Origin</label>
                      <span class="detail-value">{{ selectedDocument.sender_name || selectedDocument.origin }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper date-icon-card"><i class="bi bi-calendar-check"></i></div>
                    <div class="detail-info">
                      <label>Date Received</label>
                      <span class="detail-value">
                        {{ formatDate(selectedDocument.date_receive || selectedDocument.date_received || selectedDocument.created_at) }}
                        <small v-if="selectedDocument.time_receive" class="time-text">at {{ formatTime(selectedDocument.time_receive) }}</small>
                      </span>
                    </div>
                  </div>

                  <div class="detail-card description-card" v-if="selectedDocument.description">
                    <div class="detail-icon-wrapper desc-icon"><i class="bi bi-blockquote-right"></i></div>
                    <div class="detail-info">
                      <label>Description</label>
                      <p class="detail-value description-text">{{ selectedDocument.description }}</p>
                    </div>
                  </div>

                  <div class="detail-card meta-card">
                    <div class="detail-icon-wrapper meta-icon"><i class="bi bi-info-circle"></i></div>
                    <div class="detail-info">
                      <label>Status</label>
                      <span :class="['status-badge', getStatusClass(selectedDocument?.status)]">{{ selectedDocument?.status || 'Unknown' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Panel: PDF Preview -->
              <div class="pdf-panel">
                <div class="pdf-panel-header">
                  <div class="pdf-panel-title">
                    <i class="bi bi-file-pdf"></i>
                    <span>Document Preview</span>
                  </div>
                  <div class="pdf-controls">
                    <button class="btn-pdf-control" @click="zoomIn" title="Zoom In" :disabled="pdfLoadError"><i class="bi bi-zoom-in"></i></button>
                    <button class="btn-pdf-control" @click="zoomOut" title="Zoom Out" :disabled="pdfLoadError"><i class="bi bi-zoom-out"></i></button>
                  </div>
                </div>

                <div class="pdf-viewer-wrapper">
                  <div v-if="pdfLoading && !pdfLoadError" class="pdf-state">
                    <div class="pdf-loader-animation">
                      <div class="pdf-loader-icon"><i class="bi bi-file-pdf"></i></div>
                      <div class="loader-spinner"></div>
                    </div>
                    <p class="pdf-state-text">Loading document preview...</p>
                  </div>

                  <iframe v-show="!pdfLoading && !pdfLoadError" :src="getPdfUrl(selectedDocument)" class="pdf-iframe" :style="{ width: `${100 / pdfZoom}%`, height: `${pdfViewerHeight}px` }" frameborder="0" @load="onPdfLoaded" @error="handlePdfError"></iframe>

                  <div v-if="pdfLoadError" class="pdf-state pdf-error">
                    <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #ef4444;"></i>
                    <h5 class="mt-3">PDF Not Available</h5>
                    <p class="text-muted">The attachment could not be loaded or doesn't exist.</p>
                    <div class="error-details"><code>{{ getPdfPath(selectedDocument) }}</code></div>
                    <button class="btn btn-outline-secondary btn-sm mt-3" @click="retryPdfLoad"><i class="bi bi-arrow-repeat me-1"></i> Retry</button>
                  </div>
                </div>

                <div class="pdf-footer" v-if="!pdfLoadError">
                  <span class="pdf-zoom-level">Zoom: {{ Math.round(pdfZoom * 100) }}%</span>
                  <span class="pdf-page-info" v-if="selectedDocument">File: {{ selectedDocument.tracking_number }}_attachment.pdf</span>
                </div>
              </div>
            </div>

            <!-- Route History Tab -->
            <div v-show="viewerActiveTab === 'history'" class="route-history-panel">
              <div class="route-history-header">
                <div class="route-history-title">
                  <i class="bi bi-clock-history"></i>
                  <span>Document Route History</span>
                </div>
                <div class="route-history-badge" v-if="routeHistory.length > 0">
                  <i class="bi bi-arrow-left-right"></i>
                  <span>{{ routeHistory.length }} Route{{ routeHistory.length !== 1 ? 's' : '' }}</span>
                </div>
              </div>

              <div class="route-history-content">
                <!-- Loading State -->
                <div v-if="routeHistoryLoading" class="route-loading-state">
                  <div class="loader-spinner"></div>
                  <p class="mt-3 text-muted">Loading route history...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="routeHistory.length === 0" class="route-empty-state">
                  <div class="empty-icon-wrapper">
                    <i class="bi bi-signpost-split"></i>
                  </div>
                  <h5 class="mt-3">No Route History</h5>
                  <p class="text-muted">This document hasn't been routed yet.</p>
                </div>

                <!-- Timeline -->
                <div v-else class="route-timeline">
                  <div 
                    v-for="(route, index) in routeHistory" 
                    :key="route.id || index" 
                    class="timeline-item"
                    :class="{ 
                      'timeline-first': index === 0,
                      'timeline-last': index === routeHistory.length - 1,
                      'timeline-current': route.is_current
                    }"
                  >
                    <div class="timeline-marker">
                      <div class="marker-dot" :class="getRouteMarkerClass(route)">
                        <i :class="getRouteIcon(route)"></i>
                      </div>
                      <div class="marker-line" v-if="index < routeHistory.length - 1"></div>
                    </div>
                    
                    <div class="timeline-card" :class="{ 'current-card': route.is_current }">
                      <div class="timeline-card-header">
                        <div class="timeline-step-info">
                          <span class="step-number">Step {{ index + 1 }}</span>
                          <span :class="['route-status-badge', getRouteStatusClass(route.status)]">
                            {{ route.status || 'Processed' }}
                          </span>
                        </div>
                        <div class="timeline-date">
                          <i class="bi bi-calendar3"></i>
                          <span>{{ formatDateTime(route.routed_at || route.created_at) }}</span>
                        </div>
                      </div>
                      
                      <div class="timeline-card-body">
                        <div class="route-detail-row">
                          <div class="route-detail-item">
                            <div class="detail-icon-small from-icon">
                              <i class="bi bi-box-arrow-right"></i>
                            </div>
                            <div class="detail-text">
                              <label>From Office</label>
                              <span class="office-name">{{ route.from_office || route.office_origin || 'N/A' }}</span>
                            </div>
                          </div>
                          <div class="route-arrow">
                            <i class="bi bi-arrow-right"></i>
                          </div>
                          <div class="route-detail-item">
                            <div class="detail-icon-small to-icon">
                              <i class="bi bi-box-arrow-in-left"></i>
                            </div>
                            <div class="detail-text">
                              <label>To Office</label>
                              <span class="office-name">{{ route.to_office || route.office_destination || 'N/A' }}</span>
                            </div>
                          </div>
                        </div>
                        
                        <div class="route-meta-row" v-if="route.action_by || route.processed_by">
                          <div class="route-meta-item">
                            <i class="bi bi-person-check"></i>
                            <span>{{ route.action_by || route.processed_by }}</span>
                          </div>
                          <div class="route-meta-item" v-if="route.remarks || route.notes">
                            <i class="bi bi-chat-left-text"></i>
                            <span>{{ route.remarks || route.notes }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import PaginationComponent from '../../components/PaginationComponent.vue';

export default {
  name: "IncomingDocumentsList",
  components: {
    PaginationComponent
  },
  props: {
    headerIcon: { type: String, default: "bi bi-inbox" },
    headerTitle: { type: String, default: "Incoming Documents" },
    headerSubtitle: { type: String, default: "Track and manage incoming document workflow" },
  },
  emits: [
    "process-document",
    "view-document",
    "download-document",
    "create-document",
    "tracking-reserved",
  ],
  data() {
    return {
      activeTab: "in-progress",
      viewerActiveTab: "details",
      currentDateTime: "",
      dateTimeInterval: null,
      showCreateModal: false,
      showViewModal: false,
      showMyTrackingModal: false,
      selectedDocument: null,
      creating: false,
      reserving: false,
      documentTypes: ["Memorandum", "Letter", "Report", "Request", "Permit", "Certificate"],

      pdfLoading: true,
      pdfLoadError: false,
      pdfZoom: 1,
      pdfViewerHeight: 700,

      // Route History Data
      routeHistory: [],
      routeHistoryLoading: false,

      // IN-PROGRESS TAB DATA
      inProgress: {
        data: [],
        current_page: 1,
        from: 1,
        to: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      },
      inProgressSearch: "",
      inProgressDocTypeFilter: "",
      inProgressPerPage: 10,
      inProgressLoading: false,

      // FOR-RELEASE TAB DATA
      forReleaseDocuments: [],
      forReleaseSearch: "",
      forReleaseDocTypeFilter: "",
      forReleasePerPage: 10,
      forReleaseCurrentPage: 1,
      forReleaseLoading: false,

      // RELEASED TAB DATA
      releasedDocuments: [],
      releasedSearch: "",
      releasedDocTypeFilter: "",
      releasedPerPage: 10,
      releasedCurrentPage: 1,
      releasedLoading: false,

      // TRACKING MODAL DATA
      trackings: {
        data: [],
        current_page: 1,
        from: 1,
        to: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      },
      myTrackingSearch: "",
      trackingLoading: false,

      // CREATE FORM
      createForm: {
        tracking_number: "",
        document_type: "",
        subject: "",
        sender_name: "",
        date_received: "",
        description: "",
      },
      errors: {},
      formErrors: { show: false, message: "" },
      notification: {
        show: false,
        message: "",
        type: "success",
        timeout: null,
      },
    };
  },
  computed: {
    filteredForRelease() {
      let filtered = [...this.forReleaseDocuments];
      if (this.forReleaseDocTypeFilter) {
        filtered = filtered.filter(doc => doc.document_type === this.forReleaseDocTypeFilter);
      }
      if (this.forReleaseSearch) {
        const query = this.forReleaseSearch.toLowerCase();
        filtered = filtered.filter(doc =>
          doc.tracking_number?.toLowerCase().includes(query) ||
          doc.document_type?.toLowerCase().includes(query) ||
          doc.subject?.toLowerCase().includes(query) ||
          doc.sender_name?.toLowerCase().includes(query)
        );
      }
      return filtered;
    },
    paginatedForRelease() {
      const start = (this.forReleaseCurrentPage - 1) * this.forReleasePerPage;
      return this.filteredForRelease.slice(start, start + this.forReleasePerPage);
    },
    forReleaseTotalPages() {
      return Math.ceil(this.filteredForRelease.length / this.forReleasePerPage) || 1;
    },
    forReleaseStartItem() {
      return this.filteredForRelease.length === 0 ? 0 : (this.forReleaseCurrentPage - 1) * this.forReleasePerPage + 1;
    },
    forReleaseEndItem() {
      return Math.min(this.forReleaseCurrentPage * this.forReleasePerPage, this.filteredForRelease.length);
    },

    filteredReleased() {
      let filtered = [...this.releasedDocuments];
      if (this.releasedDocTypeFilter) {
        filtered = filtered.filter(doc => doc.document_type === this.releasedDocTypeFilter);
      }
      if (this.releasedSearch) {
        const query = this.releasedSearch.toLowerCase();
        filtered = filtered.filter(doc =>
          doc.tracking_number?.toLowerCase().includes(query) ||
          doc.document_type?.toLowerCase().includes(query) ||
          doc.subject?.toLowerCase().includes(query) ||
          doc.sender_name?.toLowerCase().includes(query)
        );
      }
      return filtered;
    },
    paginatedReleased() {
      const start = (this.releasedCurrentPage - 1) * this.releasedPerPage;
      return this.filteredReleased.slice(start, start + this.releasedPerPage);
    },
    releasedTotalPages() {
      return Math.ceil(this.filteredReleased.length / this.releasedPerPage) || 1;
    },
    releasedStartItem() {
      return this.filteredReleased.length === 0 ? 0 : (this.releasedCurrentPage - 1) * this.releasedPerPage + 1;
    },
    releasedEndItem() {
      return Math.min(this.releasedCurrentPage * this.releasedPerPage, this.filteredReleased.length);
    },
  },
  mounted() {
    this.getDataInprogress();
    this.getDataTracking();
    this.updateDateTime();
    this.dateTimeInterval = setInterval(() => this.updateDateTime(), 1000);
  },
  beforeUnmount() {
    if (this.dateTimeInterval) clearInterval(this.dateTimeInterval);
    if (this.notification.timeout) clearTimeout(this.notification.timeout);
  },
  methods: {
    // ===== ROUTE HISTORY METHODS =====
    async fetchRouteHistory(documentId) {
      if (!documentId) return;
      
      this.routeHistoryLoading = true;
      this.routeHistory = [];
      
      try {
        // Replace this URL with your actual API endpoint
        const response = await axios.get(`/dts_denr/api/documents/${documentId}/route-history`);
        this.routeHistory = response.data.data || response.data || [];
      } catch (error) {
        console.error("Error fetching route history:", error);
        // Fallback mock data for demonstration
        this.routeHistory = this.getMockRouteHistory();
      } finally {
        this.routeHistoryLoading = false;
      }
    },

    getMockRouteHistory() {
      // Mock data for demonstration - remove this when API is connected
      return [
        {
          id: 1,
          from_office: "Records Office",
          to_office: "Planning Division",
          status: "Completed",
          routed_at: new Date(Date.now() - 86400000 * 3).toISOString(),
          action_by: "Maria Santos",
          remarks: "Forwarded for review",
          is_current: false
        },
        {
          id: 2,
          from_office: "Planning Division",
          to_office: "Legal Department",
          status: "Completed",
          routed_at: new Date(Date.now() - 86400000 * 2).toISOString(),
          action_by: "Juan Dela Cruz",
          remarks: "For legal review and comments",
          is_current: false
        },
        {
          id: 3,
          from_office: "Legal Department",
          to_office: "Executive Office",
          status: "In Progress",
          routed_at: new Date(Date.now() - 86400000).toISOString(),
          action_by: "Atty. Reyes",
          remarks: "For final approval",
          is_current: true
        },
      ];
    },

    switchToHistoryTab() {
      this.viewerActiveTab = 'history';
      if (this.selectedDocument?.id) {
        this.fetchRouteHistory(this.selectedDocument.id);
      }
    },

    getRouteMarkerClass(route) {
      if (route.is_current) return 'marker-current';
      if (route.status === 'Completed' || route.status === 'Approved') return 'marker-completed';
      if (route.status === 'Rejected' || route.status === 'Returned') return 'marker-rejected';
      return 'marker-pending';
    },

    getRouteIcon(route) {
      if (route.is_current) return 'bi bi-arrow-right-circle-fill';
      if (route.status === 'Completed' || route.status === 'Approved') return 'bi bi-check-circle-fill';
      if (route.status === 'Rejected' || route.status === 'Returned') return 'bi bi-x-circle-fill';
      return 'bi bi-circle';
    },

    getRouteStatusClass(status) {
      const map = {
        'Completed': 'route-completed',
        'Approved': 'route-completed',
        'In Progress': 'route-in-progress',
        'Pending': 'route-pending',
        'Rejected': 'route-rejected',
        'Returned': 'route-rejected',
      };
      return map[status] || 'route-pending';
    },

    formatDateTime(dateString) {
      if (!dateString) return "N/A";
      const date = new Date(dateString);
      return date.toLocaleString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      });
    },

    getPdfUrl(document) {
      if (!document || !document.tracking_number) return '';
      if (document.draft_attachment) {
        return `/dts_denr/storage/app/public/${document.draft_attachment}`;
      }
      const trackingNumber = document.tracking_number;
      return `/dts_denr/storage/app/public/attachments/${trackingNumber}/draft_attachment.pdf`;
    },
    
    getPdfPath(document) {
      if (!document || !document.tracking_number) return 'No tracking number';
      const draftAttachment = document.draft_attachment;
      return `dts_denr/storage/app/public/${draftAttachment || 'no-file'}`;
    },
    
    onPdfLoaded() {
      this.pdfLoading = false;
      this.pdfLoadError = false;
    },
    
    handlePdfError() {
      this.pdfLoading = false;
      this.pdfLoadError = true;
    },
    
    retryPdfLoad() {
      this.pdfLoading = true;
      this.pdfLoadError = false;
      this.$nextTick(() => {
        const iframe = document.querySelector('.pdf-iframe');
        if (iframe) iframe.src = iframe.src;
      });
    },
    
    zoomIn() {
      if (this.pdfZoom < 2) {
        this.pdfZoom += 0.25;
        this.adjustViewerHeight();
      }
    },
    
    zoomOut() {
      if (this.pdfZoom > 0.5) {
        this.pdfZoom -= 0.25;
        this.adjustViewerHeight();
      }
    },
    
    adjustViewerHeight() {
      this.pdfViewerHeight = Math.round(700 / this.pdfZoom);
    },

    async getDataInprogress(page = 1) {
      try {
        this.inProgressLoading = true;
        const response = await axios.get("/dts_denr/api/in-progress", {
          params: {
            page: page,
            per_page: this.inProgressPerPage,
            search: this.inProgressSearch,
            document_type: this.inProgressDocTypeFilter,
          },
        });
        this.inProgress = response.data.data;
      } catch (error) {
        console.error("Error fetching in-progress data:", error);
        this.showNotification("Failed to load in-progress data. Please try again.", "error");
      } finally {
        this.inProgressLoading = false;
      }
    },

    applyInProgressFilters() {
      this.getDataInprogress(1);
    },

    clearInProgressSearch() {
      this.inProgressSearch = "";
      this.getDataInprogress(1);
    },

    clearInProgressDocTypeFilter() {
      this.inProgressDocTypeFilter = "";
      this.getDataInprogress(1);
    },

    clearAllInProgressFilters() {
      this.inProgressSearch = "";
      this.inProgressDocTypeFilter = "";
      this.getDataInprogress(1);
    },

    changeInProgressPage(page) {
      if (page >= 1 && page <= this.inProgress.last_page && page !== this.inProgress.current_page) {
        this.getDataInprogress(page);
      }
    },

    changeInProgressPerPage() {
      this.getDataInprogress(1);
    },

    async getDataTracking(page = 1) {
      try {
        this.trackingLoading = true;
        const response = await axios.get("/dts_denr/api/reserve-tracking", {
          params: {
            page: page,
            per_page: this.trackings.per_page,
            search: this.myTrackingSearch,
          },
        });
        this.trackings = response.data.data;
      } catch (error) {
        console.error("Error fetching tracking data:", error);
        this.showNotification("Failed to load tracking data. Please try again.", "error");
      } finally {
        this.trackingLoading = false;
      }
    },

    applyTrackingFilters() {
      this.getDataTracking(1);
    },

    clearTrackingSearch() {
      this.myTrackingSearch = "";
      this.getDataTracking(1);
    },

    changeTrackingPage(page) {
      if (page >= 1 && page <= this.trackings.last_page && page !== this.trackings.current_page) {
        this.getDataTracking(page);
      }
    },

    switchTab(tab) {
      this.activeTab = tab;
    },
    updateDateTime() {
      const now = new Date();
      this.currentDateTime = now.toLocaleString("en-PH", {
        weekday: "short",
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: true,
      });
    },
    formatDate(dateString) {
      if (!dateString) return "N/A";
      return new Date(dateString).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    },
    formatTime(t) {
      if (!t) return "";
      if (typeof t === "string" && (t.toUpperCase().includes("AM") || t.toUpperCase().includes("PM"))) return t;
      const [h, m] = t.split(":").map(Number),
        period = h >= 12 ? "PM" : "AM",
        displayHour = h % 12 || 12;
      return `${displayHour}:${m.toString().padStart(2, "0")} ${period}`;
    },
    getStatusClass(status) {
      const map = {
        "In-Progress": "status-in-progress",
        "For-Release": "status-for-release",
        Released: "status-released",
      };
      return map[status] || "";
    },
    openCreateModal() {
      this.showCreateModal = true;
      this.errors = {};
      this.formErrors = { show: false, message: "" };
    },
    closeCreateModal() {
      this.showCreateModal = false;
    },
    openMyTrackingModal() {
      this.myTrackingSearch = "";
      this.showMyTrackingModal = true;
      this.getDataTracking(1);
    },
    closeMyTrackingModal() {
      this.showMyTrackingModal = false;
      this.myTrackingSearch = "";
    },
    resetCreateForm() {
      this.createForm = {
        tracking_number: "",
        document_type: "",
        subject: "",
        sender_name: "",
        date_received: "",
        description: "",
      };
      this.errors = {};
      this.formErrors = { show: false, message: "" };
    },
    viewDocument(doc) {
      this.selectedDocument = doc;
      this.showViewModal = true;
      this.viewerActiveTab = "details";
      this.pdfLoading = true;
      this.pdfLoadError = false;
      this.pdfZoom = 1;
      this.pdfViewerHeight = 700;
      this.routeHistory = [];
      this.$emit("view-document", doc);
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedDocument = null;
      this.viewerActiveTab = "details";
      this.pdfLoading = true;
      this.pdfLoadError = false;
      this.pdfZoom = 1;
      this.pdfViewerHeight = 700;
      this.routeHistory = [];
    },
    processDocument(doc) {
      this.$emit("process-document", doc);
    },
    releaseDocument(doc) {
      // Implement release logic
    },
    downloadDocument(doc) {
      this.$emit("download-document", doc);
    },
    async reserveTracking() {
      const result = await Swal.fire({
        title: "Reserve Tracking?",
        text: "Are you sure you want to add this reserve tracking?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#1a4731",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Yes, reserve it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        customClass: {
          popup: "swal-square-popup",
          title: "swal-title",
          confirmButton: "swal-confirm-btn",
          cancelButton: "swal-cancel-btn",
        },
      });

      if (result.isConfirmed) {
        this.reserving = true;
        try {
          const response = await fetch("/dts_denr/api/reserve-tracking", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
            },
          });

          const data = await response.json();
          if (!response.ok) throw new Error(data.message || "Failed to reserve tracking");

          await Swal.fire({
            title: "Reserved!",
            text: data.message || "Tracking has been reserved successfully.",
            icon: "success",
            confirmButtonColor: "#1a4731",
            confirmButtonText: "OK",
          });

          await this.getDataTracking();
          this.$emit("tracking-reserved", data);
          this.showNotification("Tracking reserved successfully!", "success");
        } catch (error) {
          console.error("Reserve tracking error:", error);
          await Swal.fire({
            title: "Error!",
            text: error.message || "Failed to reserve tracking. Please try again.",
            icon: "error",
            confirmButtonColor: "#1a4731",
            confirmButtonText: "OK",
          });
          this.showNotification(error.message || "Failed to reserve tracking", "error");
        } finally {
          this.reserving = false;
        }
      }
    },
    submitCreateForm() {
      this.creating = true;
      this.errors = {};
      this.formErrors = { show: false, message: "" };

      setTimeout(() => {
        if (!this.createForm.tracking_number) this.errors.tracking_number = "Tracking number is required";
        if (!this.createForm.document_type) this.errors.document_type = "Document type is required";
        if (!this.createForm.subject) this.errors.subject = "Subject is required";
        if (!this.createForm.sender_name) this.errors.sender_name = "Sender is required";
        if (!this.createForm.date_received) this.errors.date_received = "Date received is required";

        if (Object.keys(this.errors).length > 0) {
          this.formErrors = { show: true, message: "Please fill in all required fields." };
          this.creating = false;
          return;
        }

        this.$emit("create-document", { ...this.createForm });
        this.closeCreateModal();
        this.resetCreateForm();
        this.showNotification("Document created successfully!", "success");
        this.creating = false;
      }, 800);
    },
    showNotification(message, type = "success") {
      if (this.notification.timeout) clearTimeout(this.notification.timeout);
      this.notification.show = true;
      this.notification.message = message;
      this.notification.type = type;
      this.notification.timeout = setTimeout(() => {
        this.notification.show = false;
      }, 3000);
    },
  },
};
</script>

<style scoped>
/* ===== CSS Variables ===== */
:root {
  --forest: #1a4731;
  --leaf: #2d6a4f;
  --mint: #52b788;
  --gold: #c9a84c;
  --gold-light: #f0d080;
  --paper: #f4f9f6;
  --charcoal: #1c2b24;
  --muted: #5c7a6b;
  --border: #b7d5c3;
  --white: #ffffff;
  --error: #c0392b;
}

/* ===== VERTICAL TABS LAYOUT ===== */
.tabs-vertical-container {
  display: flex;
  gap: 0;
  min-height: 400px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.tabs-vertical-nav {
  display: flex;
  flex-direction: column;
  width: 250px;
  min-width: 250px;
  background: #f8fafc;
  border-right: 1px solid #e5e7eb;
  padding: 16px;
  gap: 8px;
}
.tab-vertical-button {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}
.tab-vertical-button::before {
  content: "";
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 0;
  background: linear-gradient(180deg, #2d6a4f, #1e4d2b);
  border-radius: 0 4px 4px 0;
  transition: height 0.3s ease;
}
.tab-vertical-button:hover {
  background: #f0fdf4;
  border-color: #86efac;
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.tab-vertical-button:hover::before {
  height: 40%;
}
.tab-vertical-button.active {
  background: linear-gradient(135deg, #2d6a4f, #1e4d2b);
  border-color: #2d6a4f;
  color: #ffffff;
  transform: translateX(4px);
  box-shadow: 0 8px 25px rgba(45, 106, 79, 0.4);
}
.tab-vertical-button.active::before {
  height: 60%;
  background: linear-gradient(180deg, #52b788, #ffffff);
}
.tab-vertical-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #f0fdf4;
  transition: all 0.3s ease;
  flex-shrink: 0;
}
.tab-vertical-button.active .tab-vertical-icon {
  background: rgba(255, 255, 255, 0.25);
}
.tab-vertical-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}
.tab-vertical-label {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
  color: #374151;
  transition: color 0.3s ease;
}
.tab-vertical-button.active .tab-vertical-label {
  color: #ffffff;
}
.tab-vertical-count {
  font-size: 20px;
  font-weight: 800;
  color: #2d6a4f;
  transition: color 0.3s ease;
}
.tab-vertical-button.active .tab-vertical-count {
  color: #ffffff;
}
.tab-vertical-indicator {
  position: absolute;
  right: 12px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #e5e7eb;
  transition: all 0.3s ease;
}
.tab-vertical-button:hover .tab-vertical-indicator {
  background: #86efac;
}
.tab-vertical-button.active .tab-vertical-indicator {
  background: #52b788;
  box-shadow: 0 0 10px rgba(82, 183, 136, 0.6);
}
.tabs-vertical-body {
  flex: 1;
  padding: 20px;
  background: #ffffff;
  min-width: 0;
}

/* ===== DOCUMENT VIEWER TABS ===== */
.document-viewer-tabs {
  display: flex;
  gap: 4px;
  padding: 12px 20px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
}

.viewer-tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.3s ease;
  font-family: "Inter", sans-serif;
}

.viewer-tab-btn i {
  font-size: 14px;
}

.viewer-tab-btn:hover {
  background: #f0fdf4;
  border-color: #86efac;
  color: #2d6a4f;
  transform: translateY(-1px);
}

.viewer-tab-btn.active {
  background: linear-gradient(135deg, #2d6a4f, #1e4d2b);
  border-color: #2d6a4f;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
}

.viewer-tab-btn.active i {
  color: #52b788;
}

/* ===== FILTER CONTROLS ===== */
.filter-controls {
  margin-bottom: 20px;
}
.search-filter-row {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}
.search-box-wrapper {
  flex: 1;
  min-width: 200px;
}
.search-box {
  position: relative;
  width: 100%;
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  z-index: 1;
}
.search-input {
  width: 100%;
  padding: 10px 35px 10px 36px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  background: #ffffff;
  transition: all 0.3s ease;
}
.search-input:focus {
  border-color: #2d6a4f;
  box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
}
.search-clear-btn {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
}
.search-clear-btn:hover {
  color: #ef4444;
}
.per-page-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
}
.per-page-label {
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
}
.per-page-select {
  padding: 8px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  background: #ffffff;
  outline: none;
  cursor: pointer;
  transition: all 0.3s ease;
}
.per-page-select:focus {
  border-color: #2d6a4f;
}
.filter-wrapper {
  min-width: 180px;
}
.filter-box {
  position: relative;
}
.filter-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  z-index: 1;
}
.filter-select {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  background: #ffffff;
  outline: none;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  transition: all 0.3s ease;
}
.filter-select:focus {
  border-color: #2d6a4f;
  box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
}

/* ===== CREATE DOCUMENT BUTTON ===== */
.btn-create-document {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #2d6a4f, #1e4d2b);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
  font-family: "Inter", sans-serif;
  letter-spacing: 0.3px;
}
.btn-create-document:hover {
  background: linear-gradient(135deg, #1e4d2b, #2d6a4f);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(45, 106, 79, 0.4);
}
.btn-create-document:active {
  transform: translateY(0);
}

/* ===== MY TRACKING CONTROLS ===== */
.my-tracking-controls {
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
}

.btn-reserve-tracking-forest {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #1a4731 0%, #2d6a4f 100%);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.3s ease;
  box-shadow: 0 4px 16px rgba(26, 71, 49, 0.4);
  font-family: "Inter", sans-serif;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.btn-reserve-tracking-forest:hover:not(:disabled) {
  background: linear-gradient(135deg, #0d281a 0%, #1e4d2b 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(26, 71, 49, 0.5);
}

.btn-reserve-tracking-forest:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(26, 71, 49, 0.3);
}

.btn-reserve-tracking-forest:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

/* Active Filters */
.active-filters {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
  padding: 10px 14px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  flex-wrap: wrap;
}
.active-filters-label {
  font-size: 12px;
  font-weight: 600;
  color: #166534;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: #ffffff;
  border: 1px solid #86efac;
  border-radius: 20px;
  font-size: 12px;
  color: #166534;
  font-weight: 500;
}
.filter-tag-close {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
}
.filter-tag-close:hover {
  color: #ef4444;
}
.clear-all-filters {
  padding: 4px 12px;
  background: none;
  border: 1px solid #86efac;
  border-radius: 6px;
  font-size: 12px;
  color: #166534;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}
.clear-all-filters:hover {
  background: #dcfce7;
  border-color: #166534;
}
.results-summary {
  margin-top: 10px;
  font-size: 13px;
  color: #6b7280;
}
.results-count {
  font-weight: 700;
  color: #2d6a4f;
  font-size: 16px;
}

/* ===== MODAL OVERLAY ===== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  animation: fadeIn 0.2s ease-out;
}
.enhanced-modal {
  width: 100%;
  max-width: 620px;
  margin: 0 15px;
  animation: modalSlideUp 0.3s ease-out;
}
@keyframes modalSlideUp {
  from {
    transform: translateY(30px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
.square-modal {
  border: none;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
  background: #fff;
  position: relative;
}
.square-header {
  background: linear-gradient(135deg, #1e4d2b, #2d6a4f);
  padding: 20px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.square-icon {
  width: 42px;
  height: 42px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  margin-right: 14px;
}
.modal-title {
  font-weight: 700;
  font-size: 1.25rem;
}
.modal-subtitle {
  font-size: 0.85rem;
  opacity: 0.85;
}
.square-close {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
}
.square-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.modal-body-enhanced {
  padding: 24px;
  background: #f9fafb;
}
.error-msg {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #fef0ef;
  border: 1px solid #f5c6c3;
  border-radius: 7px;
  padding: 10px 14px;
  font-size: 12px;
  color: #c0392b;
  margin-bottom: 20px;
  font-weight: 500;
}
.form-label-enhanced {
  font-weight: 600;
  font-size: 0.9rem;
  color: #1e293b;
  margin-bottom: 6px;
  display: block;
}
.required-star {
  color: #dc2626;
  margin-left: 3px;
}
.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.input-icon {
  position: absolute;
  left: 14px;
  color: #5c7a6b;
  pointer-events: none;
  display: flex;
  z-index: 2;
}
.form-input {
  width: 100%;
  height: 46px;
  padding: 0 14px 0 42px;
  border: 1.5px solid #b7d5c3;
  border-radius: 8px;
  font-family: "Inter", sans-serif;
  font-size: 14px;
  color: #1c2b24;
  background: #f4f9f6;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  outline: none;
}
.form-input::placeholder {
  color: #b0c4b8;
}
.form-input:focus {
  border-color: #2d6a4f;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
}
.form-input:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
.form-textarea {
  height: auto;
  padding-top: 12px;
  padding-left: 42px;
  resize: vertical;
  min-height: 46px;
}
select.form-input {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235C7A6B' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  padding-right: 36px;
}
.is-invalid {
  border-color: #dc2626 !important;
  background: #fff5f5 !important;
}
.is-invalid:focus {
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
}
.invalid-feedback {
  color: #dc2626;
  font-size: 0.8rem;
  margin-top: 4px;
}
.modal-actions {
  margin-top: 24px;
  padding-top: 18px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.square-btn {
  border-radius: 8px !important;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  transition: all 0.2s;
}
.btn-save {
  background: linear-gradient(135deg, #2d6a4f 0%, #1a4731 100%);
  color: white;
  border: none;
  padding: 10px 22px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  box-shadow: 0 4px 18px rgba(26, 71, 49, 0.3);
}
.btn-save:hover {
  box-shadow: 0 6px 24px rgba(26, 71, 49, 0.38);
  transform: translateY(-1px);
}
.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}
.btn-outline-secondary {
  border-color: #d1d5db;
  color: #374151;
  padding: 10px 18px;
}
.btn-outline-secondary:hover {
  background: #f9fafb;
}
.btn-light {
  background: #f9fafb;
  border-color: #e5e7eb;
  color: #374151;
  padding: 10px 18px;
}
.btn-light:hover {
  background: #f3f4f6;
}

/* ===== ENHANCED DOCUMENT VIEWER MODAL ===== */
.document-view-modal {
  max-width: 1400px;
  width: 95vw;
  max-height: 90vh;
}

.document-viewer-body {
  padding: 0;
  max-height: calc(90vh - 80px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* ===== DOCUMENT VIEWER LAYOUT (Details + PDF side by side) ===== */
.document-viewer-layout {
  display: grid;
  grid-template-columns: 380px 1fr;
  height: calc(90vh - 140px);
  min-height: 500px;
}

/* ===== DETAILS PANEL (LEFT SIDE) ===== */
.details-panel {
  background: #ffffff;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.details-panel-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.details-panel-header i {
  color: #2d6a4f;
  font-size: 1.1rem;
}

.details-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.details-content::-webkit-scrollbar {
  width: 6px;
}

.details-content::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.details-content::-webkit-scrollbar-thumb {
  background: #94a3b8;
  border-radius: 3px;
}

.details-content::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

.detail-card {
  display: flex;
  gap: 12px;
  padding: 14px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.detail-card:hover {
  background: #ffffff;
  border-color: #86efac;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transform: translateX(2px);
}

.detail-icon-wrapper {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  background: #e0f2fe;
  color: #0284c7;
}

.detail-icon-wrapper.type-icon {
  background: #fef3c7;
  color: #d97706;
}

.detail-icon-wrapper.subject-icon {
  background: #dcfce7;
  color: #16a34a;
}

.detail-icon-wrapper.sender-icon-card {
  background: #ede9fe;
  color: #7c3aed;
}

.detail-icon-wrapper.date-icon-card {
  background: #fce7f3;
  color: #db2777;
}

.detail-icon-wrapper.desc-icon {
  background: #fff7ed;
  color: #ea580c;
}

.detail-icon-wrapper.meta-icon {
  background: #f1f5f9;
  color: #64748b;
}

.detail-info {
  flex: 1;
  min-width: 0;
}

.detail-info label {
  display: block;
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.detail-value {
  font-size: 0.85rem;
  color: #1e293b;
  font-weight: 500;
  word-break: break-word;
  line-height: 1.4;
}

.tracking-number-large {
  font-size: 0.9rem;
  font-weight: 700;
  color: #2d6a4f;
  font-family: 'Courier New', monospace;
  background: #f0fdf4;
  padding: 2px 8px;
  border-radius: 4px;
  display: inline-block;
}

.doc-type-badge-large {
  display: inline-block;
  padding: 3px 12px;
  background: #e0e7ff;
  color: #3730a3;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  border: 1px solid #c7d2fe;
}

.time-text {
  color: #64748b;
  font-size: 0.75rem;
  display: block;
  margin-top: 2px;
}

.description-card {
  background: #fffbeb;
  border-color: #fde68a;
}

.description-text {
  color: #78350f;
  font-style: italic;
  line-height: 1.6;
}

.meta-card {
  background: #f8fafc;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-in-progress {
  background: #fef3c7;
  color: #d97706;
  border: 1px solid #fde68a;
}

.status-for-release {
  background: #dbeafe;
  color: #2563eb;
  border: 1px solid #bfdbfe;
}

.status-released {
  background: #d1fae5;
  color: #059669;
  border: 1px solid #a7f3d0;
}

/* ===== PDF PANEL (RIGHT SIDE) ===== */
.pdf-panel {
  display: flex;
  flex-direction: column;
  background: #f8fafc;
  overflow: hidden;
}

.pdf-panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.pdf-panel-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.9rem;
}

.pdf-panel-title i {
  color: #dc2626;
  font-size: 1.2rem;
}

.pdf-controls {
  display: flex;
  gap: 4px;
  align-items: center;
}

.btn-pdf-control {
  background: #f1f5f9;
  border: 1px solid #e5e7eb;
  color: #475569;
  width: 34px;
  height: 34px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.btn-pdf-control:hover:not(:disabled) {
  background: #e2e8f0;
  border-color: #94a3b8;
  color: #1e293b;
}

.btn-pdf-control:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pdf-viewer-wrapper {
  flex: 1;
  overflow: auto;
  background: #525659;
  position: relative;
  min-height: 400px;
}

.pdf-viewer-wrapper::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

.pdf-viewer-wrapper::-webkit-scrollbar-track {
  background: #3a3d40;
}

.pdf-viewer-wrapper::-webkit-scrollbar-thumb {
  background: #6b7280;
  border-radius: 5px;
}

.pdf-viewer-wrapper::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

.pdf-iframe {
  border: none;
  display: block;
  background: #ffffff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.pdf-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 400px;
  color: #9ca3af;
  padding: 40px;
}

.pdf-loader-animation {
  position: relative;
  width: 80px;
  height: 80px;
  margin-bottom: 16px;
}

.pdf-loader-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 2rem;
  color: #dc2626;
  z-index: 2;
}

.pdf-state-text {
  color: #d1d5db;
  font-size: 0.9rem;
  margin-top: 8px;
}

.pdf-error {
  color: #fca5a5;
}

.pdf-error h5 {
  color: #fca5a5;
  font-weight: 600;
}

.error-details {
  background: rgba(0, 0, 0, 0.3);
  padding: 8px 16px;
  border-radius: 6px;
  margin-top: 8px;
  max-width: 80%;
  overflow: auto;
}

.error-details code {
  color: #fca5a5;
  font-size: 0.75rem;
  word-break: break-all;
}

.pdf-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 20px;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  font-size: 0.75rem;
  color: #64748b;
  flex-shrink: 0;
}

.pdf-zoom-level {
  font-weight: 600;
  color: #2d6a4f;
}

.pdf-page-info {
  font-family: 'Courier New', monospace;
}

/* ===== ROUTE HISTORY PANEL ===== */
.route-history-panel {
  display: flex;
  flex-direction: column;
  height: calc(90vh - 140px);
  background: #ffffff;
  min-height: 500px;
}

.route-history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  flex-shrink: 0;
}

.route-history-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  color: #1e293b;
  font-size: 1rem;
}

.route-history-title i {
  color: #2d6a4f;
  font-size: 1.2rem;
}

.route-history-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #166534;
}

.route-history-badge i {
  font-size: 0.9rem;
}

.route-history-content {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

.route-history-content::-webkit-scrollbar {
  width: 6px;
}

.route-history-content::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.route-history-content::-webkit-scrollbar-thumb {
  background: #94a3b8;
  border-radius: 3px;
}

.route-loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
}

.route-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  text-align: center;
}

.empty-icon-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #94a3b8;
}

/* ===== ROUTE TIMELINE ===== */
.route-timeline {
  position: relative;
  padding-left: 40px;
}

.timeline-item {
  position: relative;
  padding-bottom: 32px;
}

.timeline-item.timeline-last {
  padding-bottom: 0;
}

.timeline-marker {
  position: absolute;
  left: -40px;
  top: 0;
  width: 40px;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.marker-dot {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  z-index: 2;
  transition: all 0.3s ease;
}

.marker-current {
  background: linear-gradient(135deg, #2d6a4f, #1e4d2b);
  color: #ffffff;
  box-shadow: 0 0 0 4px rgba(45, 106, 79, 0.2);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 4px rgba(45, 106, 79, 0.2);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(45, 106, 79, 0.1);
  }
}

.marker-completed {
  background: #d1fae5;
  color: #059669;
  border: 2px solid #a7f3d0;
}

.marker-rejected {
  background: #fef0ef;
  color: #dc2626;
  border: 2px solid #f5c6c3;
}

.marker-pending {
  background: #fef3c7;
  color: #d97706;
  border: 2px solid #fde68a;
}

.marker-line {
  flex: 1;
  width: 2px;
  background: linear-gradient(180deg, #e5e7eb, #d1d5db);
  margin-top: 4px;
}

.timeline-item.timeline-first .marker-line {
  background: linear-gradient(180deg, #2d6a4f, #e5e7eb);
}

.timeline-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  transition: all 0.3s ease;
}

.timeline-card:hover {
  border-color: #86efac;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  transform: translateX(4px);
}

.timeline-card.current-card {
  border-color: #2d6a4f;
  background: #f8fafc;
  box-shadow: 0 4px 20px rgba(45, 106, 79, 0.1);
}

.timeline-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
  flex-wrap: wrap;
  gap: 8px;
}

.timeline-step-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.step-number {
  font-size: 0.75rem;
  font-weight: 700;
  color: #2d6a4f;
  background: #f0fdf4;
  padding: 3px 10px;
  border-radius: 6px;
  border: 1px solid #bbf7d0;
}

.route-status-badge {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.route-completed {
  background: #d1fae5;
  color: #059669;
  border: 1px solid #a7f3d0;
}

.route-in-progress {
  background: #dbeafe;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  animation: statusPulse 2s infinite;
}

@keyframes statusPulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.route-pending {
  background: #fef3c7;
  color: #d97706;
  border: 1px solid #fde68a;
}

.route-rejected {
  background: #fef0ef;
  color: #dc2626;
  border: 1px solid #f5c6c3;
}

.timeline-date {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: #64748b;
}

.timeline-date i {
  font-size: 0.8rem;
}

.timeline-card-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.route-detail-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.route-detail-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  flex: 1;
  min-width: 150px;
  padding: 10px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.detail-icon-small {
  width: 32px;
  height: 32px;
  min-width: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
}

.from-icon {
  background: #fef0ef;
  color: #dc2626;
}

.to-icon {
  background: #d1fae5;
  color: #059669;
}

.detail-text {
  flex: 1;
  min-width: 0;
}

.detail-text label {
  display: block;
  font-size: 0.65rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 3px;
}

.office-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1e293b;
}

.route-arrow {
  display: flex;
  align-items: center;
  color: #94a3b8;
  font-size: 1.2rem;
  flex-shrink: 0;
}

.route-meta-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.route-meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.8rem;
  color: #475569;
}

.route-meta-item i {
  font-size: 0.85rem;
  color: #2d6a4f;
}

/* ===== HEADER ENHANCEMENTS ===== */
.document-header {
  padding: 16px 24px;
}

.document-icon {
  background: rgba(220, 38, 38, 0.2);
}

.tracking-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 2px 10px;
  border-radius: 12px;
  font-family: 'Courier New', monospace;
  font-size: 0.75rem;
  margin-right: 8px;
}

.status-pill {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-header-action {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-header-action:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.btn-header-update {
  background: rgba(251, 191, 36, 0.3);
  border-color: rgba(251, 191, 36, 0.4);
}

.btn-header-update:hover {
  background: rgba(251, 191, 36, 0.5);
  transform: scale(1.05);
}

/* ===== NOTIFICATION TOAST ===== */
.notification-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 20px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  animation: slideInRight 0.3s ease-out;
  min-width: 300px;
  max-width: 500px;
}
@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
.notification-toast.success {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
}
.notification-toast.error {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  color: white;
}
.notification-content {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 500;
}
.notification-content i {
  font-size: 1.2rem;
}
.notification-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
}
.notification-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* ===== TABLE ===== */
.table-responsive {
  overflow-x: auto;
  margin-top: 16px;
}
.office-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  background: white;
}
.office-table th,
.office-table td {
  border: 1px solid #f3f4f6;
  padding: 12px 14px;
  text-align: left;
  vertical-align: middle;
}
.office-table thead th {
  background: #f8fafc;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e5e7eb;
}
.office-table tbody tr {
  transition: all 0.2s;
}
.office-table tbody tr:hover {
  background: #f0fdf4;
  border-color: #86efac;
}
.office-table tbody tr:nth-child(even) {
  background: #fafafa;
}
.office-table tbody tr:nth-child(even):hover {
  background: #f0fdf4;
}
.row-number {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: #f3f4f6;
  color: #6b7280;
  font-size: 12px;
  font-weight: 600;
}
.tracking-number {
  font-weight: 700;
  color: #2d6a4f;
  font-family: "Courier New", monospace;
  font-size: 13px;
  padding: 2px 8px;
  background: #f0fdf4;
  border-radius: 4px;
}
.doc-type-badge {
  display: inline-block;
  padding: 3px 10px;
  background: #e0e7ff;
  color: #3730a3;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  border: 1px solid #c7d2fe;
}
.subject-text {
  color: #1e293b;
  font-weight: 500;
  line-height: 1.4;
  font-size: 13px;
}
.sender-text {
  color: #475569;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
}
.sender-icon {
  color: #94a3b8;
  font-size: 14px;
}
.date-received {
  color: #64748b;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.date-icon {
  color: #94a3b8;
  font-size: 13px;
}

/* ===== ACTION BUTTONS ===== */
.action-buttons {
  display: flex;
  gap: 6px;
  align-items: center;
}
.btn-action {
  background: none;
  border: 1px solid transparent;
  border-radius: 6px;
  padding: 6px 10px;
  cursor: pointer;
  font-size: 0.95rem;
  transition: all 0.2s;
  text-decoration: none;
}
.btn-view {
  color: #6366f1;
  border-color: #c7d2fe;
  background: #eef2ff;
}
.btn-view:hover {
  background: #ddd6fe;
  transform: scale(1.05);
}
.btn-process {
  color: #f59e0b;
  border-color: #fde68a;
  background: #fef3c7;
}
.btn-process:hover {
  background: #fde68a;
  transform: scale(1.05);
}
.btn-release {
  color: #059669;
  border-color: #a7f3d0;
  background: #d1fae5;
}
.btn-release:hover {
  background: #a7f3d0;
  transform: scale(1.05);
}
.btn-download {
  color: #0891b2;
  border-color: #67e8f9;
  background: #cffafe;
}
.btn-download:hover {
  background: #67e8f9;
  transform: scale(1.05);
}

.empty-state {
  padding: 20px;
  text-align: center;
}
.loader-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #2d6a4f;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 10px;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ===== MY TRACKING MODAL ENHANCEMENTS ===== */
.tracking-modal {
  max-height: 90vh;
  display: flex;
  align-items: center;
}

.tracking-modal .modal-content {
  max-height: 85vh;
  display: flex;
  flex-direction: column;
}

.tracking-modal-body {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  padding: 20px;
  max-height: calc(85vh - 80px);
}

.sticky-top-controls {
  position: sticky;
  top: 0;
  z-index: 10;
  background: #f9fafb;
  padding-bottom: 16px;
  margin-bottom: 8px;
  flex-shrink: 0;
}

.tracking-table-scroll {
  flex: 1;
  overflow-y: auto;
  overflow-x: auto;
  min-height: 200px;
  max-height: calc(85vh - 250px);
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 16px;
}

.tracking-table-scroll::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.tracking-table-scroll::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.tracking-table-scroll::-webkit-scrollbar-thumb {
  background: #94a3b8;
  border-radius: 4px;
}

.tracking-table-scroll::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

.tracking-table-scroll {
  scrollbar-width: thin;
  scrollbar-color: #94a3b8 #f1f5f9;
}

.tracking-table-scroll .table-responsive {
  margin-top: 0;
}

.tracking-table-scroll .office-table thead {
  position: sticky;
  top: 0;
  z-index: 5;
  background: #f8fafc;
}

.tracking-table-scroll .office-table thead th {
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
}

.tracking-pagination {
  flex-shrink: 0;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.tracking-table-scroll .empty-state {
  padding: 40px 20px;
  text-align: center;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 1200px) {
  .document-view-modal {
    max-width: 98vw;
  }
  
  .document-viewer-layout {
    grid-template-columns: 340px 1fr;
  }
}

@media (max-width: 1024px) {
  .tabs-vertical-container {
    flex-direction: column;
  }
  .tabs-vertical-nav {
    flex-direction: row;
    width: 100%;
    min-width: 100%;
    overflow-x: auto;
    padding: 12px;
    gap: 8px;
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
  }
  .tab-vertical-button {
    flex-direction: column;
    min-width: 120px;
    padding: 12px;
    gap: 8px;
  }
  .tab-vertical-button::before {
    left: 50%;
    top: auto;
    bottom: 0;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    border-radius: 4px 4px 0 0;
    transition: width 0.3s ease;
  }
  .tab-vertical-button:hover::before {
    width: 40%;
    height: 3px;
  }
  .tab-vertical-button.active::before {
    width: 60%;
    height: 3px;
  }
  .tab-vertical-button:hover {
    transform: translateY(-2px);
  }
  .tab-vertical-button.active {
    transform: translateY(-2px);
  }
  .tab-vertical-indicator {
    display: none;
  }
  .tabs-vertical-body {
    padding: 16px;
  }
  .search-filter-row {
    flex-direction: column;
  }
  .filter-wrapper {
    min-width: 100%;
  }
  .btn-create-document {
    width: 100%;
    justify-content: center;
  }
  .my-tracking-controls {
    flex-direction: column;
    align-items: stretch;
  }
}

@media (max-width: 992px) {
  .document-viewer-layout {
    grid-template-columns: 1fr;
    grid-template-rows: auto 1fr;
    height: calc(90vh - 140px);
  }
  
  .details-panel {
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
    max-height: 350px;
  }
  
  .pdf-viewer-wrapper {
    min-height: 350px;
  }
  
  .route-detail-row {
    flex-direction: column;
  }
  
  .route-arrow {
    transform: rotate(90deg);
    align-self: center;
  }
}

@media (max-width: 768px) {
  .document-view-modal {
    max-width: 100vw;
    width: 100vw;
    margin: 0;
    border-radius: 0;
    max-height: 100vh;
  }
  
  .document-viewer-tabs {
    padding: 8px 12px;
    gap: 4px;
  }
  
  .viewer-tab-btn {
    padding: 8px 14px;
    font-size: 12px;
  }
  
  .details-panel {
    max-height: 280px;
  }
  
  .pdf-viewer-wrapper {
    min-height: 300px;
  }
  
  .pdf-state {
    min-height: 300px;
  }
  
  .route-history-content {
    padding: 16px;
  }
  
  .route-timeline {
    padding-left: 32px;
  }
  
  .timeline-marker {
    left: -32px;
    width: 32px;
  }
  
  .marker-dot {
    width: 28px;
    height: 28px;
    font-size: 0.85rem;
  }
  
  .timeline-card-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .tracking-modal {
    max-width: 98% !important;
    margin: 0 5px;
  }
  .tracking-modal-body {
    padding: 16px;
    max-height: calc(80vh - 60px);
  }
  .my-tracking-controls .search-box-wrapper {
    max-width: 100% !important;
  }
  .btn-reserve-tracking-forest {
    width: 100%;
    justify-content: center;
    padding: 10px 16px;
    font-size: 13px;
  }
  .tracking-table-scroll {
    max-height: calc(75vh - 250px);
  }
  .tracking-table-scroll .office-table {
    font-size: 12px;
  }
  .tracking-table-scroll .office-table th,
  .tracking-table-scroll .office-table td {
    padding: 8px 10px;
  }
}

@media (max-width: 576px) {
  .document-header {
    padding: 12px 16px;
  }
  
  .viewer-tab-btn span {
    display: none;
  }
  
  .viewer-tab-btn i {
    font-size: 16px;
  }
  
  .detail-card {
    padding: 10px;
  }
  
  .detail-icon-wrapper {
    width: 34px;
    height: 34px;
    min-width: 34px;
    font-size: 0.9rem;
  }
  
  .btn-pdf-control {
    width: 30px;
    height: 30px;
    font-size: 0.8rem;
  }
  
  .pdf-footer {
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
  }
  
  .route-history-header {
    padding: 12px 16px;
    flex-direction: column;
    gap: 8px;
    align-items: flex-start;
  }
  
  .active-filters {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 500px) {
  .notification-toast {
    left: 10px;
    right: 10px;
    min-width: auto;
    max-width: none;
    top: 10px;
  }
  .enhanced-modal {
    max-width: 95%;
    margin: 0 10px;
  }
}
</style>