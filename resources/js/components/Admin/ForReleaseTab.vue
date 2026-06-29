<!-- resources/js/components/ForReleaseTab.vue -->
<template>
  <div>
    <!-- Filter Controls -->
    <div class="filter-controls">
      <div class="search-filter-row">
        <div class="search-box-wrapper">
          <div class="search-box">
            <i class="bi bi-search search-icon"></i>
            <input
              v-model="searchQuery"
              type="text"
              class="search-input"
              placeholder="Search by tracking no, subject, sender..."
              @input="applyFilters"
            />
            <button
              v-if="searchQuery"
              class="search-clear-btn"
              @click="clearSearch"
            >
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
        </div>

        <div class="per-page-wrapper">
          <span class="per-page-label">Show</span>
          <select
            v-model="perPage"
            class="per-page-select"
            @change="changePerPage"
          >
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
            <select
              v-model="docTypeFilter"
              class="filter-select"
              @change="applyFilters"
            >
              <option value="">All Document Types</option>
              <option v-for="type in documentTypes" :key="type" :value="type">
                {{ type }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div v-if="searchQuery || docTypeFilter" class="active-filters">
        <span class="active-filters-label">Active Filters:</span>
        <span v-if="searchQuery" class="filter-tag">
          <i class="bi bi-search"></i> "{{ searchQuery }}"
          <button class="filter-tag-close" @click="clearSearch">
            <i class="bi bi-x"></i>
          </button>
        </span>
        <span v-if="docTypeFilter" class="filter-tag">
          <i class="bi bi-funnel"></i> {{ docTypeFilter }}
          <button class="filter-tag-close" @click="clearDocTypeFilter">
            <i class="bi bi-x"></i>
          </button>
        </span>
        <button class="clear-all-filters" @click="clearAllFilters">
          Clear All
        </button>
      </div>

      <div class="results-summary">
        <span class="results-count">{{ for_release.total }}</span>
        document(s) found in <strong>For Release Document</strong>
      </div>
    </div>

    <!-- Table -->
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
          <tr v-if="loading && for_release.data.length === 0">
            <td colspan="8" class="text-center">
              <div class="loader-spinner"></div>
              Loading...
            </td>
          </tr>
          <tr v-else-if="!loading && for_release.data.length === 0">
            <td colspan="8" class="text-center">
              <div class="empty-state">
                <i
                  class="bi bi-inbox"
                  style="font-size: 3rem; color: #9ca3af"
                ></i>
                <p class="mt-2 text-muted">
                  {{
                    searchQuery || docTypeFilter
                      ? "No documents match your filters"
                      : "No For Release Data Found"
                  }}
                </p>
              </div>
            </td>
          </tr>
          <tr v-for="(doc, index) in for_release.data" :key="doc.id">
            <td class="text-center">
              <span class="row-number">
                {{
                  (for_release.current_page - 1) * for_release.per_page +
                  index +
                  1
                }}
              </span>
            </td>
            <td>
              <span class="tracking-number">{{ doc.tracking_number }}</span>
            </td>
            <td>{{ doc.document_classification }}</td>
            <td>
              <span class="doc-type-badge">
                {{ doc.document_type?.document_type_name || doc.document_type }}
              </span>
            </td>
            <td>
              <div class="subject-text">{{ doc.subject }}</div>
            </td>
            <td>
              <div class="sender-text">
                <i class="bi bi-person-circle sender-icon"></i>
                {{ doc.sender_name }}
              </div>
            </td>
            <td>
              <div class="date-received">
                <i class="bi bi-calendar3 date-icon"></i>
                {{ formatDate(doc.date_receive) }} At
                {{ formatTime(doc.time_receive) }}
              </div>
            </td>
            <td>
              <div class="action-buttons">
                <button
                  class="btn-action btn-view"
                  title="View Details"
                  @click="viewDocument(doc)"
                >
                  <i class="bi bi-eye"></i>
                </button>
                <button
                  class="btn-action btn-forward"
                  title="Release Document"
                  @click="openReleaseModal(doc)"
                >
                  <i class="bi bi-check-circle"></i>
                </button>
                <button
                  class="btn-action btn-forward"
                  title="Forward Office"
                  @click="openForwardModal(doc)"
                >
                  <i class="bi bi-arrow-right-circle"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <PaginationComponent
      :current-page="Number(for_release.current_page)"
      :total-pages="Number(for_release.last_page)"
      :total="Number(for_release.total)"
      :per-page="Number(for_release.per_page)"
      :from="Number(for_release.from)"
      :to="Number(for_release.to)"
      @page-change="changePage"
    />

    <!-- FORWARD DOCUMENT MODAL -->
    <div
      v-if="showForwardModal"
      class="modal-overlay"
      @click.self="closeForwardModal"
    >
      <div class="modal-dialog enhanced-modal" style="max-width: 700px">
        <div class="modal-content square-modal">
          <div
            class="modal-header-enhanced square-header"
            style="background: #2563eb"
          >
            <div class="d-flex align-items-center">
              <div
                class="modal-icon-wrapper square-icon"
                style="background: rgba(255, 255, 255, 0.2)"
              >
                <i class="bi bi-arrow-right-circle"></i>
              </div>
              <div>
                <h5 class="modal-title">Forward Document</h5>
              </div>
            </div>
            <button
              type="button"
              class="btn-close-custom square-close"
              :disabled="forwarding"
              @click="closeForwardModal"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body-enhanced">
            <div v-if="forwardError" class="error-msg" role="alert">
              <svg
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
              >
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <circle
                  cx="12"
                  cy="16.5"
                  r="0.7"
                  fill="currentColor"
                  stroke="none"
                />
              </svg>
              <span>{{ forwardError }}</span>
            </div>

            <div class="forward-doc-info">
              <div class="forward-doc-row">
                <span class="forward-doc-label">Tracking Number:</span>
                <span class="forward-doc-value">{{
                  forwardDocument?.tracking_number || "N/A"
                }}</span>
              </div>
              <div class="forward-doc-row">
                <span class="forward-doc-label">Document Type:</span>
                <span class="doc-type-badge">
                  {{
                    forwardDocument?.document_type?.document_type_name ||
                    forwardDocument?.document_type ||
                    "N/A"
                  }}
                </span>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label-enhanced">
                Select Destination Office(s)
                <span class="required-star">*</span>
              </label>

              <div v-if="officesLoading" class="text-center py-4">
                <div class="loader-spinner"></div>
                <p class="mt-2 text-muted">Loading offices...</p>
              </div>

              <div v-else-if="offices.length === 0" class="text-center py-4">
                <i
                  class="bi bi-building"
                  style="font-size: 2rem; color: #9ca3af"
                ></i>
                <p class="mt-2 text-muted">No offices available</p>
              </div>

              <div v-else class="office-list-container">
                <div
                  v-for="office in offices"
                  :key="office.id"
                  class="office-select-item"
                  :class="{ selected: isOfficeSelected(office.id) }"
                  @click="toggleOfficeSelection(office)"
                >
                  <div class="office-checkbox">
                    <i
                      :class="
                        isOfficeSelected(office.id)
                          ? 'bi bi-check-square-fill'
                          : 'bi bi-square'
                      "
                    ></i>
                  </div>
                  <div class="office-icon-wrapper">
                    <i class="bi bi-building"></i>
                  </div>
                  <div class="office-details">
                    <span class="office-name-text">{{
                      office.sub_office_name
                    }}</span>
                    <span v-if="office.office_code" class="office-code">{{
                      office.office_code
                    }}</span>
                  </div>
                  <div
                    v-if="isOfficeSelected(office.id)"
                    class="selected-indicator"
                  >
                    <i class="bi bi-check-circle-fill"></i>
                  </div>
                </div>
              </div>
            </div>

            <div
              v-if="selectedOffices.length > 0"
              class="selected-offices-summary mt-3"
            >
              <div class="selected-offices-header">
                <i class="bi bi-check2-all"></i>
                <span>Selected Offices ({{ selectedOffices.length }})</span>
              </div>
              <div class="selected-offices-list">
                <span
                  v-for="office in selectedOffices"
                  :key="office.id"
                  class="selected-office-tag"
                >
                  <i class="bi bi-building"></i>
                  {{ office.sub_office_name }}
                  <button
                    class="remove-office-btn"
                    :disabled="forwarding"
                    @click="removeOffice(office.id)"
                  >
                    <i class="bi bi-x"></i>
                  </button>
                </span>
              </div>
            </div>

            <div class="modal-actions">
              <button
                type="button"
                class="btn btn-outline-secondary square-btn"
                :disabled="forwarding"
                @click="clearForwardForm"
              >
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
              </button>
              <div class="d-flex gap-3">
                <button
                  type="button"
                  class="btn btn-light square-btn"
                  :disabled="forwarding"
                  @click="closeForwardModal"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  class="btn btn-save square-btn"
                  style="background: linear-gradient(135deg, #2563eb, #1d4ed8)"
                  :disabled="forwarding || selectedOffices.length === 0"
                  @click="submitForwardDocument"
                >
                  <span
                    v-if="forwarding"
                    class="spinner-border spinner-border-sm me-1"
                    role="status"
                  ></span>
                  <i v-else class="bi bi-send-check me-1"></i>
                  {{ forwarding ? "Forwarding..." : "Forward Document" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RELEASE DOCUMENT MODAL -->
    <div
      v-if="showReleaseModal"
      class="modal-overlay"
      @click.self="closeReleaseModal"
    >
      <div class="modal-dialog enhanced-modal release-modal">
        <div class="modal-content square-modal">
          <div
            class="modal-header-enhanced square-header"
            style="background: linear-gradient(135deg, #059669, #047857)"
          >
            <div class="d-flex align-items-center">
              <div
                class="modal-icon-wrapper square-icon"
                style="background: rgba(255, 255, 255, 0.2)"
              >
                <i class="bi bi-check-circle"></i>
              </div>
              <div>
                <h5 class="modal-title">Release Document</h5>
                <small class="modal-subtitle">Confirm document release</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close-custom square-close"
              :disabled="releasing"
              @click="closeReleaseModal"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body-enhanced release-modal-body">
            <div v-if="releaseError" class="error-msg" role="alert">
              <svg
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
              >
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <circle
                  cx="12"
                  cy="16.5"
                  r="0.7"
                  fill="currentColor"
                  stroke="none"
                />
              </svg>
              <span>{{ releaseError }}</span>
            </div>

            <!-- Document Info - Responsive Grid -->
            <div class="release-doc-info-grid">
              <div class="release-info-card">
                <div class="release-info-label">
                  <i class="bi bi-upc-scan"></i>
                  Tracking Number
                </div>
                <div class="release-info-value tracking-value">
                  {{ releaseDocument?.tracking_number || "N/A" }}
                </div>
              </div>
              <div class="release-info-card">
                <div class="release-info-label">
                  <i class="bi bi-file-earmark-text"></i>
                  Document Type
                </div>
                <div class="release-info-value">
                  <span class="doc-type-badge">
                    {{
                      releaseDocument?.document_type?.document_type_name ||
                      releaseDocument?.document_type ||
                      "N/A"
                    }}
                  </span>
                </div>
              </div>
              <div class="release-info-card">
                <div class="release-info-label">
                  <i class="bi bi-tags"></i>
                  Document Classification
                </div>
                <div class="release-info-value">
                  {{ releaseDocument?.document_classification || "N/A" }}
                </div>
              </div>
              <div class="release-info-card">
                <div class="release-info-label">
                  <i class="bi bi-journal-text"></i>
                  Subject / Title
                </div>
                <div class="release-info-value subject-value">
                  {{
                    releaseDocument?.subject || releaseDocument?.title || "N/A"
                  }}
                </div>
              </div>
            </div>

            <!-- Release Date & Time -->
            <div class="release-datetime-section">
              <label class="form-label-enhanced">
                <i class="bi bi-calendar-check me-2"></i>
                Release Date & Time <span class="required-star">*</span>
              </label>
              <div class="release-datetime-grid">
                <div class="form-group">
                  <label class="form-label-sm">Date</label>
                  <input
                    type="date"
                    class="form-input"
                    v-model="releaseForm.date"
                    :disabled="releasing"
                    required
                  />
                </div>
                <div class="form-group">
                  <label class="form-label-sm">Time</label>
                  <input
                    type="time"
                    class="form-input"
                    v-model="releaseForm.time"
                    :disabled="releasing"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- Upload Attachment -->
            <div class="release-upload-section">
              <label class="form-label-enhanced">
                <i class="bi bi-cloud-upload me-2"></i>
                Upload Released Attachment <span class="required-star">*</span>
              </label>

              <div
                class="release-upload-area"
                :class="{
                  'drag-over': isDragging,
                  'has-file': releaseForm.attachment,
                }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
                @click="triggerFileInput"
              >
                <div class="release-upload-content">
                  <div
                    v-if="!releaseForm.attachment"
                    class="upload-placeholder"
                  >
                    <div class="upload-icon-wrapper">
                      <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <h6 class="upload-title">
                      Drop your file here or click to browse
                    </h6>
                    <p class="upload-subtitle">
                      Supports PDF files only (Max 10MB)
                    </p>
                  </div>
                  <div v-else class="upload-file-preview">
                    <div class="upload-file-icon">
                      <i class="bi bi-file-earmark-pdf"></i>
                    </div>
                    <div class="upload-file-info">
                      <span class="upload-file-name">{{
                        releaseForm.attachment.name
                      }}</span>
                      <span class="upload-file-size">{{
                        formatFileSize(releaseForm.attachment.size)
                      }}</span>
                    </div>
                    <button
                      type="button"
                      class="upload-remove-btn"
                      :disabled="releasing"
                      @click.stop="removeAttachment"
                    >
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                </div>
                <input
                  ref="fileInput"
                  type="file"
                  class="release-file-input"
                  accept=".pdf,application/pdf"
                  :disabled="releasing"
                  @change="handleFileChange"
                />
              </div>
              <div
                v-if="uploadProgress > 0 && uploadProgress < 100"
                class="upload-progress"
              >
                <div
                  class="progress-bar"
                  :style="{ width: uploadProgress + '%' }"
                ></div>
                <span class="progress-text">{{ uploadProgress }}%</span>
              </div>
            </div>

            <!-- Remarks -->
            <div class="release-remarks-section">
              <label class="form-label-enhanced">
                <i class="bi bi-chat-left-text me-2"></i>
                Remarks (Optional)
              </label>
              <textarea
                class="form-input form-textarea"
                v-model="releaseForm.remarks"
                :disabled="releasing"
                rows="3"
                placeholder="Enter any additional remarks for this release..."
              ></textarea>
            </div>

            <!-- Modal Actions -->
            <div class="modal-actions">
              <button
                type="button"
                class="btn btn-outline-secondary square-btn"
                :disabled="releasing"
                @click="closeReleaseModal"
              >
                <i class="bi bi-x-lg me-1"></i> Cancel
              </button>
              <button
                type="button"
                class="btn btn-save square-btn"
                style="background: linear-gradient(135deg, #059669, #047857)"
                :disabled="
                  releasing ||
                  !releaseForm.date ||
                  !releaseForm.time ||
                  !releaseForm.attachment
                "
                @click="submitReleaseDocument"
              >
                <span
                  v-if="releasing"
                  class="spinner-border spinner-border-sm me-1"
                  role="status"
                ></span>
                <i v-else class="bi bi-check-circle me-1"></i>
                {{ releasing ? "Releasing..." : "Confirm Release" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DOCUMENT VIEWER MODAL -->
    <div
      v-if="showViewModal"
      class="modal-overlay"
      @click.self="closeViewModal"
    >
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
                  <span class="tracking-badge">{{
                    selectedDocument?.tracking_number
                  }}</span>
                  <span
                    :class="[
                      'status-pill',
                      getStatusClass(selectedDocument?.status),
                    ]"
                  >
                    {{ selectedDocument?.status }}
                  </span>
                </small>
              </div>
            </div>
            <div class="header-actions">
              <button
                type="button"
                class="btn-close-custom square-close"
                @click="closeViewModal"
              >
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>

          <div class="modal-body-enhanced document-viewer-body">
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
                @click="viewerActiveTab = 'history'"
              >
                <i class="bi bi-clock-history"></i>
                <span>Route History</span>
              </button>
              <button
                class="viewer-tab-btn"
                :class="{ active: viewerActiveTab === 'attachments' }"
                @click="viewerActiveTab = 'attachments'"
              >
                <i class="bi bi-paperclip"></i>
                <span>Attachments</span>
              </button>
            </div>

            <!-- Document Information Tab -->
            <div
              v-show="viewerActiveTab === 'details'"
              class="document-viewer-layout"
            >
              <div class="details-panel">
                <div class="details-panel-header">
                  <i class="bi bi-info-circle-fill"></i>
                  <span>Document Information</span>
                </div>

                <div v-if="selectedDocument" class="details-content">
                  <div class="detail-card">
                    <div class="detail-icon-wrapper">
                      <i class="bi bi-upc-scan"></i>
                    </div>
                    <div class="detail-info">
                      <label>Tracking Number</label>
                      <span class="tracking-number-large">{{
                        selectedDocument.tracking_number
                      }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper type-icon">
                      <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="detail-info">
                      <label>Document Type</label>
                      <span class="doc-type-badge-large">
                        {{
                          selectedDocument.document_type?.document_type_name ||
                          selectedDocument.document_type
                        }}
                      </span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper subject-icon">
                      <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="detail-info">
                      <label>Subject / Title</label>
                      <span class="detail-value">{{
                        selectedDocument.subject || selectedDocument.title
                      }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper sender-icon-card">
                      <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="detail-info">
                      <label>Sender / Origin</label>
                      <span class="detail-value">{{
                        selectedDocument.sender_name || selectedDocument.origin
                      }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper date-icon-card">
                      <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="detail-info">
                      <label>Date Received</label>
                      <span class="detail-value">
                        {{
                          formatDate(
                            selectedDocument.date_receive ||
                              selectedDocument.date_received ||
                              selectedDocument.created_at
                          )
                        }}
                        <small
                          v-if="selectedDocument.time_receive"
                          class="time-text"
                        >
                          at {{ formatTime(selectedDocument.time_receive) }}
                        </small>
                      </span>
                    </div>
                  </div>

                  <div
                    v-if="selectedDocument.description"
                    class="detail-card description-card"
                  >
                    <div class="detail-icon-wrapper desc-icon">
                      <i class="bi bi-blockquote-right"></i>
                    </div>
                    <div class="detail-info">
                      <label>Description</label>
                      <p class="detail-value description-text">
                        {{ selectedDocument.description }}
                      </p>
                    </div>
                  </div>

                  <div class="detail-card meta-card">
                    <div class="detail-icon-wrapper meta-icon">
                      <i class="bi bi-info-circle"></i>
                    </div>
                    <div class="detail-info">
                      <label>Status</label>
                      <span
                        :class="[
                          'status-badge',
                          getStatusClass(selectedDocument?.status),
                        ]"
                      >
                        {{ selectedDocument?.status || "Unknown" }}
                      </span>
                    </div>
                  </div>

                  <div
                    v-if="selectedDocument?.date_released"
                    class="detail-card release-date-card"
                  >
                    <div class="detail-icon-wrapper release-icon">
                      <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="detail-info">
                      <label>Date Released</label>
                      <span class="detail-value">
                        {{ formatDate(selectedDocument.date_released) }}
                        <small
                          v-if="selectedDocument.time_released"
                          class="time-text"
                        >
                          at {{ formatTime(selectedDocument.time_released) }}
                        </small>
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- PDF Panel -->
              <div class="pdf-panel">
                <div class="pdf-panel-header">
                  <div class="pdf-panel-title">
                    <i class="bi bi-file-pdf"></i>
                    <span>Document Preview</span>
                  </div>
                  <div class="pdf-controls">
                    <button
                      class="btn-pdf-control"
                      title="Zoom In"
                      :disabled="pdfLoadError"
                      @click="zoomIn"
                    >
                      <i class="bi bi-zoom-in"></i>
                    </button>
                    <button
                      class="btn-pdf-control"
                      title="Zoom Out"
                      :disabled="pdfLoadError"
                      @click="zoomOut"
                    >
                      <i class="bi bi-zoom-out"></i>
                    </button>
                  </div>
                </div>

                <div class="pdf-viewer-wrapper">
                  <div v-if="pdfLoading && !pdfLoadError" class="pdf-state">
                    <div class="pdf-loader-animation">
                      <div class="pdf-loader-icon">
                        <i class="bi bi-file-pdf"></i>
                      </div>
                      <div class="loader-spinner"></div>
                    </div>
                    <p class="pdf-state-text">Loading document preview...</p>
                  </div>

                  <iframe
                    v-show="!pdfLoading && !pdfLoadError"
                    :src="getPdfUrl(selectedDocument)"
                    class="pdf-iframe"
                    :style="{
                      width: `${100 / pdfZoom}%`,
                      height: `${pdfViewerHeight}px`,
                    }"
                    frameborder="0"
                    @load="onPdfLoaded"
                    @error="handlePdfError"
                  ></iframe>

                  <div v-if="pdfLoadError" class="pdf-state pdf-error">
                    <i
                      class="bi bi-file-earmark-x"
                      style="font-size: 4rem; color: #ef4444"
                    ></i>
                    <h5 class="mt-3">PDF Not Available</h5>
                    <p class="text-muted">
                      The attachment could not be loaded or doesn't exist.
                    </p>
                    <button
                      class="btn btn-outline-secondary btn-sm mt-3"
                      @click="retryPdfLoad"
                    >
                      <i class="bi bi-arrow-repeat me-1"></i> Retry
                    </button>
                  </div>
                </div>

                <div v-if="!pdfLoadError" class="pdf-footer">
                  <span class="pdf-zoom-level"
                    >Zoom: {{ Math.round(pdfZoom * 100) }}%</span
                  >
                  <span v-if="selectedDocument" class="pdf-page-info">
                    File: {{ selectedDocument.tracking_number }}_attachment.pdf
                  </span>
                </div>
              </div>
            </div>

            <!-- Route History Tab -->
            <div
              v-show="viewerActiveTab === 'history'"
              class="route-history-panel"
            >
              <div class="route-history-header">
                <div class="route-history-title">
                  <i class="bi bi-clock-history"></i>
                  <span>Document Route History</span>
                </div>
                <div
                  v-if="selectedDocument?.document_route?.length > 0"
                  class="route-history-badge"
                >
                  <i class="bi bi-arrow-left-right"></i>
                  <span>
                    {{ selectedDocument.document_route.length }}
                    Route{{
                      selectedDocument.document_route.length !== 1 ? "s" : ""
                    }}
                  </span>
                </div>
              </div>

              <div class="route-history-content">
                <div v-if="routeHistoryLoading" class="route-state-box">
                  <div class="loader-spinner"></div>
                  <p class="mt-3 text-muted">Loading route history...</p>
                </div>

                <div
                  v-else-if="
                    !selectedDocument?.document_route ||
                    selectedDocument.document_route.length === 0
                  "
                  class="route-state-box"
                >
                  <div class="empty-icon-wrapper">
                    <i class="bi bi-signpost-2"></i>
                  </div>
                  <h5 class="mt-3">No Route History</h5>
                  <p class="text-muted">
                    This document hasn't been routed yet.
                  </p>
                </div>

                <div v-else class="timeline-container">
                  <div
                    v-for="(route, rIndex) in selectedDocument.document_route"
                    :key="rIndex"
                    class="timeline-item"
                    :class="{
                      'is-last':
                        rIndex === selectedDocument.document_route.length - 1,
                    }"
                  >
                    <div
                      class="timeline-node"
                      :class="getRouteNodeClass(route.status)"
                    >
                      <i :class="getRouteStatusIcon(route.status)"></i>
                    </div>

                    <div
                      class="timeline-card"
                      :class="{ 'active-card': isRouteActive(route) }"
                    >
                      <div class="timeline-card-header">
                        <div class="office-info">
                          <i class="bi bi-building-fill"></i>
                          <div class="office-text">
                            <span class="office-name">
                              {{
                                route.office?.sub_office_name ||
                                route.sub_office_name ||
                                "Unknown Office"
                              }}
                            </span>
                          </div>
                        </div>
                        <span
                          class="route-status-badge"
                          :class="getRouteStatusClass(route.status)"
                        >
                          <i :class="getRouteStatusIcon(route.status)"></i>
                          {{ route.status || "PENDING" }}
                        </span>
                      </div>

                      <div class="timeline-card-body">
                        <div class="info-grid-enhanced">
                          <div
                            v-if="route.date_receive"
                            class="info-card received-card"
                          >
                            <div class="info-card-body">
                              <div class="info-field">
                                <div class="field-icon">
                                  <i class="bi bi-calendar-event"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Received On</span>
                                  <span class="field-value received-date">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ formatDateTime(route.date_receive) }}
                                  </span>
                                </div>
                              </div>

                              <div
                                v-if="
                                  route.received_by || route.received_by_name
                                "
                                class="info-field"
                              >
                                <div class="field-icon">
                                  <i class="bi bi-person-check"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Received By</span>
                                  <span class="field-value received-by">
                                    <template v-if="route.received_by">
                                      {{ route.received_by.firstname || "" }}
                                      {{
                                        route.received_by.middlename
                                          ? route.received_by.middlename + " "
                                          : ""
                                      }}
                                      {{ route.received_by.lastname || "" }}
                                    </template>
                                    <template
                                      v-else-if="route.received_by_name"
                                    >
                                      {{ route.received_by_name }}
                                    </template>
                                  </span>
                                </div>
                              </div>

                              <div
                                v-if="route.remarks"
                                class="info-field remarks-field"
                              >
                                <div class="field-icon remarks-icon">
                                  <i class="bi bi-chat-left-text"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Remarks</span>
                                  <span class="field-value remarks-text">{{
                                    route.remarks
                                  }}</span>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div
                            v-if="route.date_document_out"
                            class="info-card completed-card"
                          >
                            <div class="info-card-header">
                              <div class="info-card-icon completed-icon">
                                <i class="bi bi-check2-all"></i>
                              </div>
                              <span class="info-card-title"
                                >Completion Information</span
                              >
                              <span
                                v-if="route.status === 'Completed'"
                                class="info-card-status completed-status"
                              >
                                <i class="bi bi-check-circle-fill"></i> Done
                              </span>
                            </div>

                            <div class="info-card-body">
                              <div class="info-field">
                                <div class="field-icon">
                                  <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Completed On</span>
                                  <span class="field-value completed-date">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{
                                      formatDateTime(route.date_document_out)
                                    }}
                                  </span>
                                </div>
                              </div>

                              <div
                                v-if="
                                  route.completed_by || route.completed_by_name
                                "
                                class="info-field"
                              >
                                <div class="field-icon">
                                  <i class="bi bi-person-check"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Completed By</span>
                                  <span class="field-value">
                                    <template v-if="route.completed_by">
                                      {{ route.completed_by.firstname || "" }}
                                      {{
                                        route.completed_by.middlename
                                          ? route.completed_by.middlename + " "
                                          : ""
                                      }}
                                      {{ route.completed_by.lastname || "" }}
                                    </template>
                                    <template
                                      v-else-if="route.completed_by_name"
                                    >
                                      {{ route.completed_by_name }}
                                    </template>
                                  </span>
                                </div>
                              </div>

                              <div v-if="route.remarks" class="info-field">
                                <div class="field-icon notes-icon">
                                  <i class="bi bi-sticky"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label"
                                    >Completion Notes</span
                                  >
                                  <span class="field-value notes-text">{{
                                    route.remarks
                                  }}</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div
                          v-if="route.from_office || route.to_office"
                          class="flow-section-enhanced"
                        >
                          <div class="flow-container">
                            <div
                              v-if="route.from_office"
                              class="flow-node origin-node"
                            >
                              <div class="flow-node-icon">
                                <i class="bi bi-box-arrow-right"></i>
                              </div>
                              <div class="flow-node-content">
                                <span class="flow-node-label">From</span>
                                <span class="flow-node-value">{{
                                  route.from_office
                                }}</span>
                              </div>
                            </div>

                            <div
                              v-if="route.from_office && route.to_office"
                              class="flow-arrow-enhanced"
                            >
                              <div class="arrow-line"></div>
                              <div class="arrow-icon">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                              </div>
                              <div class="arrow-line"></div>
                            </div>

                            <div
                              v-if="route.to_office"
                              class="flow-node destination-node"
                            >
                              <div class="flow-node-icon">
                                <i class="bi bi-box-arrow-in-left"></i>
                              </div>
                              <div class="flow-node-content">
                                <span class="flow-node-label">To</span>
                                <span class="flow-node-value">{{
                                  route.to_office
                                }}</span>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div
                          v-if="
                            !route.date_receive &&
                            !route.date_document_out &&
                            !route.from_office &&
                            !route.to_office
                          "
                          class="text-center py-3 text-muted"
                        >
                          <i
                            class="bi bi-hourglass-split"
                            style="font-size: 1.5rem"
                          ></i>
                          <p class="mt-2 mb-0" style="font-size: 0.85rem">
                            Awaiting action
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Attachments Tab -->
            <div
              v-show="viewerActiveTab === 'attachments'"
              class="attachments-panel"
            >
              <div class="attachments-header">
                <div class="attachments-title">
                  <i class="bi bi-paperclip"></i>
                  <span>Document Attachments</span>
                </div>
                <span class="attachments-count">
                  {{ getAttachmentsCount() }} file(s)
                </span>
              </div>

              <div class="attachments-content">
                <!-- Draft Attachment -->
                <div
                  v-if="selectedDocument?.draft_attachment"
                  class="attachment-item"
                  @click="openAttachmentViewer('draft')"
                >
                  <div class="attachment-icon-wrapper">
                    <i class="bi bi-file-earmark-pdf"></i>
                  </div>
                  <div class="attachment-details">
                    <div class="attachment-name">
                      {{ selectedDocument.tracking_number }}_draft.pdf
                      <span class="attachment-badge draft-badge">Draft</span>
                    </div>
                    <div class="attachment-meta">
                      <span class="attachment-size">PDF Document</span>
                      <span class="attachment-separator">•</span>
                      <span class="attachment-date"
                        >Uploaded:
                        {{ formatDate(selectedDocument.created_at) }}</span
                      >
                    </div>
                  </div>
                  <div class="attachment-actions" @click.stop>
                    <button
                      class="btn-attachment-view"
                      title="View"
                      @click="openAttachmentViewer('draft')"
                    >
                      <i class="bi bi-eye"></i>
                    </button>
                    <a
                      :href="
                        getAttachmentUrl(selectedDocument.draft_attachment)
                      "
                      download
                      class="btn-attachment-download"
                      title="Download"
                    >
                      <i class="bi bi-download"></i>
                    </a>
                  </div>
                </div>

                <!-- Acted Documents - Loop through acted_documents -->
                <div
                  v-for="(actedDoc, index) in getActedDocuments()"
                  :key="'acted-' + index"
                  class="attachment-item"
                  @click="openActedDocumentViewer(actedDoc)"
                >
                  <div class="attachment-icon-wrapper">
                    <i class="bi bi-file-earmark-pdf"></i>
                  </div>
                  <div class="attachment-details">
                    <div class="attachment-name">
                      {{ getActedFileName(actedDoc) }}
                      <span class="attachment-badge acted-badge">Acted</span>
                    </div>
                    <div class="attachment-meta">
                      <span class="attachment-size">PDF Document</span>
                      <span class="attachment-separator">•</span>
                      <span class="attachment-date">
                        {{
                          formatDate(
                            actedDoc.created_at || actedDoc.date_created
                          )
                        }}
                      </span>
                      <span v-if="actedDoc.remarks" class="attachment-separator"
                        >•</span
                      >
                      <span
                        v-if="actedDoc.remarks"
                        class="attachment-remarks"
                        >{{ actedDoc.remarks }}</span
                      >
                    </div>
                  </div>
                  <div class="attachment-actions" @click.stop>
                    <button
                      class="btn-attachment-view"
                      title="View"
                      @click="openActedDocumentViewer(actedDoc)"
                    >
                      <i class="bi bi-eye"></i>
                    </button>
                    <a
                      :href="getActedDocumentUrl(actedDoc)"
                      download
                      class="btn-attachment-download"
                      title="Download"
                    >
                      <i class="bi bi-download"></i>
                    </a>
                  </div>
                </div>

                <!-- No Attachments -->
                <div
                  v-if="!hasAttachments() && getActedDocuments().length === 0"
                  class="no-attachments"
                >
                  <i
                    class="bi bi-file-earmark-x"
                    style="font-size: 3rem; color: #9ca3af"
                  ></i>
                  <h5 class="mt-3">No Attachments</h5>
                  <p class="text-muted">
                    This document doesn't have any attachments yet.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ATTACHMENT VIEWER MODAL -->
    <div
      v-if="showAttachmentViewer"
      class="modal-overlay"
      @click.self="closeAttachmentViewer"
    >
      <div class="modal-dialog enhanced-modal attachment-viewer-modal">
        <div class="modal-content square-modal">
          <div
            class="modal-header-enhanced square-header"
            style="background: linear-gradient(135deg, #1e40af, #1d4ed8)"
          >
            <div class="d-flex align-items-center">
              <div
                class="modal-icon-wrapper square-icon"
                style="background: rgba(255, 255, 255, 0.2)"
              >
                <i class="bi bi-file-earmark-pdf"></i>
              </div>
              <div>
                <h5 class="modal-title">Attachment Viewer</h5>
                <small class="modal-subtitle">
                  <span class="tracking-badge">{{
                    selectedAttachment?.tracking_number ||
                    selectedDocument?.tracking_number
                  }}</span>
                  <span class="attachment-type-badge">{{
                    selectedAttachmentType === "draft" ? "Draft" : "Released"
                  }}</span>
                </small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close-custom square-close"
              @click="closeAttachmentViewer"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body-enhanced attachment-viewer-body">
            <div class="attachment-viewer-toolbar">
              <div class="toolbar-left">
                <span class="toolbar-file-name">
                  <i class="bi bi-file-pdf text-danger me-2"></i>
                  {{
                    selectedAttachment?.tracking_number ||
                    selectedDocument?.tracking_number
                  }}_{{ selectedAttachmentType }}.pdf
                </span>
              </div>
              <div class="toolbar-right">
                <button
                  class="btn-toolbar"
                  title="Zoom In"
                  @click="attachmentZoomIn"
                >
                  <i class="bi bi-zoom-in"></i>
                </button>
                <button
                  class="btn-toolbar"
                  title="Zoom Out"
                  @click="attachmentZoomOut"
                >
                  <i class="bi bi-zoom-out"></i>
                </button>
                <button
                  class="btn-toolbar"
                  title="Reset Zoom"
                  @click="attachmentZoomReset"
                >
                  <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <a
                  :href="getAttachmentViewerUrl()"
                  download
                  class="btn-toolbar btn-toolbar-download"
                  title="Download"
                >
                  <i class="bi bi-download"></i>
                </a>
              </div>
            </div>

            <div class="attachment-viewer-content">
              <div v-if="attachmentLoading" class="attachment-loading">
                <div class="loader-spinner"></div>
                <p class="mt-3 text-muted">Loading attachment...</p>
              </div>

              <iframe
                v-show="!attachmentLoading && !attachmentLoadError"
                :src="getAttachmentViewerUrl()"
                class="attachment-viewer-iframe"
                :style="{
                  width: `${100 / attachmentZoom}%`,
                  height: `${attachmentViewerHeight}px`,
                }"
                frameborder="0"
                @load="onAttachmentLoaded"
                @error="handleAttachmentError"
              ></iframe>

              <div v-if="attachmentLoadError" class="attachment-error">
                <i
                  class="bi bi-file-earmark-x"
                  style="font-size: 4rem; color: #ef4444"
                ></i>
                <h5 class="mt-3">Unable to Load Attachment</h5>
                <p class="text-muted">
                  The attachment could not be loaded or doesn't exist.
                </p>
                <button
                  class="btn btn-outline-secondary btn-sm mt-3"
                  @click="retryAttachmentLoad"
                >
                  <i class="bi bi-arrow-repeat me-1"></i> Retry
                </button>
              </div>
            </div>

            <div class="attachment-viewer-footer">
              <span class="attachment-zoom-level"
                >Zoom: {{ Math.round(attachmentZoom * 100) }}%</span
              >
              <span class="attachment-info">
                <i class="bi bi-file-pdf me-1"></i>
                {{
                  selectedAttachmentType === "draft"
                    ? "Draft Document"
                    : "Released Document"
                }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ACTED DOCUMENT VIEWER MODAL -->
    <div
      v-if="showActedViewer"
      class="modal-overlay"
      @click.self="closeActedViewer"
    >
      <div class="modal-dialog enhanced-modal attachment-viewer-modal">
        <div class="modal-content square-modal">
          <div
            class="modal-header-enhanced square-header"
            style="background: linear-gradient(135deg, #7c3aed, #6d28d9)"
          >
            <div class="d-flex align-items-center">
              <div
                class="modal-icon-wrapper square-icon"
                style="background: rgba(255, 255, 255, 0.2)"
              >
                <i class="bi bi-file-earmark-pdf"></i>
              </div>
              <div>
                <h5 class="modal-title">Acted Document Viewer</h5>
                <small class="modal-subtitle">
                  <span class="tracking-badge">{{
                    selectedDocument?.tracking_number
                  }}</span>
                  <span class="attachment-type-badge acted-badge-sm"
                    >Acted</span
                  >
                </small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close-custom square-close"
              @click="closeActedViewer"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body-enhanced attachment-viewer-body">
            <div class="attachment-viewer-toolbar">
              <div class="toolbar-left">
                <span class="toolbar-file-name">
                  <i class="bi bi-file-pdf text-danger me-2"></i>
                  {{ getActedFileName(selectedActedDocument) }}
                </span>
              </div>
              <div class="toolbar-right">
                <button
                  class="btn-toolbar"
                  title="Zoom In"
                  @click="actedZoomIn"
                >
                  <i class="bi bi-zoom-in"></i>
                </button>
                <button
                  class="btn-toolbar"
                  title="Zoom Out"
                  @click="actedZoomOut"
                >
                  <i class="bi bi-zoom-out"></i>
                </button>
                <button
                  class="btn-toolbar"
                  title="Reset Zoom"
                  @click="actedZoomReset"
                >
                  <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <a
                  :href="getActedDocumentUrl(selectedActedDocument)"
                  download
                  class="btn-toolbar btn-toolbar-download"
                  title="Download"
                >
                  <i class="bi bi-download"></i>
                </a>
              </div>
            </div>

            <div class="attachment-viewer-content">
              <div v-if="actedLoading" class="attachment-loading">
                <div class="loader-spinner"></div>
                <p class="mt-3 text-muted">Loading acted document...</p>
              </div>

              <iframe
                v-show="!actedLoading && !actedLoadError"
                :src="getActedDocumentUrl(selectedActedDocument)"
                class="attachment-viewer-iframe"
                :style="{
                  width: `${100 / actedZoom}%`,
                  height: `${actedViewerHeight}px`,
                }"
                frameborder="0"
                @load="onActedLoaded"
                @error="handleActedError"
              ></iframe>

              <div v-if="actedLoadError" class="attachment-error">
                <i
                  class="bi bi-file-earmark-x"
                  style="font-size: 4rem; color: #ef4444"
                ></i>
                <h5 class="mt-3">Unable to Load Document</h5>
                <p class="text-muted">
                  The acted document could not be loaded or doesn't exist.
                </p>
                <button
                  class="btn btn-outline-secondary btn-sm mt-3"
                  @click="retryActedLoad"
                >
                  <i class="bi bi-arrow-repeat me-1"></i> Retry
                </button>
              </div>
            </div>

            <div class="attachment-viewer-footer">
              <span class="attachment-zoom-level"
                >Zoom: {{ Math.round(actedZoom * 100) }}%</span
              >
              <span class="attachment-info">
                <i class="bi bi-file-pdf me-1"></i>
                Acted Document
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import PaginationComponent from "../../components/PaginationComponent.vue";

export default {
  name: "ForReleaseTab",

  components: { PaginationComponent },

  emits: ["view-document", "show-notification"],

  props: {
    documentTypes: {
      type: Array,
      default: () => [
        "Memorandum",
        "Letter",
        "Report",
        "Request",
        "Permit",
        "Certificate",
      ],
    },
  },

  data() {
    return {
      for_release: {
        data: [],
        current_page: 1,
        from: 1,
        to: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      },
      searchQuery: "",
      docTypeFilter: "",
      perPage: 10,
      loading: false,

      // Forward modal
      showForwardModal: false,
      forwardDocument: null,
      offices: [],
      officesLoading: false,
      selectedOffices: [],
      forwardRemarks: "",
      forwarding: false,
      forwardError: "",

      // Release modal
      showReleaseModal: false,
      releaseDocument: null,
      releaseForm: {
        date: "",
        time: "",
        remarks: "",
        attachment: null,
      },
      releasing: false,
      releaseError: "",
      isDragging: false,
      uploadProgress: 0,

      // View modal
      showViewModal: false,
      selectedDocument: null,
      viewerActiveTab: "details",
      pdfLoading: true,
      pdfLoadError: false,
      pdfZoom: 1,
      pdfViewerHeight: 700,
      routeHistoryLoading: false,

      // Attachment Viewer
      showAttachmentViewer: false,
      selectedAttachment: null,
      selectedAttachmentType: "draft",
      attachmentLoading: true,
      attachmentLoadError: false,
      attachmentZoom: 1,
      attachmentViewerHeight: 700,

      // Acted Document Viewer
      showActedViewer: false,
      selectedActedDocument: null,
      actedLoading: true,
      actedLoadError: false,
      actedZoom: 1,
      actedViewerHeight: 700,
    };
  },

  mounted() {
    this.getDataForRelease();
    this.setDefaultDateTime();
  },

  methods: {
    // Data fetching
    async getDataForRelease(page = 1) {
      this.loading = true;
      try {
        const response = await axios.get("/dts_denr/api/for-release", {
          params: {
            page,
            per_page: this.perPage,
            search: this.searchQuery,
            document_type: this.docTypeFilter,
          },
        });
        this.for_release = response.data.data;
      } catch (error) {
        console.error("Error fetching for-release data:", error);
        this.$emit("show-notification", {
          message: "Failed to load data. Please try again.",
          type: "error",
        });
      } finally {
        this.loading = false;
      }
    },

    // Filters
    applyFilters() {
      this.getDataForRelease(1);
    },
    clearSearch() {
      this.searchQuery = "";
      this.getDataForRelease(1);
    },
    clearDocTypeFilter() {
      this.docTypeFilter = "";
      this.getDataForRelease(1);
    },
    clearAllFilters() {
      this.searchQuery = "";
      this.docTypeFilter = "";
      this.getDataForRelease(1);
    },
    changePage(page) {
      if (
        page >= 1 &&
        page <= this.for_release.last_page &&
        page !== this.for_release.current_page
      ) {
        this.getDataForRelease(page);
      }
    },
    changePerPage() {
      this.getDataForRelease(1);
    },

    // Set default date/time
    setDefaultDateTime() {
      const now = new Date();
      this.releaseForm.date = now.toISOString().split("T")[0];
      this.releaseForm.time = now.toTimeString().slice(0, 5);
    },

    // File handling
    triggerFileInput() {
      if (!this.releasing) {
        this.$refs.fileInput.click();
      }
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.validateAndSetFile(file);
      }
    },
    handleDrop(event) {
      this.isDragging = false;
      const files = event.dataTransfer.files;
      if (files.length > 0) {
        this.validateAndSetFile(files[0]);
      }
    },
    validateAndSetFile(file) {
      // Validate file type
      if (file.type !== "application/pdf") {
        this.releaseError = "Please upload a PDF file only.";
        return;
      }

      // Validate file size (max 100MB)
      if (file.size > 100 * 1024 * 1024) {
        this.releaseError = "File size exceeds 100MB limit.";
        return;
      }

      this.releaseError = "";
      this.releaseForm.attachment = file;
      this.uploadProgress = 100;
    },
    removeAttachment() {
      this.releaseForm.attachment = null;
      this.uploadProgress = 0;
      this.$refs.fileInput.value = "";
    },
    formatFileSize(bytes) {
      if (bytes === 0) return "0 Bytes";
      const k = 1024;
      const sizes = ["Bytes", "KB", "MB", "GB"];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    },

    // Release modal
    openReleaseModal(doc) {
      this.releaseDocument = doc;
      this.showReleaseModal = true;
      this.releaseError = "";
      this.setDefaultDateTime();
      this.releaseForm.remarks = "";
      this.releaseForm.attachment = null;
      this.uploadProgress = 0;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
    },
    closeReleaseModal() {
      this.showReleaseModal = false;
      this.releaseDocument = null;
      this.releaseError = "";
      this.releasing = false;
      this.releaseForm.attachment = null;
      this.uploadProgress = 0;
    },
    async submitReleaseDocument() {
      if (!this.releaseForm.date || !this.releaseForm.time) {
        this.releaseError = "Please select both release date and time.";
        return;
      }

      if (!this.releaseForm.attachment) {
        this.releaseError = "Please upload a released document attachment.";
        return;
      }

      this.releasing = true;
      this.releaseError = "";
      this.uploadProgress = 0;

      try {
        const formData = new FormData();
        formData.append("document_id", this.releaseDocument.id);
        formData.append(
          "tracking_number",
          this.releaseDocument.tracking_number
        );
        formData.append("date_released", this.releaseForm.date);
        formData.append("time_released", this.releaseForm.time);
        formData.append("remarks", this.releaseForm.remarks || "");
        formData.append("attachment", this.releaseForm.attachment);

        const response = await axios.post(
          "/dts_denr/api/release-document/" + this.releaseDocument.id,
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
            onUploadProgress: (progressEvent) => {
              if (progressEvent.total) {
                this.uploadProgress = Math.round(
                  (progressEvent.loaded / progressEvent.total) * 100
                );
              }
            },
          }
        );

        await Swal.fire({
          title: "Released!",
          text:
            response.data.message || "Document has been released successfully.",
          icon: "success",
          confirmButtonColor: "#059669",
          confirmButtonText: "OK",
        });

        this.closeReleaseModal();
        this.$emit("show-notification", {
          message: "Document released successfully!",
          type: "success",
        });
        this.getDataForRelease(this.for_release.current_page);
      } catch (error) {
        console.error("Release document error:", error);
        const message =
          error.response?.data?.message ||
          "Failed to release document. Please try again.";
        this.releaseError = message;
        this.$emit("show-notification", { message, type: "error" });
      } finally {
        this.releasing = false;
        this.uploadProgress = 0;
      }
    },

    // Forward modal
    openForwardModal(doc) {
      this.forwardDocument = doc;
      this.showForwardModal = true;
      this.selectedOffices = [];
      this.forwardRemarks = "";
      this.forwardError = "";
      this.fetchOffices();
    },
    closeForwardModal() {
      this.showForwardModal = false;
      this.forwardDocument = null;
      this.selectedOffices = [];
      this.forwardRemarks = "";
      this.forwardError = "";
    },
    clearForwardForm() {
      this.selectedOffices = [];
      this.forwardRemarks = "";
      this.forwardError = "";
    },
    async fetchOffices() {
      this.officesLoading = true;
      try {
        const response = await axios.get("/dts_denr/api/route-office-other");
        this.offices = response.data.data || [];
      } catch (error) {
        console.error("Error fetching offices:", error);
        this.forwardError = "Failed to load offices. Please try again.";
      } finally {
        this.officesLoading = false;
      }
    },
    isOfficeSelected(officeId) {
      return this.selectedOffices.some((o) => o.id === officeId);
    },
    toggleOfficeSelection(office) {
      if (this.forwarding) return;
      this.forwardError = "";
      this.isOfficeSelected(office.id)
        ? this.removeOffice(office.id)
        : this.selectedOffices.push(office);
    },
    removeOffice(officeId) {
      this.selectedOffices = this.selectedOffices.filter(
        (o) => o.id !== officeId
      );
    },
    async submitForwardDocument() {
      if (this.selectedOffices.length === 0) {
        this.forwardError = "Please select at least one office.";
        return;
      }
      this.forwarding = true;
      this.forwardError = "";
      try {
        const response = await axios.post(
          "/dts_denr/api/add-forward-document",
          {
            document_id: this.forwardDocument.id,
            tracking_number: this.forwardDocument.tracking_number,
            offices: this.selectedOffices.map((o) => o.id),
            remarks: this.forwardRemarks,
          }
        );
        await Swal.fire({
          title: "Forwarded!",
          text:
            response.data.message ||
            "Document has been forwarded successfully.",
          icon: "success",
          confirmButtonColor: "#1a4731",
          confirmButtonText: "OK",
        });
        this.closeForwardModal();
        this.$emit("show-notification", {
          message: "Document forwarded successfully!",
          type: "success",
        });
        this.getDataForRelease(this.for_release.current_page);
      } catch (error) {
        console.error("Forward document error:", error);
        const message =
          error.response?.data?.message ||
          "Failed to forward document. Please try again.";
        this.forwardError = message;
        this.$emit("show-notification", { message, type: "error" });
      } finally {
        this.forwarding = false;
      }
    },

    // View modal
    viewDocument(doc) {
      this.selectedDocument = doc;
      this.showViewModal = true;
      this.viewerActiveTab = "details";
      this.pdfLoading = true;
      this.pdfLoadError = false;
      this.pdfZoom = 1;
      this.pdfViewerHeight = 700;
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
    },

    // PDF methods
    getPdfUrl(document) {
      if (!document) return "";

      if (document.draft_attachment) {
        return `/dts_denr/storage/app/public/${document.draft_attachment}`;
      }

      if (document.released_attachment) {
        return this.getReleasedAttachmentUrl(document.released_attachment);
      }

      return `/dts_denr/storage/app/public/attachments/${document.tracking_number}/draft_attachment.pdf`;
    },
    getAttachmentUrl(path) {
      if (!path) return "#";
      if (path.startsWith("http")) return path;
      return `/dts_denr/storage/app/public/${path}`;
    },
    getReleasedAttachmentUrl(path) {
      if (!path) return "#";
      if (path.startsWith("http")) return path;
      return `/dts_denr/storage/app/public/${path}`;
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
        const iframe = document.querySelector(".pdf-iframe");
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

    // Acted Documents Methods
    getActedDocuments() {
      if (!this.selectedDocument) return [];

      // Check if document has acted_documents array
      if (
        this.selectedDocument.acted_documents &&
        Array.isArray(this.selectedDocument.acted_documents)
      ) {
        return this.selectedDocument.acted_documents;
      }

      // If there's a single acted_document field
      if (this.selectedDocument.acted_document) {
        return [this.selectedDocument.acted_document];
      }

      return [];
    },

    getActedFileName(actedDoc) {
      if (!actedDoc) return "acted_document.pdf";

      // Check if there's a file name field
      if (actedDoc.file_name) return actedDoc.file_name;
      if (actedDoc.filename) return actedDoc.filename;
      if (actedDoc.name) return actedDoc.name;

      // Generate from tracking number
      const tracking = this.selectedDocument?.tracking_number || "document";
      return `${tracking}_acted_${actedDoc.id || "1"}.pdf`;
    },

    getActedDocumentUrl(actedDoc) {
      if (!actedDoc) return "#";

      // If there's a direct path
      if (actedDoc.file_path) {
        if (actedDoc.file_path.startsWith("http")) return actedDoc.file_path;
        return `/dts_denr/storage/app/public/${actedDoc.file_path}`;
      }

      if (actedDoc.path) {
        if (actedDoc.path.startsWith("http")) return actedDoc.path;
        return `/dts_denr/storage/app/public/${actedDoc.path}`;
      }

      // If there's a file name, construct path
      if (actedDoc.file_name || actedDoc.filename) {
        const fileName = actedDoc.file_name || actedDoc.filename;
        const tracking = this.selectedDocument?.tracking_number || "unknown";
        return `/dts_denr/storage/app/public/attachments/${tracking}/ACTED_DOCUMENTS/${fileName}`;
      }

      // Try to use the acted_document column value
      if (actedDoc.acted_document) {
        return `/dts_denr/storage/app/public/${actedDoc.acted_document}`;
      }

      // Fallback: construct from tracking number
      const tracking = this.selectedDocument?.tracking_number || "unknown";
      const fileName = `${tracking}_acted_${actedDoc.id || "1"}.pdf`;
      return `/dts_denr/storage/app/public/attachments/${tracking}/ACTED_DOCUMENTS/${fileName}`;
    },

    openActedDocumentViewer(actedDoc) {
      this.selectedActedDocument = actedDoc;
      this.showActedViewer = true;
      this.actedLoading = true;
      this.actedLoadError = false;
      this.actedZoom = 1;
      this.actedViewerHeight = 700;
    },

    closeActedViewer() {
      this.showActedViewer = false;
      this.selectedActedDocument = null;
      this.actedLoading = true;
      this.actedLoadError = false;
      this.actedZoom = 1;
      this.actedViewerHeight = 700;
    },

    onActedLoaded() {
      this.actedLoading = false;
      this.actedLoadError = false;
    },

    handleActedError() {
      this.actedLoading = false;
      this.actedLoadError = true;
    },

    retryActedLoad() {
      this.actedLoading = true;
      this.actedLoadError = false;
      this.$nextTick(() => {
        const iframe = document.querySelector(".attachment-viewer-iframe");
        if (iframe) iframe.src = iframe.src;
      });
    },

    actedZoomIn() {
      if (this.actedZoom < 2) {
        this.actedZoom += 0.25;
        this.actedViewerHeight = Math.round(700 / this.actedZoom);
      }
    },

    actedZoomOut() {
      if (this.actedZoom > 0.5) {
        this.actedZoom -= 0.25;
        this.actedViewerHeight = Math.round(700 / this.actedZoom);
      }
    },

    actedZoomReset() {
      this.actedZoom = 1;
      this.actedViewerHeight = 700;
    },

    // Attachment Viewer methods
    openAttachmentViewer(type) {
      this.selectedAttachment = this.selectedDocument;
      this.selectedAttachmentType = type;
      this.showAttachmentViewer = true;
      this.attachmentLoading = true;
      this.attachmentLoadError = false;
      this.attachmentZoom = 1;
      this.attachmentViewerHeight = 700;
    },
    closeAttachmentViewer() {
      this.showAttachmentViewer = false;
      this.selectedAttachment = null;
      this.selectedAttachmentType = "draft";
      this.attachmentLoading = true;
      this.attachmentLoadError = false;
      this.attachmentZoom = 1;
      this.attachmentViewerHeight = 700;
    },
    getAttachmentViewerUrl() {
      if (!this.selectedDocument) return "#";
      if (this.selectedAttachmentType === "draft") {
        return this.getAttachmentUrl(this.selectedDocument.draft_attachment);
      } else {
        return this.getReleasedAttachmentUrl(
          this.selectedDocument.released_attachment
        );
      }
    },
    onAttachmentLoaded() {
      this.attachmentLoading = false;
      this.attachmentLoadError = false;
    },
    handleAttachmentError() {
      this.attachmentLoading = false;
      this.attachmentLoadError = true;
    },
    retryAttachmentLoad() {
      this.attachmentLoading = true;
      this.attachmentLoadError = false;
      this.$nextTick(() => {
        const iframe = document.querySelector(".attachment-viewer-iframe");
        if (iframe) iframe.src = iframe.src;
      });
    },
    attachmentZoomIn() {
      if (this.attachmentZoom < 2) {
        this.attachmentZoom += 0.25;
        this.adjustAttachmentViewerHeight();
      }
    },
    attachmentZoomOut() {
      if (this.attachmentZoom > 0.5) {
        this.attachmentZoom -= 0.25;
        this.adjustAttachmentViewerHeight();
      }
    },
    attachmentZoomReset() {
      this.attachmentZoom = 1;
      this.adjustAttachmentViewerHeight();
    },
    adjustAttachmentViewerHeight() {
      this.attachmentViewerHeight = Math.round(700 / this.attachmentZoom);
    },

    // Attachments methods
    hasAttachments() {
      return !!(
        this.selectedDocument?.draft_attachment ||
        this.selectedDocument?.released_attachment
      );
    },
    getAttachmentsCount() {
      let count = 0;
      if (this.selectedDocument?.draft_attachment) count++;
      if (this.selectedDocument?.released_attachment) count++;

      // Count acted documents
      const actedDocs = this.getActedDocuments();
      count += actedDocs.length;

      return count;
    },

    // Route history helpers
    isRouteActive(route) {
      return (
        ["In Progress", "Active", "Current"].includes(route.status) ||
        route.is_current === true
      );
    },
    getRouteStatusType(status) {
      if (!status) return "pending";
      const s = status.toLowerCase();
      if (["completed", "approved"].includes(s)) return "completed";
      if (["in progress", "active", "current"].includes(s)) return "active";
      if (["rejected", "returned", "cancelled"].includes(s)) return "rejected";
      return "pending";
    },
    getRouteNodeClass(status) {
      return `node-${this.getRouteStatusType(status)}`;
    },
    getRouteStatusClass(status) {
      return `badge-${this.getRouteStatusType(status)}`;
    },
    getRouteStatusIcon(status) {
      const t = this.getRouteStatusType(status);
      if (t === "completed") return "bi bi-check-circle-fill";
      if (t === "active") return "bi bi-arrow-repeat";
      if (t === "rejected") return "bi bi-x-circle-fill";
      return "bi bi-clock-fill";
    },

    // Formatters
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
      if (typeof t === "string" && /AM|PM/i.test(t)) return t;
      const [h, m] = t.split(":").map(Number);
      const period = h >= 12 ? "PM" : "AM";
      return `${h % 12 || 12}:${String(m).padStart(2, "0")} ${period}`;
    },
    formatDateTime(dateString) {
      if (!dateString) return "N/A";
      return new Date(dateString).toLocaleString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
      });
    },
    getStatusClass(status) {
      const map = {
        "In-Progress": "status-in-progress",
        "For-Release": "status-for-release",
        Released: "status-released",
      };
      return map[status] || "";
    },
  },
};
</script>



<style scoped>
/* Modal overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  animation: fadeIn 0.2s ease-out;
  padding: 15px;
}

.enhanced-modal {
  width: 100%;
  max-width: 620px;
  margin: 0 auto;
  animation: modalSlideUp 0.3s ease-out;
}

/* Release Modal Specific */
.release-modal {
  max-width: 700px;
}

.release-modal-body {
  padding: 20px 24px;
}

.square-modal {
  border: none;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
  background: #fff;
  position: relative;
  max-height: 95vh;
  display: flex;
  flex-direction: column;
}

.square-header {
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
}

.square-header {
  background: linear-gradient(135deg, #1e4d2b, #2d6a4f);
}

.square-icon {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  margin-right: 12px;
  flex-shrink: 0;
}

.modal-title {
  font-weight: 700;
  font-size: 1.1rem;
  margin: 0;
}

.modal-subtitle {
  font-size: 0.75rem;
  opacity: 0.85;
  display: block;
}

.btn-close-custom.square-close {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: white;
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
  flex-shrink: 0;
}

.btn-close-custom.square-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.modal-body-enhanced {
  padding: 20px;
  background: #f9fafb;
  overflow-y: auto;
  flex: 1;
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
  margin-bottom: 16px;
  font-weight: 500;
}

/* Form elements */
.form-label-enhanced {
  font-weight: 600;
  font-size: 0.85rem;
  color: #1e293b;
  margin-bottom: 6px;
  display: block;
}

.required-star {
  color: #dc2626;
  margin-left: 3px;
}

.form-input {
  width: 100%;
  height: 42px;
  padding: 0 12px;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 13px;
  color: #1c2b24;
  background: #ffffff;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.form-input:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.form-textarea {
  height: auto;
  padding-top: 10px;
  resize: vertical;
  min-height: 42px;
}

/* Release Modal Styles */
.release-doc-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 16px;
}

.release-info-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 10px 14px;
  transition: all 0.2s ease;
}

.release-info-card:hover {
  border-color: #059669;
  background: #f0fdf4;
}

.release-info-label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.6rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 3px;
}

.release-info-label i {
  font-size: 0.7rem;
  color: #059669;
}

.release-info-value {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1e293b;
  word-break: break-word;
}

.tracking-value {
  font-family: "Courier New", monospace;
  color: #059669;
  background: #ecfdf5;
  padding: 1px 6px;
  border-radius: 4px;
  display: inline-block;
}

.subject-value {
  font-weight: 500;
  font-size: 0.8rem;
}

.release-datetime-section {
  margin-bottom: 14px;
}

.release-datetime-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.form-label-sm {
  font-size: 0.7rem;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

/* Upload Attachment Styles */
.release-upload-section {
  margin-bottom: 14px;
}

.release-upload-area {
  border: 2px dashed #d1d5db;
  border-radius: 10px;
  padding: 24px 16px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #fafbfc;
  position: relative;
  min-height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.release-upload-area:hover {
  border-color: #059669;
  background: #f0fdf4;
}

.release-upload-area.drag-over {
  border-color: #059669;
  background: #ecfdf5;
  transform: scale(1.01);
}

.release-upload-area.has-file {
  border-color: #059669;
  background: #ecfdf5;
  border-style: solid;
}

.release-upload-content {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.upload-placeholder {
  width: 100%;
}

.upload-icon-wrapper {
  width: 60px;
  height: 60px;
  margin: 0 auto 8px;
  background: #d1fae5;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  color: #059669;
}

.upload-title {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.85rem;
  margin-bottom: 2px;
}

.upload-subtitle {
  font-size: 0.7rem;
  color: #94a3b8;
  margin: 0;
}

/* File preview in upload area */
.upload-file-preview {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 6px 10px;
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.upload-file-icon {
  width: 38px;
  height: 38px;
  min-width: 38px;
  border-radius: 8px;
  background: #fef2f2;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: #dc2626;
}

.upload-file-info {
  flex: 1;
  text-align: left;
  min-width: 0;
}

.upload-file-name {
  display: block;
  font-weight: 600;
  color: #1e293b;
  font-size: 0.8rem;
  word-break: break-all;
}

.upload-file-size {
  display: block;
  font-size: 0.65rem;
  color: #64748b;
}

.upload-remove-btn {
  width: 28px;
  height: 28px;
  border: none;
  border-radius: 6px;
  background: #fef2f2;
  color: #dc2626;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.upload-remove-btn:hover:not(:disabled) {
  background: #fee2e2;
  transform: scale(1.1);
}

.upload-remove-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.release-file-input {
  display: none;
}

/* Upload Progress */
.upload-progress {
  margin-top: 8px;
  position: relative;
  height: 5px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #059669, #047857);
  border-radius: 3px;
  transition: width 0.3s ease;
}

.progress-text {
  position: absolute;
  right: 0;
  top: -18px;
  font-size: 0.65rem;
  font-weight: 600;
  color: #059669;
}

.release-remarks-section {
  margin-bottom: 14px;
}

/* Modal actions */
.modal-actions {
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.square-btn {
  border-radius: 8px !important;
  font-weight: 600;
  transition: all 0.2s;
  padding: 8px 18px;
  font-size: 0.85rem;
}

.btn-save {
  color: white;
  border: none;
  padding: 8px 20px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
}

.btn-save:hover:not(:disabled) {
  box-shadow: 0 4px 16px rgba(5, 150, 105, 0.4);
  transform: translateY(-1px);
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

/* Button release */
.btn-release {
  color: #059669;
  border-color: #86efac;
  background: #ecfdf5;
}

.btn-release:hover {
  background: #86efac;
  transform: scale(1.05);
}

/* Table */
.office-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  background: white;
}

.office-table th,
.office-table td {
  border: 1px solid #f3f4f6;
  padding: 10px 12px;
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

.office-table tbody tr:hover {
  background: #f0fdf4;
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
  width: 26px;
  height: 26px;
  border-radius: 6px;
  background: #f3f4f6;
  color: #6b7280;
  font-size: 11px;
  font-weight: 600;
}

.tracking-number {
  font-weight: 700;
  color: #2d6a4f;
  font-family: "Courier New", monospace;
  font-size: 12px;
  padding: 2px 6px;
  background: #f0fdf4;
  border-radius: 4px;
}

.doc-type-badge {
  display: inline-block;
  padding: 2px 8px;
  background: #e0e7ff;
  color: #3730a3;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 600;
  border: 1px solid #c7d2fe;
}

.subject-text {
  color: #1e293b;
  font-weight: 500;
  line-height: 1.4;
  font-size: 12px;
}

.sender-text {
  color: #475569;
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
}

.sender-icon {
  color: #94a3b8;
  font-size: 13px;
}

.date-received {
  color: #64748b;
  font-size: 11px;
  display: flex;
  align-items: center;
  gap: 4px;
}
.date-icon {
  color: #94a3b8;
  font-size: 12px;
}

/* Action buttons */
.action-buttons {
  display: flex;
  gap: 4px;
  align-items: center;
}

.btn-action {
  background: none;
  border: 1px solid transparent;
  border-radius: 6px;
  padding: 5px 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.btn-view {
  color: #6366f1;
  border-color: #c7d2fe;
  background: #eef2ff;
}
.btn-forward {
  color: #2563eb;
  border-color: #93c5fd;
  background: #dbeafe;
}

.btn-view:hover {
  background: #ddd6fe;
  transform: scale(1.05);
}
.btn-forward:hover {
  background: #93c5fd;
  transform: scale(1.05);
}

/* Filter controls */
.filter-controls {
  margin-bottom: 16px;
}

.search-filter-row {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.search-box-wrapper {
  flex: 1;
  min-width: 180px;
}
.search-box {
  position: relative;
  width: 100%;
}

.search-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  z-index: 1;
}

.search-input {
  width: 100%;
  padding: 8px 30px 8px 32px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
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
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 2px;
}

.search-clear-btn:hover {
  color: #ef4444;
}

.per-page-wrapper {
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}

.per-page-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

.per-page-select {
  padding: 6px 10px;
  border: 2px solid #e5e7eb;
  border-radius: 6px;
  font-size: 12px;
  background: #ffffff;
  outline: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.per-page-select:focus {
  border-color: #2d6a4f;
}

.filter-wrapper {
  min-width: 160px;
}
.filter-box {
  position: relative;
}

.filter-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  z-index: 1;
}

.filter-select {
  width: 100%;
  padding: 8px 10px 8px 32px;
  border: 2px solid #e5e7eb;
  border-radius: 6px;
  font-size: 12px;
  background: #ffffff;
  outline: none;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 8px center;
  transition: all 0.3s ease;
}

.filter-select:focus {
  border-color: #2d6a4f;
  box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1);
}

.active-filters {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 10px;
  padding: 8px 12px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 6px;
  flex-wrap: wrap;
}

.active-filters-label {
  font-size: 11px;
  font-weight: 600;
  color: #166534;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  background: #ffffff;
  border: 1px solid #86efac;
  border-radius: 16px;
  font-size: 11px;
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
  padding: 3px 10px;
  background: none;
  border: 1px solid #86efac;
  border-radius: 4px;
  font-size: 11px;
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
  margin-top: 8px;
  font-size: 12px;
  color: #6b7280;
}
.results-count {
  font-weight: 700;
  color: #2d6a4f;
  font-size: 14px;
}

/* Loader */
.loader-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top-color: #2d6a4f;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 8px;
}

.empty-state {
  padding: 16px;
  text-align: center;
}
.text-muted {
  color: #94a3b8;
}

/* Status badges */
.status-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 16px;
  font-size: 0.7rem;
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

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes modalSlideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Document viewer styles */
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

.document-viewer-tabs {
  display: flex;
  gap: 4px;
  padding: 10px 16px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  flex-shrink: 0;
  overflow-x: auto;
}

.viewer-tab-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.3s ease;
}

.viewer-tab-btn:hover {
  background: #f0fdf4;
  border-color: #86efac;
  color: #2d6a4f;
}

.viewer-tab-btn.active {
  background: linear-gradient(135deg, #2d6a4f, #1e4d2b);
  border-color: #2d6a4f;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
}

.document-viewer-layout {
  display: grid;
  grid-template-columns: 340px 1fr;
  height: calc(90vh - 140px);
  min-height: 400px;
}

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
  gap: 8px;
  padding: 12px 16px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.85rem;
  flex-shrink: 0;
}

.details-content {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.detail-card {
  display: flex;
  gap: 10px;
  padding: 10px 12px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.detail-icon-wrapper {
  width: 34px;
  height: 34px;
  min-width: 34px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
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
.detail-icon-wrapper.release-icon {
  background: #d1fae5;
  color: #059669;
}

.detail-info {
  flex: 1;
  min-width: 0;
}

.detail-info label {
  display: block;
  font-size: 0.6rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}

.detail-value {
  font-size: 0.8rem;
  color: #1e293b;
  font-weight: 500;
  word-break: break-word;
  line-height: 1.4;
}

.tracking-number-large {
  font-size: 0.85rem;
  font-weight: 700;
  color: #2d6a4f;
  font-family: "Courier New", monospace;
  background: #f0fdf4;
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
}

.doc-type-badge-large {
  display: inline-block;
  padding: 2px 10px;
  background: #e0e7ff;
  color: #3730a3;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #c7d2fe;
}

.time-text {
  color: #64748b;
  font-size: 0.7rem;
  display: block;
  margin-top: 2px;
}

/* PDF Panel */
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
  padding: 10px 16px;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.pdf-panel-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.85rem;
}

.pdf-panel-title i {
  color: #dc2626;
  font-size: 1.1rem;
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
  width: 30px;
  height: 30px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.85rem;
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
  min-height: 300px;
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
  min-height: 300px;
  color: #9ca3af;
  padding: 30px;
}

.pdf-loader-animation {
  position: relative;
  width: 60px;
  height: 60px;
  margin-bottom: 12px;
}

.pdf-loader-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 1.6rem;
  color: #dc2626;
  z-index: 2;
}

.pdf-error {
  color: #fca5a5;
}
.pdf-error h5 {
  color: #fca5a5;
  font-weight: 600;
  font-size: 1rem;
}

.pdf-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 16px;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  font-size: 0.7rem;
  color: #64748b;
  flex-shrink: 0;
}

.pdf-zoom-level {
  font-weight: 600;
  color: #2d6a4f;
}

/* Attachment Viewer */
.attachment-viewer-modal {
  max-width: 1200px;
  width: 95vw;
  max-height: 90vh;
}

.attachment-viewer-body {
  padding: 0;
  max-height: calc(90vh - 80px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f9fafb;
}

.attachment-viewer-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
  flex-wrap: wrap;
  gap: 8px;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.toolbar-file-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.85rem;
}

.attachment-type-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.toolbar-right {
  display: flex;
  gap: 4px;
  align-items: center;
}

.btn-toolbar {
  background: #f1f5f9;
  border: 1px solid #e5e7eb;
  color: #475569;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.85rem;
  text-decoration: none;
}

.btn-toolbar:hover:not(:disabled) {
  background: #e2e8f0;
  border-color: #94a3b8;
  color: #1e293b;
}

.btn-toolbar-download {
  background: #dbeafe;
  border-color: #93c5fd;
  color: #2563eb;
}

.btn-toolbar-download:hover {
  background: #bfdbfe;
  border-color: #60a5fa;
  color: #1d4ed8;
}

.attachment-viewer-content {
  flex: 1;
  overflow: auto;
  background: #525659;
  position: relative;
  min-height: 400px;
}

.attachment-viewer-iframe {
  border: none;
  display: block;
  background: #ffffff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.attachment-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 400px;
  color: #9ca3af;
}

.attachment-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 400px;
  color: #fca5a5;
  padding: 30px;
}

.attachment-error h5 {
  color: #fca5a5;
  font-weight: 600;
  font-size: 1rem;
}

.attachment-viewer-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 16px;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
  font-size: 0.7rem;
  color: #64748b;
  flex-shrink: 0;
}

.attachment-zoom-level {
  font-weight: 600;
  color: #2d6a4f;
}

/* Forward modal styles */
.forward-doc-info {
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 10px 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.forward-doc-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.forward-doc-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  min-width: 70px;
}

.forward-doc-value {
  font-size: 0.8rem;
  font-weight: 600;
  color: #1e293b;
}

.office-list-container {
  max-height: 260px;
  overflow-y: auto;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #ffffff;
}

.office-select-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid #f3f4f6;
  position: relative;
}

.office-select-item:last-child {
  border-bottom: none;
}
.office-select-item:hover {
  background: #f0fdf4;
}
.office-select-item.selected {
  background: #f0fdf4;
  border-left: 3px solid #059669;
}

.office-checkbox {
  font-size: 1.1rem;
  color: #059669;
  min-width: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.office-select-item:not(.selected) .office-checkbox {
  color: #d1d5db;
}

.office-icon-wrapper {
  width: 32px;
  height: 32px;
  min-width: 32px;
  border-radius: 6px;
  background: #e0e7ff;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #3730a3;
  font-size: 0.9rem;
}

.office-select-item.selected .office-icon-wrapper {
  background: #d1fae5;
  color: #059669;
}

.office-details {
  flex: 1;
  min-width: 0;
}

.office-name-text {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.3;
}

.office-code {
  display: block;
  font-size: 0.65rem;
  color: #64748b;
  margin-top: 1px;
}

.selected-indicator {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #059669;
  font-size: 1rem;
}

.selected-offices-summary {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 6px;
  padding: 10px 14px;
}

.selected-offices-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  font-size: 0.8rem;
  color: #166534;
}

.selected-offices-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 6px;
}

.selected-office-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  background: #ffffff;
  border: 1px solid #86efac;
  border-radius: 16px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #166534;
}

.remove-office-btn {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  font-size: 0.8rem;
  transition: color 0.2s;
}

.remove-office-btn:hover:not(:disabled) {
  color: #dc2626;
}
.remove-office-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Route history styles */
.route-history-panel {
  display: flex;
  flex-direction: column;
  height: calc(90vh - 140px);
  background: #ffffff;
  min-height: 400px;
}

.route-history-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  flex-shrink: 0;
  flex-wrap: wrap;
  gap: 8px;
}

.route-history-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.9rem;
}

.route-history-title i {
  color: #2d6a4f;
  font-size: 1.1rem;
}

.route-history-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 16px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #166534;
}

.route-history-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f9fafb;
}

.timeline-container {
  position: relative;
  padding-left: 40px;
}

.timeline-container::before {
  content: "";
  position: absolute;
  left: 12px;
  top: 8px;
  bottom: 8px;
  width: 2px;
  background: linear-gradient(180deg, #cbd5e1, #e2e8f0);
}

.timeline-item {
  position: relative;
  margin-bottom: 24px;
}
.timeline-item.is-last {
  margin-bottom: 0;
}

.timeline-node {
  position: absolute;
  left: -34px;
  top: 0;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  z-index: 2;
  border: 3px solid #ffffff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  font-size: 0.7rem;
}

.node-completed {
  background: #059669;
}
.node-active {
  background: #2563eb;
  animation: pulse 2s infinite;
}
.node-pending {
  background: #d97706;
}
.node-rejected {
  background: #dc2626;
}

.timeline-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  transition: all 0.3s ease;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.timeline-card:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  border-color: #cbd5e1;
}

.timeline-card.active-card {
  border-left: 4px solid #2563eb;
  background: linear-gradient(135deg, #eff6ff, #ffffff);
}

.timeline-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
  flex-wrap: wrap;
  gap: 8px;
}

.office-info {
  display: flex;
  align-items: center;
  gap: 8px;
}
.office-info i {
  font-size: 1.1rem;
  color: #2d6a4f;
}
.office-text {
  display: flex;
  flex-direction: column;
}

.office-name {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.3;
}

.route-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 16px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.badge-completed {
  background: #d1fae5;
  color: #059669;
  border: 1px solid #a7f3d0;
}
.badge-active {
  background: #dbeafe;
  color: #2563eb;
  border: 1px solid #bfdbfe;
}
.badge-pending {
  background: #fef3c7;
  color: #d97706;
  border: 1px solid #fde68a;
}
.badge-rejected {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.timeline-card-body {
  padding: 14px 16px;
}

.info-grid-enhanced {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 12px;
  margin-bottom: 14px;
}

.info-card {
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  transition: all 0.3s ease;
}

.received-card {
  border-left: 4px solid #3b82f6;
}
.completed-card {
  border-left: 4px solid #10b981;
}

.info-card-body {
  padding: 10px 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-field {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.field-icon {
  width: 24px;
  height: 24px;
  min-width: 24px;
  border-radius: 6px;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  color: #64748b;
}

.field-content {
  flex: 1;
  min-width: 0;
}

.field-label {
  display: block;
  font-size: 0.6rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 1px;
}

.field-value {
  display: block;
  font-size: 0.8rem;
  color: #1e293b;
  font-weight: 500;
  word-break: break-word;
  line-height: 1.4;
}

.received-date {
  color: #2563eb;
  font-weight: 600;
}
.completed-date {
  color: #059669;
  font-weight: 600;
}

.remarks-text {
  font-style: italic;
  color: #78350f;
  background: #fffbeb;
  padding: 3px 8px;
  border-radius: 4px;
  border-left: 3px solid #f59e0b;
}

/* Flow section */
.flow-section-enhanced {
  margin-top: 6px;
  padding: 12px 16px;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.flow-container {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.flow-node {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
  min-width: 120px;
  padding: 8px 12px;
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.origin-node {
  border-left: 4px solid #3b82f6;
}
.destination-node {
  border-left: 4px solid #10b981;
}

.flow-node-icon {
  width: 28px;
  height: 28px;
  min-width: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  color: #ffffff;
}

.origin-node .flow-node-icon {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}
.destination-node .flow-node-icon {
  background: linear-gradient(135deg, #10b981, #059669);
}

.flow-node-content {
  flex: 1;
  min-width: 0;
}

.flow-node-label {
  display: block;
  font-size: 0.55rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.flow-node-value {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #1e293b;
  word-break: break-word;
}

.flow-arrow-enhanced {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
  padding: 0 4px;
}

.arrow-line {
  width: 16px;
  height: 2px;
  background: linear-gradient(90deg, #94a3b8, #cbd5e1);
  border-radius: 2px;
}

.arrow-icon {
  font-size: 1.2rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: arrowPulse 1.5s ease-in-out infinite;
}

/* Attachments styles */
.attachments-panel {
  display: flex;
  flex-direction: column;
  height: calc(90vh - 140px);
  background: #ffffff;
  min-height: 400px;
}

.attachments-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  flex-shrink: 0;
}

.attachments-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.9rem;
}

.attachments-title i {
  color: #2d6a4f;
  font-size: 1.1rem;
}

.attachments-count {
  padding: 3px 12px;
  background: #f1f5f9;
  border-radius: 16px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #475569;
}

.attachments-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f9fafb;
}

.attachment-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.attachment-item:hover {
  border-color: #2563eb;
  box-shadow: 0 4px 16px rgba(37, 99, 235, 0.12);
  transform: translateY(-2px);
}

.attachment-item:last-child {
  margin-bottom: 0;
}

.attachment-icon-wrapper {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 8px;
  background: #fef2f2;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  color: #dc2626;
}

.attachment-details {
  flex: 1;
  min-width: 0;
}

.attachment-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.attachment-badge {
  font-size: 0.55rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.draft-badge {
  background: #fef3c7;
  color: #d97706;
  border: 1px solid #fde68a;
}

.released-badge {
  background: #d1fae5;
  color: #059669;
  border: 1px solid #a7f3d0;
}

.attachment-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.7rem;
  color: #64748b;
  margin-top: 2px;
}

.attachment-separator {
  color: #d1d5db;
}

.attachment-actions {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}

.btn-attachment-view,
.btn-attachment-download {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  color: #475569;
  background: #ffffff;
}

.btn-attachment-view:hover {
  background: #eff6ff;
  border-color: #93c5fd;
  color: #2563eb;
}

.btn-attachment-download:hover {
  background: #f0fdf4;
  border-color: #86efac;
  color: #059669;
}

.no-attachments {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 200px;
  text-align: center;
  color: #9ca3af;
}

.no-attachments h5 {
  font-size: 1rem;
  margin-top: 8px;
}

/* Animations */
@keyframes pulse {
  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(37, 99, 235, 0.1);
  }
}

@keyframes arrowPulse {
  0%,
  100% {
    transform: translateX(0);
    opacity: 1;
  }
  50% {
    transform: translateX(4px);
    opacity: 0.6;
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .document-view-modal {
    max-width: 98vw;
  }
  .document-viewer-layout {
    grid-template-columns: 1fr;
    grid-template-rows: auto 1fr;
    height: calc(90vh - 140px);
  }
  .details-panel {
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
    max-height: 300px;
  }
  .pdf-viewer-wrapper {
    min-height: 300px;
  }
  .info-grid-enhanced {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .modal-overlay {
    padding: 10px;
  }

  .enhanced-modal {
    max-width: 100%;
  }

  .square-header {
    padding: 14px 16px;
  }
  .square-icon {
    width: 34px;
    height: 34px;
    font-size: 1.1rem;
    margin-right: 10px;
  }
  .modal-title {
    font-size: 1rem;
  }

  .modal-body-enhanced {
    padding: 16px;
  }
  .release-modal-body {
    padding: 16px;
  }

  .release-doc-info-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  .release-datetime-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .release-upload-area {
    padding: 20px 16px;
    min-height: 80px;
  }
  .upload-icon-wrapper {
    width: 48px;
    height: 48px;
    font-size: 1.3rem;
  }
  .upload-title {
    font-size: 0.8rem;
  }

  .modal-actions {
    flex-direction: column;
    align-items: stretch;
  }
  .modal-actions .d-flex {
    justify-content: center;
  }
  .square-btn {
    width: 100%;
    justify-content: center;
  }

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
    overflow-x: auto;
  }
  .viewer-tab-btn {
    padding: 6px 12px;
    font-size: 11px;
  }
  .viewer-tab-btn span {
    display: none;
  }
  .viewer-tab-btn i {
    font-size: 14px;
  }

  .search-filter-row {
    flex-direction: column;
    gap: 8px;
  }
  .filter-wrapper {
    min-width: 100%;
  }

  .office-table {
    font-size: 11px;
  }
  .office-table th,
  .office-table td {
    padding: 6px 8px;
  }
  .office-table th:nth-child(3),
  .office-table td:nth-child(3) {
    display: none;
  }
  .office-table th:nth-child(6),
  .office-table td:nth-child(6) {
    display: none;
  }

  .btn-action {
    padding: 4px 6px;
    font-size: 0.8rem;
  }

  .flow-container {
    flex-direction: column;
    gap: 6px;
  }
  .flow-node {
    width: 100%;
    min-width: unset;
  }
  .flow-arrow-enhanced {
    transform: rotate(90deg);
    padding: 0;
  }
  .arrow-line {
    width: 24px;
  }

  .attachment-item {
    flex-wrap: wrap;
  }
  .attachment-actions {
    width: 100%;
    justify-content: flex-end;
    margin-top: 6px;
  }

  .attachment-viewer-modal {
    max-width: 100vw;
    width: 100vw;
    margin: 0;
    border-radius: 0;
    max-height: 100vh;
  }
  .attachment-viewer-content {
    min-height: 300px;
  }

  .timeline-container {
    padding-left: 32px;
  }
  .timeline-node {
    left: -28px;
    width: 22px;
    height: 22px;
    font-size: 0.6rem;
  }
}

@media (max-width: 480px) {
  .square-header {
    padding: 12px 14px;
    flex-wrap: wrap;
  }
  .square-icon {
    width: 30px;
    height: 30px;
    font-size: 1rem;
    margin-right: 8px;
  }
  .modal-title {
    font-size: 0.9rem;
  }
  .modal-subtitle {
    font-size: 0.65rem;
  }

  .release-info-card {
    padding: 8px 10px;
  }
  .release-info-value {
    font-size: 0.75rem;
  }
  .release-info-label {
    font-size: 0.55rem;
  }

  .upload-file-preview {
    flex-wrap: wrap;
    gap: 8px;
  }
  .upload-file-icon {
    width: 32px;
    height: 32px;
    min-width: 32px;
    font-size: 1rem;
  }
  .upload-file-name {
    font-size: 0.75rem;
  }
  .upload-remove-btn {
    margin-left: auto;
  }

  .office-table th:nth-child(4),
  .office-table td:nth-child(4) {
    display: none;
  }
  .office-table th:nth-child(7),
  .office-table td:nth-child(7) {
    display: none;
  }

  .per-page-wrapper {
    width: 100%;
    justify-content: flex-start;
  }
  .filter-wrapper {
    min-width: 100%;
  }

  .viewer-tab-btn {
    padding: 4px 10px;
    font-size: 10px;
  }
  .viewer-tab-btn i {
    font-size: 12px;
  }
}
</style>
