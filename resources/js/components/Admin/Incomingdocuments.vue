<template>
  <div class="card border-0 shadow-md">
    <!-- ===== HEADER ===== -->
    <div class="card-header py-3" style="background: #0f766e">
      <div
        class="d-flex flex-wrap justify-content-between align-items-center gap-3"
      >
        <div class="d-flex align-items-center">
          <div class="step-header-icon bg-white bg-opacity-20 p-2 rounded">
            <i
              :class="headerIcon"
              style="color: rgb(46, 125, 50); font-size: 2rem"
            ></i>
          </div>
          <div class="ms-3">
            <h5 class="mb-0 fw-semibold text">{{ headerTitle }}</h5>
            <small class="text-50 d-none d-sm-inline">{{
              headerSubtitle
            }}</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="bg-light bg-opacity-90 px-3 py-2 rounded-3 shadow-sm">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-clock" style="color: rgb(46, 125, 50)"></i>
              <span class="fw-semibold" style="color: rgb(46, 125, 50)">{{
                currentDateTime
              }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== BODY ===== -->
    <div class="card-body" style="position: relative; min-height: 300px">
      <!-- Notification Toast -->
      <div
        v-if="notification.show"
        :class="['notification-toast', notification.type]"
      >
        <div class="notification-content">
          <i
            :class="
              notification.type === 'success'
                ? 'bi bi-check-circle-fill'
                : 'bi bi-exclamation-circle-fill'
            "
          ></i>
          <span>{{ notification.message }}</span>
        </div>
        <button
          @click="notification.show = false"
          class="notification-close"
        >
          <i class="bi bi-x"></i>
        </button>
      </div>

      <!-- Vertical Tabs Design -->
      <div class="tabs-vertical-container">
        <div class="tabs-vertical-nav">
          <button
            class="tab-vertical-button"
            :class="{ active: activeTab === 'in-progress' }"
            @click="switchTab('in-progress')"
          >
            <div class="tab-vertical-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" :stroke="activeTab === 'in-progress' ? '#ffffff' : '#059669'" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">IN PROGRESS</span>
              <span class="tab-vertical-count">{{ inProgressCount }}</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>

          <button
            class="tab-vertical-button"
            :class="{ active: activeTab === 'for-release' }"
            @click="switchTab('for-release')"
          >
            <div class="tab-vertical-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" :stroke="activeTab === 'for-release' ? '#ffffff' : '#059669'" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="12" y1="12" x2="12" y2="18"/>
                <polyline points="9 15 12 18 15 15"/>
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">FOR RELEASE</span>
              <span class="tab-vertical-count">{{ forReleaseCount }}</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>

          <button
            class="tab-vertical-button"
            :class="{ active: activeTab === 'released' }"
            @click="switchTab('released')"
          >
            <div class="tab-vertical-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" :stroke="activeTab === 'released' ? '#ffffff' : '#059669'" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">RELEASED</span>
              <span class="tab-vertical-count">{{ releasedCount }}</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>
        </div>

        <div class="tabs-vertical-body">
          <!-- Filter Controls -->
          <div class="filter-controls">
            <div class="search-filter-row">
              <!-- Search Box -->
              <div class="search-box-wrapper">
                <div class="search-box">
                  <i class="bi bi-search search-icon"></i>
                  <input
                    type="text"
                    v-model="searchQuery"
                    @input="applyFilters"
                    class="search-input"
                    placeholder="Search by tracking no, subject, sender..."
                  />
                  <button
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="search-clear-btn"
                  >
                    <i class="bi bi-x-circle"></i>
                  </button>
                </div>
              </div>

              <!-- Per Page Selector -->
              <div class="per-page-wrapper">
                <span class="per-page-label">Show</span>
                <select
                  v-model="perPage"
                  @change="changePerPage"
                  class="per-page-select"
                >
                  <option :value="10">10</option>
                  <option :value="25">25</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
                </select>
                <span class="per-page-label">entries</span>
              </div>

              <!-- Document Type Filter -->
              <div class="filter-wrapper">
                <div class="filter-box">
                  <i class="bi bi-funnel filter-icon"></i>
                  <select
                    v-model="documentTypeFilter"
                    @change="applyFilters"
                    class="filter-select"
                  >
                    <option value="">All Document Types</option>
                    <option v-for="type in documentTypes" :key="type" :value="type">
                      {{ type }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Create Document Button -->
              <button
                @click="openCreateModal"
                class="btn-create-document"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Create Document
              </button>
            </div>

            <!-- Active Filters Display -->
            <div class="active-filters" v-if="searchQuery || documentTypeFilter">
              <span class="active-filters-label">Active Filters:</span>
              <span v-if="searchQuery" class="filter-tag">
                <i class="bi bi-search"></i> "{{ searchQuery }}"
                <button @click="clearSearch" class="filter-tag-close">
                  <i class="bi bi-x"></i>
                </button>
              </span>
              <span v-if="documentTypeFilter" class="filter-tag">
                <i class="bi bi-funnel"></i> {{ documentTypeFilter }}
                <button @click="clearDocTypeFilter" class="filter-tag-close">
                  <i class="bi bi-x"></i>
                </button>
              </span>
              <button @click="clearAllFilters" class="clear-all-filters">
                Clear All
              </button>
            </div>

            <!-- Results Summary -->
            <div class="results-summary">
              <span class="results-count">{{ filteredDocuments.length }}</span> 
              document(s) found in <strong>{{ activeTabLabel }}</strong>
            </div>
          </div>

          <!-- Table -->
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
                <tr v-if="loading && filteredDocuments.length === 0">
                  <td colspan="7" class="text-center py-5">
                    <div class="loader-spinner"></div>
                    <div class="mt-2">Loading documents...</div>
                  </td>
                </tr>
                <tr v-else-if="!loading && filteredDocuments.length === 0">
                  <td colspan="7" class="text-center py-5">
                    <div class="empty-state">
                      <i class="bi bi-file-earmark-x" style="font-size: 3rem; color: #9ca3af;"></i>
                      <p class="mt-2 text-muted">
                        {{ searchQuery || documentTypeFilter ? 
                           'No documents match your filters' : 
                           'No ' + activeTabLabel + ' Documents' }}
                      </p>
                      <button 
                        v-if="!searchQuery && !documentTypeFilter"
                        @click="openCreateModal"
                        class="btn btn-sm mt-3"
                        style="background-color: #2d6a4f; color: white; border: none; border-radius: 6px; padding: 8px 16px;"
                      >
                        <i class="bi bi-plus-circle me-1"></i> Create First Document
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-for="(doc, index) in paginatedDocuments" :key="doc.id">
                  <td class="text-center">
                    <span class="row-number">{{ (currentPage - 1) * perPage + index + 1 }}</span>
                  </td>
                  <td>
                    <span class="tracking-number">{{ doc.tracking_number }}</span>
                  </td>
                  <td>
                    <span class="doc-type-badge">{{ doc.document_type }}</span>
                  </td>
                  <td>
                    <div class="subject-text">{{ doc.subject || doc.title }}</div>
                  </td>
                  <td>
                    <div class="sender-text">
                      <i class="bi bi-person-circle sender-icon"></i>
                      {{ doc.sender_name || doc.origin }}
                    </div>
                  </td>
                  <td>
                    <div class="date-received">
                      <i class="bi bi-calendar3 date-icon"></i>
                      {{ formatDate(doc.date_received || doc.created_at) }}
                    </div>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <button
                        class="btn-action btn-view"
                        @click="viewDocument(doc)"
                        title="View Details"
                      >
                        <i class="bi bi-eye"></i>
                      </button>
                      <button
                        v-if="activeTab === 'in-progress'"
                        class="btn-action btn-process"
                        @click="processDocument(doc)"
                        title="Move to For Release"
                      >
                        <i class="bi bi-arrow-right-circle"></i>
                      </button>
                      <button
                        v-if="activeTab === 'for-release'"
                        class="btn-action btn-release"
                        @click="releaseDocument(doc)"
                        title="Release Document"
                      >
                        <i class="bi bi-send-check"></i>
                      </button>
                      <button
                        class="btn-action btn-download"
                        @click="downloadDocument(doc)"
                        title="Download"
                      >
                        <i class="bi bi-download"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="pagination-wrapper" v-if="totalPages > 1">
            <div class="pagination-info">
              Showing {{ startItem }} to {{ endItem }} of {{ filteredDocuments.length }} entries
            </div>
            <div class="pagination-buttons">
              <button
                @click="changePage(1)"
                :disabled="currentPage === 1"
                class="page-btn"
              >
                <i class="bi bi-chevron-double-left"></i>
              </button>
              <button
                @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="page-btn"
              >
                <i class="bi bi-chevron-left"></i>
              </button>

              <button
                v-for="page in displayedPages"
                :key="page"
                @click="changePage(page)"
                :class="['page-btn', { active: currentPage === page }]"
              >
                {{ page }}
              </button>

              <button
                @click="changePage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="page-btn"
              >
                <i class="bi bi-chevron-right"></i>
              </button>
              <button
                @click="changePage(totalPages)"
                :disabled="currentPage === totalPages"
                class="page-btn"
              >
                <i class="bi bi-chevron-double-right"></i>
              </button>
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
            <button
              type="button"
              class="btn-close-custom square-close"
              @click="closeCreateModal"
              :disabled="creating"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body-enhanced">
            <!-- Error Message -->
            <div
              v-if="formErrors.show"
              class="error-msg"
              role="alert"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none"/>
              </svg>
              <span>{{ formErrors.message }}</span>
            </div>

            <form @submit.prevent="submitCreateForm">
              <div class="row g-3">
                <!-- Tracking Number -->
                <div class="col-md-6">
                  <label for="tracking_number" class="form-label-enhanced">
                    Tracking Number <span class="required-star">*</span>
                  </label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                      </svg>
                    </span>
                    <input
                      type="text"
                      id="tracking_number"
                      v-model="createForm.tracking_number"
                      class="form-input"
                      :class="{ 'is-invalid': errors.tracking_number }"
                      placeholder="e.g., TRK-2024-001"
                      :disabled="creating"
                    />
                  </div>
                  <div v-if="errors.tracking_number" class="invalid-feedback d-block">
                    {{ errors.tracking_number }}
                  </div>
                </div>

                <!-- Document Type -->
                <div class="col-md-6">
                  <label for="document_type" class="form-label-enhanced">
                    Document Type <span class="required-star">*</span>
                  </label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                      </svg>
                    </span>
                    <select
                      id="document_type"
                      v-model="createForm.document_type"
                      class="form-input"
                      :class="{ 'is-invalid': errors.document_type }"
                      :disabled="creating"
                      style="padding-left: 42px;"
                    >
                      <option value="">Select Document Type</option>
                      <option v-for="type in documentTypes" :key="type" :value="type">
                        {{ type }}
                      </option>
                    </select>
                  </div>
                  <div v-if="errors.document_type" class="invalid-feedback d-block">
                    {{ errors.document_type }}
                  </div>
                </div>
              </div>

              <!-- Subject/Title -->
              <div class="mt-3">
                <label for="subject" class="form-label-enhanced">
                  Subject/Title <span class="required-star">*</span>
                </label>
                <div class="input-wrap">
                  <span class="input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                      <polyline points="14 2 14 8 20 8"/>
                      <line x1="16" y1="13" x2="8" y2="13"/>
                      <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                  </span>
                  <input
                    type="text"
                    id="subject"
                    v-model="createForm.subject"
                    class="form-input"
                    :class="{ 'is-invalid': errors.subject }"
                    placeholder="Enter document subject or title"
                    :disabled="creating"
                  />
                </div>
                <div v-if="errors.subject" class="invalid-feedback d-block">
                  {{ errors.subject }}
                </div>
              </div>

              <div class="row g-3 mt-3">
                <!-- Sender/Origin -->
                <div class="col-md-6">
                  <label for="sender_name" class="form-label-enhanced">
                    Sender/Origin <span class="required-star">*</span>
                  </label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                      </svg>
                    </span>
                    <input
                      type="text"
                      id="sender_name"
                      v-model="createForm.sender_name"
                      class="form-input"
                      :class="{ 'is-invalid': errors.sender_name }"
                      placeholder="Name of sender or origin office"
                      :disabled="creating"
                    />
                  </div>
                  <div v-if="errors.sender_name" class="invalid-feedback d-block">
                    {{ errors.sender_name }}
                  </div>
                </div>

                <!-- Date Received -->
                <div class="col-md-6">
                  <label for="date_received" class="form-label-enhanced">
                    Date Received <span class="required-star">*</span>
                  </label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                      </svg>
                    </span>
                    <input
                      type="datetime-local"
                      id="date_received"
                      v-model="createForm.date_received"
                      class="form-input"
                      :class="{ 'is-invalid': errors.date_received }"
                      :disabled="creating"
                    />
                  </div>
                  <div v-if="errors.date_received" class="invalid-feedback d-block">
                    {{ errors.date_received }}
                  </div>
                </div>
              </div>

              <!-- Description -->
              <div class="mt-3">
                <label for="description" class="form-label-enhanced">
                  Description
                </label>
                <div class="input-wrap">
                  <span class="input-icon" style="top: 14px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                      <polyline points="14 2 14 8 20 8"/>
                      <line x1="16" y1="13" x2="8" y2="13"/>
                      <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                  </span>
                  <textarea
                    id="description"
                    v-model="createForm.description"
                    class="form-input form-textarea"
                    :class="{ 'is-invalid': errors.description }"
                    rows="3"
                    placeholder="Brief description of the document"
                    :disabled="creating"
                  ></textarea>
                </div>
                <div v-if="errors.description" class="invalid-feedback d-block">
                  {{ errors.description }}
                </div>
              </div>

              <div class="modal-actions">
                <button
                  type="button"
                  class="btn btn-outline-secondary square-btn"
                  @click="resetCreateForm"
                  :disabled="creating"
                >
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </button>
                <div class="d-flex gap-3">
                  <button
                    type="button"
                    class="btn btn-light square-btn"
                    @click="closeCreateModal"
                    :disabled="creating"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="btn btn-save square-btn"
                    :disabled="creating"
                  >
                    <span
                      v-if="creating"
                      class="spinner-border spinner-border-sm me-1"
                      role="status"
                      aria-hidden="true"
                    ></span>
                    <i v-else class="bi bi-check2-circle me-1"></i>
                    {{ creating ? 'Creating...' : 'Create Document' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- View Document Modal -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="closeViewModal">
      <div class="modal-dialog enhanced-modal" style="max-width: 800px;">
        <div class="modal-content square-modal">
          <div class="modal-header-enhanced square-header">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon">
                <i class="bi bi-file-earmark-text"></i>
              </div>
              <div>
                <h5 class="modal-title">Document Details</h5>
                <small class="modal-subtitle">{{ selectedDocument?.tracking_number }}</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close-custom square-close"
              @click="closeViewModal"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body-enhanced">
            <div class="document-details" v-if="selectedDocument">
              <div class="detail-row">
                <div class="detail-item">
                  <label>Tracking Number</label>
                  <span>{{ selectedDocument.tracking_number }}</span>
                </div>
                <div class="detail-item">
                  <label>Document Type</label>
                  <span class="doc-type-badge">{{ selectedDocument.document_type }}</span>
                </div>
              </div>
              <div class="detail-row">
                <div class="detail-item">
                  <label>Subject/Title</label>
                  <span>{{ selectedDocument.subject || selectedDocument.title }}</span>
                </div>
                <div class="detail-item">
                  <label>Date Received</label>
                  <span>{{ formatDate(selectedDocument.date_received || selectedDocument.created_at) }}</span>
                </div>
              </div>
              <div class="detail-row">
                <div class="detail-item">
                  <label>Sender/Origin</label>
                  <span>{{ selectedDocument.sender_name || selectedDocument.origin }}</span>
                </div>
                <div class="detail-item">
                  <label>Status</label>
                  <span :class="['status-badge', getStatusClass(selectedDocument.status)]">
                    {{ selectedDocument.status }}
                  </span>
                </div>
              </div>
              <div class="detail-row" v-if="selectedDocument.description">
                <div class="detail-item full-width">
                  <label>Description</label>
                  <span>{{ selectedDocument.description }}</span>
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
export default {
  name: "IncomingDocumentsList",
  props: {
    headerIcon: { type: String, default: "bi bi-inbox" },
    headerTitle: { type: String, default: "Incoming Documents" },
    headerSubtitle: {
      type: String,
      default: "Track and manage incoming document workflow",
    },
  },
  emits: ["process-document", "release-document", "view-document", "download-document", "create-document"],
  data() {
    return {
      activeTab: "in-progress",
      searchQuery: "",
      documentTypeFilter: "",
      perPage: 10,
      currentPage: 1,
      currentDateTime: "",
      dateTimeInterval: null,
      loading: false,
      showCreateModal: false,
      showViewModal: false,
      selectedDocument: null,
      creating: false,
      createForm: {
        tracking_number: "",
        document_type: "",
        subject: "",
        sender_name: "",
        date_received: "",
        description: "",
      },
      // ===== DUMMY DATA =====
      documents: [
        {
          id: 1,
          tracking_number: "TRK-2024-001",
          document_type: "Memorandum",
          subject: "Updated Office Guidelines for Q4 2024 Updated Office Guidelines for Q4 2024Updated Office Guidelines for Q4 2024 Updated Office Guidelines for Q4 2024 Updated Office Guidelines for Q4 2024 Updated Office Guidelines for Q4 2024 Updated Office Guidelines for Q4 2024",
          sender_name: "Office of the Director",
          date_received: "2024-01-15T09:30:00",
          description: "Revised office protocols and guidelines for the fourth quarter of fiscal year 2024.",
          status: "In-Progress"
        },
        {
          id: 2,
          tracking_number: "TRK-2024-002",
          document_type: "Letter",
          subject: "Community Partnership Proposal",
          sender_name: "Barangay San Antonio",
          date_received: "2024-01-16T14:15:00",
          description: "Proposal for environmental conservation partnership program.",
          status: "In-Progress"
        },
        {
          id: 3,
          tracking_number: "TRK-2024-003",
          document_type: "Report",
          subject: "Monthly Environmental Assessment - December 2023",
          sender_name: "Environmental Monitoring Division",
          date_received: "2024-01-17T11:00:00",
          description: "Comprehensive environmental assessment report for December 2023.",
          status: "In-Progress"
        },
        {
          id: 4,
          tracking_number: "TRK-2024-004",
          document_type: "Request",
          subject: "Equipment Procurement Request - Laboratory Division",
          sender_name: "Laboratory Services Section",
          date_received: "2024-01-18T08:45:00",
          description: "Request for procurement of new laboratory testing equipment.",
          status: "For-Release"
        },
        {
          id: 5,
          tracking_number: "TRK-2024-005",
          document_type: "Permit",
          subject: "Environmental Compliance Certificate - ABC Corp",
          sender_name: "Environmental Impact Assessment Section",
          date_received: "2024-01-19T10:30:00",
          description: "ECC application review and approval for ABC Corporation.",
          status: "For-Release"
        },
        {
          id: 6,
          tracking_number: "TRK-2024-006",
          document_type: "Memorandum",
          subject: "Staff Training Schedule - First Quarter 2024",
          sender_name: "Human Resources Department",
          date_received: "2024-01-20T13:00:00",
          description: "Training calendar and enrollment details for capacity building.",
          status: "For-Release"
        },
        {
          id: 7,
          tracking_number: "TRK-2024-007",
          document_type: "Certificate",
          subject: "Certificate of Compliance - XYZ Mining Corp",
          sender_name: "Mines and Geosciences Bureau",
          date_received: "2024-01-10T09:00:00",
          description: "Certificate of compliance for environmental safety standards.",
          status: "Released"
        },
        {
          id: 8,
          tracking_number: "TRK-2024-008",
          document_type: "Report",
          subject: "Annual Environmental Performance Report 2023",
          sender_name: "Planning and Policy Division",
          date_received: "2024-01-08T15:30:00",
          description: "Comprehensive annual report on agency environmental performance.",
          status: "Released"
        },
        {
          id: 9,
          tracking_number: "TRK-2024-009",
          document_type: "Letter",
          subject: "Thank You Letter - Tree Planting Event",
          sender_name: "Local Government Unit",
          date_received: "2024-01-12T11:20:00",
          description: "Acknowledgment for participation in community tree planting.",
          status: "Released"
        },
        {
          id: 10,
          tracking_number: "TRK-2024-010",
          document_type: "Permit",
          subject: "Wildlife Transport Permit - Rescue Center",
          sender_name: "Biodiversity Management Bureau",
          date_received: "2024-01-22T16:00:00",
          description: "Permit for transportation of rescued wildlife species.",
          status: "In-Progress"
        },
        {
          id: 11,
          tracking_number: "TRK-2024-011",
          document_type: "Request",
          subject: "Technical Assistance - Solid Waste Management",
          sender_name: "Municipality of San Miguel",
          date_received: "2024-01-23T10:45:00",
          description: "Request for technical assistance in waste management plan.",
          status: "In-Progress"
        },
        {
          id: 12,
          tracking_number: "TRK-2024-012",
          document_type: "Memorandum",
          subject: "Budget Allocation Guidelines FY 2024",
          sender_name: "Finance and Administrative Division",
          date_received: "2024-01-24T08:30:00",
          description: "Guidelines for budget allocation and utilization FY 2024.",
          status: "For-Release"
        },
        {
          id: 13,
          tracking_number: "TRK-2024-013",
          document_type: "Report",
          subject: "Water Quality Monitoring - January 2024",
          sender_name: "Water Quality Management Section",
          date_received: "2024-01-25T14:00:00",
          description: "Results of water quality testing from major river systems.",
          status: "For-Release"
        },
        {
          id: 14,
          tracking_number: "TRK-2024-014",
          document_type: "Certificate",
          subject: "Certificate of Recognition - Green School",
          sender_name: "Environmental Education Division",
          date_received: "2024-01-14T09:15:00",
          description: "Recognition for schools in environmental education.",
          status: "Released"
        },
        {
          id: 15,
          tracking_number: "TRK-2024-015",
          document_type: "Letter",
          subject: "Invitation to Environmental Summit 2024",
          sender_name: "Regional Director's Office",
          date_received: "2024-01-26T11:30:00",
          description: "Official invitation to the annual Environmental Summit.",
          status: "In-Progress"
        }
      ],
      // ===== END DUMMY DATA =====
      errors: {},
      formErrors: {
        show: false,
        message: "",
      },
      notification: {
        show: false,
        message: "",
        type: "success",
        timeout: null,
      },
    };
  },
  computed: {
    activeTabLabel() {
      const labels = {
        'in-progress': 'In Progress',
        'for-release': 'For Release',
        'released': 'Released'
      };
      return labels[this.activeTab] || this.activeTab;
    },
    documentTypes() {
      const types = new Set();
      this.documents.forEach(doc => {
        if (doc.document_type) {
          types.add(doc.document_type);
        }
      });
      return Array.from(types).sort();
    },
    filteredDocuments() {
      let filtered = this.documents.filter(doc => {
        let status = doc.status?.toLowerCase() || '';
        
        if (this.activeTab === 'in-progress') {
          if (!['in-progress', 'pending', 'processing'].includes(status)) return false;
        } else if (this.activeTab === 'for-release') {
          if (!['for-release', 'for release', 'approved'].includes(status)) return false;
        } else if (this.activeTab === 'released') {
          if (!['released', 'completed'].includes(status)) return false;
        }
        
        if (this.documentTypeFilter && doc.document_type !== this.documentTypeFilter) {
          return false;
        }
        
        return true;
      });

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(doc => 
          doc.tracking_number?.toLowerCase().includes(query) ||
          doc.document_type?.toLowerCase().includes(query) ||
          doc.subject?.toLowerCase().includes(query) ||
          doc.title?.toLowerCase().includes(query) ||
          doc.sender_name?.toLowerCase().includes(query) ||
          doc.origin?.toLowerCase().includes(query)
        );
      }

      return filtered;
    },
    paginatedDocuments() {
      const start = (this.currentPage - 1) * this.perPage;
      const end = start + this.perPage;
      return this.filteredDocuments.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredDocuments.length / this.perPage) || 1;
    },
    startItem() {
      return this.filteredDocuments.length === 0 ? 0 : (this.currentPage - 1) * this.perPage + 1;
    },
    endItem() {
      return Math.min(this.currentPage * this.perPage, this.filteredDocuments.length);
    },
    displayedPages() {
      const pages = [];
      const total = this.totalPages;
      const current = this.currentPage;
      const delta = 2;
      for (let i = 1; i <= total; i++) {
        if (
          i === 1 ||
          i === total ||
          (i >= current - delta && i <= current + delta)
        ) {
          pages.push(i);
        } else if (pages[pages.length - 1] !== "...") {
          pages.push("...");
        }
      }
      return pages;
    },
    inProgressCount() {
      return this.documents.filter(doc => {
        let status = doc.status?.toLowerCase() || '';
        return ['in-progress', 'pending', 'processing'].includes(status);
      }).length;
    },
    forReleaseCount() {
      return this.documents.filter(doc => {
        let status = doc.status?.toLowerCase() || '';
        return ['for-release', 'for release', 'approved'].includes(status);
      }).length;
    },
    releasedCount() {
      return this.documents.filter(doc => {
        let status = doc.status?.toLowerCase() || '';
        return ['released', 'completed'].includes(status);
      }).length;
    },
  },
  mounted() {
    // Comment out API call when using dummy data
    // this.getIncomingDocuments();
    this.updateDateTime();
    this.dateTimeInterval = setInterval(() => this.updateDateTime(), 1000);
  },
  beforeUnmount() {
    if (this.dateTimeInterval) clearInterval(this.dateTimeInterval);
    if (this.notification.timeout) clearTimeout(this.notification.timeout);
  },
  methods: {
    async getIncomingDocuments() {
      try {
        this.loading = true;
        const response = await axios.get("/dts_denr/api/incoming-documents");
        this.documents = response.data.data || response.data || [];
      } catch (error) {
        console.error("Error fetching incoming documents:", error);
        this.showNotification(
          "Failed to load incoming documents. Please try again.",
          "error"
        );
        this.documents = [];
      } finally {
        this.loading = false;
      }
    },
    switchTab(tab) {
      this.activeTab = tab;
      this.currentPage = 1;
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },
    changePerPage() {
      this.currentPage = 1;
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
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    },
    getStatusClass(status) {
      const statusLower = status?.toLowerCase() || '';
      if (['in-progress', 'pending', 'processing'].includes(statusLower)) {
        return 'status-warning';
      } else if (['for-release', 'for release', 'approved'].includes(statusLower)) {
        return 'status-info';
      } else if (['released', 'completed'].includes(statusLower)) {
        return 'status-success';
      }
      return 'status-default';
    },
    getCsrfToken() {
      const metaToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
      if (metaToken) return metaToken;

      const name = "XSRF-TOKEN=";
      const decodedCookie = decodeURIComponent(document.cookie);
      const cookies = decodedCookie.split(";");
      for (let i = 0; i < cookies.length; i++) {
        let c = cookies[i].trim();
        if (c.indexOf(name) === 0) {
          return c.substring(name.length, c.length);
        }
      }
      return "";
    },
    openCreateModal() {
      this.errors = {};
      this.formErrors.show = false;
      this.resetCreateForm();
      this.showCreateModal = true;
    },
    closeCreateModal(force = false) {
      if (this.creating && !force) return;
      this.showCreateModal = false;
      this.errors = {};
      this.formErrors.show = false;
      this.creating = false;
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
      this.formErrors.show = false;
    },
    validateCreateForm() {
      this.errors = {};
      this.formErrors.show = false;
      const requiredFields = [
        "tracking_number",
        "document_type",
        "subject",
        "sender_name",
        "date_received",
      ];
      const fieldLabels = {
        tracking_number: "Tracking Number",
        document_type: "Document Type",
        subject: "Subject/Title",
        sender_name: "Sender/Origin",
        date_received: "Date Received",
      };
      let valid = true;
      for (const field of requiredFields) {
        if (!this.createForm[field] || !this.createForm[field].toString().trim()) {
          this.errors[field] = `${fieldLabels[field]} is required.`;
          valid = false;
        }
      }
      if (!valid) {
        this.formErrors.show = true;
        this.formErrors.message = "Please fill in all required fields.";
      }
      return valid;
    },
    async submitCreateForm() {
      if (!this.validateCreateForm()) {
        this.showNotification(
          "Please fill in all required fields.",
          "error"
        );
        return;
      }

      this.creating = true;
      this.formErrors.show = false;
      
      // For dummy data: add document locally
      const newId = this.documents.length > 0 ? Math.max(...this.documents.map(d => d.id)) + 1 : 1;
      const newDocument = {
        id: newId,
        tracking_number: this.createForm.tracking_number.trim(),
        document_type: this.createForm.document_type,
        subject: this.createForm.subject.trim(),
        sender_name: this.createForm.sender_name.trim(),
        date_received: this.createForm.date_received,
        description: this.createForm.description.trim(),
        status: "In-Progress",
      };
      
      this.documents.push(newDocument);
      
      this.$emit("create-document", newDocument);

      this.showNotification(
        `Document "${newDocument.tracking_number}" created successfully!`,
        "success"
      );

      this.resetCreateForm();
      this.closeCreateModal(true);
      
      this.creating = false;
      
      /* 
      // Uncomment for real API call:
      const csrfToken = this.getCsrfToken();
      const payload = {
        tracking_number: this.createForm.tracking_number.trim(),
        document_type: this.createForm.document_type,
        subject: this.createForm.subject.trim(),
        sender_name: this.createForm.sender_name.trim(),
        date_received: this.createForm.date_received,
        description: this.createForm.description.trim(),
        status: "In-Progress",
      };

      try {
        const response = await fetch("/dts_denr/api/incoming-documents", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
          },
          body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
          if (response.status === 422) {
            const serverErrors = data.errors;
            this.errors = {};
            for (const field in serverErrors) {
              this.errors[field] = serverErrors[field][0];
            }
            this.formErrors.show = true;
            this.formErrors.message = "Please correct the highlighted errors.";
            throw new Error("Validation failed");
          }
          throw new Error(data.message || "Request failed");
        }

        this.$emit("create-document", data.data);

        this.showNotification(
          `Document "${payload.tracking_number}" created successfully!`,
          "success"
        );

        this.resetCreateForm();
        this.closeCreateModal(true);
        this.getIncomingDocuments();

      } catch (error) {
        if (error.message !== "Validation failed") {
          this.formErrors.show = true;
          this.formErrors.message = error.message || "An error occurred while creating document.";
          this.showNotification(
            error.message || "An error occurred while creating document.",
            "error"
          );
        }
      } finally {
        this.creating = false;
      }
      */
    },
    viewDocument(doc) {
      this.selectedDocument = doc;
      this.showViewModal = true;
      this.$emit("view-document", doc);
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedDocument = null;
    },
    processDocument(doc) {
      // Update status for dummy data
      doc.status = "For-Release";
      this.$emit("process-document", doc);
      this.showNotification(`Document ${doc.tracking_number} moved to For Release`, "success");
    },
    releaseDocument(doc) {
      // Update status for dummy data
      doc.status = "Released";
      this.$emit("release-document", doc);
      this.showNotification(`Document ${doc.tracking_number} has been released`, "success");
    },
    downloadDocument(doc) {
      this.$emit("download-document", doc);
    },
    showNotification(message, type = "success") {
      if (this.notification.timeout) clearTimeout(this.notification.timeout);
      this.notification.show = true;
      this.notification.message = message;
      this.notification.type = type;
      this.notification.timeout = setTimeout(() => {
        this.notification.show = false;
      }, 5000);
    },
    applyFilters() {
      this.currentPage = 1;
    },
    clearSearch() {
      this.searchQuery = "";
      this.currentPage = 1;
    },
    clearDocTypeFilter() {
      this.documentTypeFilter = "";
      this.currentPage = 1;
    },
    clearAllFilters() {
      this.searchQuery = "";
      this.documentTypeFilter = "";
      this.currentPage = 1;
    },
  },
};
</script>

<style scoped>
/* ===== CSS Variables ===== */
:root {
  --forest:     #1A4731;
  --leaf:       #2D6A4F;
  --mint:       #52B788;
  --gold:       #C9A84C;
  --gold-light: #F0D080;
  --paper:      #F4F9F6;
  --charcoal:   #1C2B24;
  --muted:      #5C7A6B;
  --border:     #B7D5C3;
  --white:      #FFFFFF;
  --error:      #C0392B;
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
  content: '';
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
  font-family: 'Inter', sans-serif;
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

.filter-tag i {
  font-size: 11px;
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

/* Results Summary */
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
  from { opacity: 0; }
  to { opacity: 1; }
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

/* ===== MODAL BODY ===== */
.modal-body-enhanced {
  padding: 24px;
  background: #f9fafb;
}

/* ===== ERROR MESSAGE ===== */
.error-msg {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #FEF0EF;
  border: 1px solid #F5C6C3;
  border-radius: 7px;
  padding: 10px 14px;
  font-size: 12px;
  color: #C0392B;
  margin-bottom: 20px;
  font-weight: 500;
}

/* ===== FORM LABELS ===== */
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

/* ===== INPUT WRAPPER ===== */
.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  color: #5C7A6B;
  pointer-events: none;
  display: flex;
  z-index: 2;
}

.form-input {
  width: 100%;
  height: 46px;
  padding: 0 14px 0 42px;
  border: 1.5px solid #B7D5C3;
  border-radius: 8px;
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: #1C2B24;
  background: #F4F9F6;
  transition: border-color .2s, box-shadow .2s, background .2s;
  outline: none;
}

.form-input::placeholder {
  color: #B0C4B8;
}

.form-input:focus {
  border-color: #2D6A4F;
  background: #FFFFFF;
  box-shadow: 0 0 0 3px rgba(45,106,79,0.1);
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
  background: #FFF5F5 !important;
}

.is-invalid:focus {
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
}

.invalid-feedback {
  color: #dc2626;
  font-size: 0.8rem;
  margin-top: 4px;
}

/* ===== MODAL ACTIONS ===== */
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
  font-family: 'Inter', sans-serif;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-save {
  background: linear-gradient(135deg, #2D6A4F 0%, #1A4731 100%);
  color: white;
  border: none;
  padding: 10px 22px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  box-shadow: 0 4px 18px rgba(26,71,49,0.3);
}

.btn-save:hover {
  box-shadow: 0 6px 24px rgba(26,71,49,0.38);
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

/* Document Details */
.document-details {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item.full-width {
  grid-column: 1 / -1;
}

.detail-item label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-item span {
  font-size: 14px;
  color: #1e293b;
  font-weight: 500;
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-warning {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fbbf24;
}

.status-info {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #60a5fa;
}

.status-success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #34d399;
}

.status-default {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
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
  font-family: 'Courier New', monospace;
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

/* ===== PAGINATION ===== */
.pagination-wrapper {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  gap: 10px;
}

.pagination-info {
  font-size: 13px;
  color: #6c757d;
  white-space: nowrap;
  font-weight: 500;
}

.pagination-buttons {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.page-btn {
  border: 2px solid #e5e7eb;
  background: white;
  color: #495057;
  padding: 6px 12px;
  cursor: pointer;
  border-radius: 6px;
  font-size: 13px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  font-weight: 600;
}

.page-btn:hover:not(:disabled) {
  background: #f0fdf4;
  border-color: #2d6a4f;
  color: #2d6a4f;
}

.page-btn.active {
  background: #2d6a4f;
  color: white;
  border-color: #2d6a4f;
  box-shadow: 0 2px 8px rgba(45, 106, 79, 0.3);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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
  to { transform: rotate(360deg); }
}

/* ===== RESPONSIVE ===== */
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
}

@media (max-width: 576px) {
  .pagination-wrapper {
    flex-direction: column;
    align-items: center;
  }
  .pagination-buttons {
    justify-content: center;
  }
  .detail-row {
    grid-template-columns: 1fr;
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