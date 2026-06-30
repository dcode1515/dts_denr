<!-- resources/js/components/tabs/ReleasedTab.vue -->
<template>
  <div>
    <!-- Filter Controls -->
    <div class="filter-controls">
      <div class="search-filter-row">
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
            <button v-if="searchQuery" @click="clearSearch" class="search-clear-btn">
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
        </div>
        <div class="per-page-wrapper">
          <span class="per-page-label">Show</span>
          <select v-model="perPage" @change="changePerPage" class="per-page-select">
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
            <select v-model="docTypeFilter" @change="applyFilters" class="filter-select">
              <option value="">All Document Types</option>
              <option v-for="type in documentTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
        </div>
      </div>

      <div class="active-filters" v-if="searchQuery || docTypeFilter">
        <span class="active-filters-label">Active Filters:</span>
        <span v-if="searchQuery" class="filter-tag">
          <i class="bi bi-search"></i> "{{ searchQuery }}"
          <button @click="clearSearch" class="filter-tag-close"><i class="bi bi-x"></i></button>
        </span>
        <span v-if="docTypeFilter" class="filter-tag">
          <i class="bi bi-funnel"></i> {{ docTypeFilter }}
          <button @click="clearDocTypeFilter" class="filter-tag-close"><i class="bi bi-x"></i></button>
        </span>
        <button @click="clearAllFilters" class="clear-all-filters">Clear All</button>
      </div>

      <div class="results-summary">
        {{ released.total }} document(s) found in <strong>Released</strong>
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
            <th style="width: 15%">Date Received</th>
            <th style="width: 15%">Date Released</th>
            <th style="width: 10%">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && released.data.length === 0">
            <td colspan="8" class="text-center"><div class="loader-spinner"></div>Loading...</td>
          </tr>
          <tr v-else-if="!loading && released.data.length === 0">
            <td colspan="8" class="text-center">
              <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #9ca3af"></i>
                <p class="mt-2 text-muted">
                  {{ searchQuery || docTypeFilter ? "No documents match your filters" : "No Released Data Found" }}
                </p>
              </div>
            </td>
          </tr>
          <tr v-for="(doc, index) in released.data" :key="doc.id">
            <td class="text-center"><span class="row-number">{{ (released.current_page - 1) * released.per_page + index + 1 }}</span></td>
            <td><span class="tracking-number">{{ doc.tracking_number }}</span></td>
            <td><span class="doc-type-badge">{{ doc.document_type?.document_type_name || doc.document_type }}</span></td>
            <td><div class="subject-text">{{ doc.subject }}</div></td>
            <td>
              <div class="date-received">
                <i class="bi bi-calendar3 date-icon"></i>
                {{ formatDate(doc.date_receive) }} At {{ formatTime(doc.time_receive) }}
              </div>
            </td>
            <td>
              <div class="date-received">
                <i class="bi bi-calendar3 date-icon"></i>
                {{ formatDate(doc.date_release) }} At {{ formatTime(doc.time_release) }}
              </div>
            </td>
            <td>
              <div class="action-buttons">
                <button class="btn-action btn-view" title="View Details" @click="viewDocument(doc)"><i class="bi bi-eye"></i></button>
                <button class="btn-action btn-forward" title="Release Document" @click="openReleaseModal(doc)"><i class="bi bi-check-circle"></i></button>
                <button class="btn-action btn-forward" title="Forward Office" @click="openForwardModal(doc)"><i class="bi bi-arrow-right-circle"></i></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <PaginationComponent
      :current-page="Number(released.current_page)"
      :total-pages="Number(released.last_page)"
      :total="Number(released.total)"
      :per-page="Number(released.per_page)"
      :from="Number(released.from)"
      :to="Number(released.to)"
      @page-change="changePage"
    />

    <!-- FORWARD MODAL -->
    <div v-if="showForwardModal" class="modal-overlay" @click.self="closeForwardModal">
      <div class="modal-dialog enhanced-modal" style="max-width: 700px">
        <div class="modal-content square-modal">
           <div class="modal-header-enhanced square-header document-header">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon" style="background: rgba(255,255,255,0.2)">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
              <div><h5 class="modal-title">Forward Document</h5></div>
            </div>
            <button type="button" class="btn-close-custom square-close" :disabled="forwarding" @click="closeForwardModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body-enhanced">
            <div v-if="forwardError" class="error-msg" role="alert">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none"/>
              </svg>
              <span>{{ forwardError }}</span>
            </div>

            <div class="forward-doc-info">
              <div class="forward-doc-row">
                <span class="forward-doc-label">Tracking Number:</span>
                <span class="forward-doc-value">{{ forwardDocument?.tracking_number || "N/A" }}</span>
              </div>
              <div class="forward-doc-row">
                <span class="forward-doc-label">Document Type:</span>
                <span class="doc-type-badge">{{ forwardDocument?.document_type?.document_type_name || forwardDocument?.document_type || "N/A" }}</span>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label-enhanced">Select Destination Office(s) <span class="required-star">*</span></label>

              <div v-if="officesLoading" class="text-center py-4">
                <div class="loader-spinner"></div>
                <p class="mt-2 text-muted">Loading offices...</p>
              </div>

              <div v-else-if="offices.length === 0" class="text-center py-4">
                <i class="bi bi-building" style="font-size: 2rem; color: #9ca3af"></i>
                <p class="mt-2 text-muted">No offices available</p>
              </div>

              <div v-else class="office-list-container">
                <div v-for="office in offices" :key="office.id" class="office-select-item" 
                     :class="{ selected: isOfficeSelected(office.id) }" @click="toggleOfficeSelection(office)">
                  <div class="office-checkbox">
                    <i :class="isOfficeSelected(office.id) ? 'bi bi-check-square-fill' : 'bi bi-square'"></i>
                  </div>
                  <div class="office-icon-wrapper"><i class="bi bi-building"></i></div>
                  <div class="office-details">
                    <span class="office-name-text">{{ office.sub_office_name }}</span>
                    <span v-if="office.office_code" class="office-code">{{ office.office_code }}</span>
                  </div>
                  <div v-if="isOfficeSelected(office.id)" class="selected-indicator">
                    <i class="bi bi-check-circle-fill"></i>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="selectedOffices.length > 0" class="selected-offices-summary mt-3">
              <div class="selected-offices-header">
                <i class="bi bi-check2-all"></i>
                <span>Selected Offices ({{ selectedOffices.length }})</span>
              </div>
              <div class="selected-offices-list">
                <span v-for="office in selectedOffices" :key="office.id" class="selected-office-tag">
                  <i class="bi bi-building"></i> {{ office.sub_office_name }}
                  <button class="remove-office-btn" :disabled="forwarding" @click="removeOffice(office.id)">
                    <i class="bi bi-x"></i>
                  </button>
                </span>
              </div>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn btn-outline-secondary square-btn" :disabled="forwarding" @click="clearForwardForm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
              </button>
              <div class="d-flex gap-3">
                <button type="button" class="btn btn-light square-btn" :disabled="forwarding" @click="closeForwardModal">Cancel</button>
                <button type="button" class="btn btn-save square-btn" style="background: linear-gradient(135deg, #2563eb, #1d4ed8)"
                        :disabled="forwarding || selectedOffices.length === 0" @click="submitForwardDocument">
                  <span v-if="forwarding" class="spinner-border spinner-border-sm me-1" role="status"></span>
                  <i v-else class="bi bi-send-check me-1"></i>
                  {{ forwarding ? "Forwarding..." : "Forward Document" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RELEASE MODAL -->
    <div v-if="showReleaseModal" class="modal-overlay" @click.self="closeReleaseModal">
      <div class="modal-dialog enhanced-modal release-modal">
        <div class="modal-content square-modal">
          <div class="modal-header-enhanced square-header document-header">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon" style="background: rgba(255,255,255,0.2)">
                <i class="bi bi-check-circle"></i>
              </div>
              <div>
                <h5 class="modal-title">Release Document</h5>
                <small class="modal-subtitle">Confirm document release</small>
              </div>
            </div>
            <button type="button" class="btn-close-custom square-close" :disabled="releasing" @click="closeReleaseModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body-enhanced release-modal-body">
            <div v-if="releaseError" class="error-msg" role="alert">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none"/>
              </svg>
              <span>{{ releaseError }}</span>
            </div>

            <div class="release-doc-info-grid">
              <div class="release-info-card">
                <div class="release-info-label"><i class="bi bi-upc-scan"></i> Tracking Number</div>
                <div class="release-info-value tracking-value">{{ releaseDocument?.tracking_number || "N/A" }}</div>
              </div>
              <div class="release-info-card">
                <div class="release-info-label"><i class="bi bi-file-earmark-text"></i> Document Type</div>
                <div class="release-info-value">
                  <span class="doc-type-badge">{{ releaseDocument?.document_type?.document_type_name || releaseDocument?.document_type || "N/A" }}</span>
                </div>
              </div>
              <div class="release-info-card">
                <div class="release-info-label"><i class="bi bi-tags"></i> Classification</div>
                <div class="release-info-value">{{ releaseDocument?.document_classification || "N/A" }}</div>
              </div>
              <div class="release-info-card">
                <div class="release-info-label"><i class="bi bi-journal-text"></i> Subject</div>
                <div class="release-info-value subject-value">{{ releaseDocument?.subject || releaseDocument?.title || "N/A" }}</div>
              </div>
            </div>

            <div class="release-datetime-section">
              <label class="form-label-enhanced"><i class="bi bi-calendar-check me-2"></i> Release Date & Time <span class="required-star">*</span></label>
              <div class="release-datetime-grid">
                <div class="form-group">
                  <label class="form-label-sm">Date</label>
                  <input type="date" class="form-input" v-model="releaseForm.date" :disabled="releasing" required />
                </div>
                <div class="form-group">
                  <label class="form-label-sm">Time</label>
                  <input type="time" class="form-input" v-model="releaseForm.time" :disabled="releasing" required />
                </div>
              </div>
            </div>

            <div class="release-upload-section">
              <label class="form-label-enhanced"><i class="bi bi-cloud-upload me-2"></i> Upload Released Attachment <span class="required-star">*</span></label>
              <div class="release-upload-area" :class="{ 'drag-over': isDragging, 'has-file': releaseForm.attachment }"
                   @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                   @drop.prevent="handleDrop" @click="triggerFileInput">
                <div class="release-upload-content">
                  <div v-if="!releaseForm.attachment" class="upload-placeholder">
                    <div class="upload-icon-wrapper"><i class="bi bi-cloud-arrow-up"></i></div>
                    <h6 class="upload-title">Drop your file here or click to browse</h6>
                    <p class="upload-subtitle">Supports PDF files only (Max 10MB)</p>
                  </div>
                  <div v-else class="upload-file-preview">
                    <div class="upload-file-icon"><i class="bi bi-file-earmark-pdf"></i></div>
                    <div class="upload-file-info">
                      <span class="upload-file-name">{{ releaseForm.attachment.name }}</span>
                      <span class="upload-file-size">{{ formatFileSize(releaseForm.attachment.size) }}</span>
                    </div>
                    <button type="button" class="upload-remove-btn" :disabled="releasing" @click.stop="removeAttachment">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                </div>
                <input ref="fileInput" type="file" class="release-file-input" accept=".pdf,application/pdf" :disabled="releasing" @change="handleFileChange" />
              </div>  <br>
              <div v-if="uploadProgress > 0 && uploadProgress < 100" class="upload-progress">
                <div class="progress-bar" :style="{ width: uploadProgress + '%' }"></div>
                <span class="progress-text">{{ uploadProgress }}%</span>
              </div>
            </div>

            <div class="release-remarks-section">
              <label class="form-label-enhanced"><i class="bi bi-chat-left-text me-2"></i> Remarks (Optional)</label>
              <textarea class="form-input form-textarea" v-model="releaseForm.remarks" :disabled="releasing" rows="3"
                        placeholder="Enter any additional remarks for this release..."></textarea>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn btn-outline-secondary square-btn" :disabled="releasing" @click="closeReleaseModal">
                <i class="bi bi-x-lg me-1"></i> Cancel
              </button>
              <button type="button" class="btn btn-save square-btn" style="background: linear-gradient(135deg, #059669, #047857)"
                      :disabled="releasing || !releaseForm.date || !releaseForm.time || !releaseForm.attachment"
                      @click="submitReleaseDocument">
                <span v-if="releasing" class="spinner-border spinner-border-sm me-1" role="status"></span>
                <i v-else class="bi bi-check-circle me-1"></i>
                {{ releasing ? "Releasing..." : "Confirm Release" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- DOCUMENT VIEWER MODAL -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="closeViewModal">
      <div class="modal-dialog enhanced-modal document-view-modal">
        <div class="modal-content square-modal">
          <div class="modal-header-enhanced square-header document-header">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon document-icon"><i class="bi bi-file-earmark-pdf"></i></div>
              <div>
                <h5 class="modal-title">Document Viewer</h5>
                <small class="modal-subtitle">
                  <span class="tracking-badge">{{ selectedDocument?.tracking_number }}</span>
                 
                </small>
              </div>
            </div>
            <button type="button" class="btn-close-custom square-close" @click="closeViewModal"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="modal-body-enhanced document-viewer-body">
            <!-- Tabs -->
            <div class="document-viewer-tabs">
              <button class="viewer-tab-btn" :class="{ active: viewerActiveTab === 'summary' }" @click="viewerActiveTab = 'summary'">
                <i class="bi bi-info-circle-fill"></i><span>Document Summary</span>
              </button>
              <button class="viewer-tab-btn" :class="{ active: viewerActiveTab === 'incoming' }" @click="viewerActiveTab = 'incoming'">
                <i class="bi bi-file-earmark-pdf"></i><span>Incoming Document</span>
              </button>
              <button class="viewer-tab-btn" :class="{ active: viewerActiveTab === 'released' }" @click="viewerActiveTab = 'released'">
                <i class="bi bi-check-circle-fill"></i><span>Released Document</span>
              </button>
              <button class="viewer-tab-btn" :class="{ active: viewerActiveTab === 'history' }" @click="viewerActiveTab = 'history'">
                <i class="bi bi-clock-history"></i><span>Office History</span>
              </button>
            </div>

            <!-- Summary Tab -->
            <div v-show="viewerActiveTab === 'summary'" class="document-summary-layout">
              <div class="summary-panel">
                <div class="summary-panel-header"><i class="bi bi-info-circle-fill"></i> Document Summary</div>
                <div v-if="selectedDocument" class="summary-content">
                  <div class="summary-section-title"><i class="bi bi-file-earmark-text"></i> Document Information</div>

                  <div class="summary-grid">
                    <div class="summary-card">
                      <div class="summary-icon-wrapper"><i class="bi bi-upc-scan"></i></div>
                      <div class="summary-info">
                        <label>Tracking Number</label>
                        <span class="tracking-number-large">{{ selectedDocument.tracking_number }}</span>
                      </div>
                    </div>

                    <div class="summary-card">
                      <div class="summary-icon-wrapper type-icon"><i class="bi bi-file-earmark-text"></i></div>
                      <div class="summary-info">
                        <label>Document Type</label>
                        <span class="doc-type-badge-large">{{ selectedDocument.document_type?.document_type_name || selectedDocument.document_type }}</span>
                      </div>
                    </div>

                    <div class="summary-card full-width">
                      <div class="summary-icon-wrapper subject-icon"><i class="bi bi-journal-text"></i></div>
                      <div class="summary-info">
                        <label>Subject / Title</label>
                        <span class="summary-value">{{ selectedDocument.subject || selectedDocument.title }}</span>
                      </div>
                    </div>

                    <div class="summary-card">
                      <div class="summary-icon-wrapper sender-icon"><i class="bi bi-person-badge"></i></div>
                      <div class="summary-info">
                        <label>Sender / Origin</label>
                        <span class="summary-value">{{ selectedDocument.sender_name || selectedDocument.origin }}</span>
                      </div>
                    </div>

                    <div class="summary-card">
                      <div class="summary-icon-wrapper classification-icon"><i class="bi bi-tags"></i></div>
                      <div class="summary-info">
                        <label>Document Classification</label>
                        <span class="summary-value classification-value">{{ selectedDocument.document_classification || "N/A" }}</span>
                      </div>
                    </div>

                    <div class="summary-card">
                      <div class="summary-icon-wrapper date-icon"><i class="bi bi-calendar-check"></i></div>
                      <div class="summary-info">
                        <label>Date Received</label>
                        <span class="summary-value">
                          {{ formatDate(selectedDocument.date_receive || selectedDocument.date_received || selectedDocument.created_at) }}
                          <small v-if="selectedDocument.time_receive" class="time-text">at {{ formatTime(selectedDocument.time_receive) }}</small>
                        </span>
                      </div>
                    </div>

                    <div class="summary-card">
                      <div class="summary-icon-wrapper release-icon"><i class="bi bi-calendar-check-fill"></i></div>
                      <div class="summary-info">
                        <label>Date Released</label>
                        <span class="summary-value">
                          {{ formatDate(selectedDocument.date_release) }}
                          <small v-if="selectedDocument.time_release" class="time-text">at {{ formatTime(selectedDocument.time_release) }}</small>
                        </span>
                      </div>
                    </div>

                    <div v-if="selectedDocument.description" class="summary-card full-width">
                      <div class="summary-icon-wrapper desc-icon"><i class="bi bi-blockquote-right"></i></div>
                      <div class="summary-info">
                        <label>Description</label>
                        <p class="summary-value description-text">{{ selectedDocument.description }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="summary-section-title"><i class="bi bi-graph-up-arrow"></i> Document Statistics</div>
                  <div class="summary-stats-grid">
                    <div class="summary-stat-card">
                      <div class="stat-icon-wrapper processing-icon"><i class="bi bi-clock-history"></i></div>
                      <div class="stat-info">
                        <span class="stat-label">Processing Time</span>
                        <span class="stat-value">{{ getWorkingDays(selectedDocument.date_receive, selectedDocument.date_release) }} <span class="stat-unit">days</span></span>
                      </div>
                    </div>

                    <div class="summary-stat-card">
                      <div class="stat-icon-wrapper routes-icon"><i class="bi bi-diagram-3"></i></div>
                      <div class="stat-info">
                        <span class="stat-label">Office Routes</span>
                        <span class="stat-value">{{ getOfficeRoutesCount(selectedDocument) }} <span class="stat-unit">route(s)</span></span>
                      </div>
                    </div>

                    <div class="summary-stat-card">
                      <div class="stat-icon-wrapper calendar-icon"><i class="bi bi-calendar-range"></i></div>
                      <div class="stat-info">
                        <span class="stat-label">Total Days</span>
                        <span class="stat-value">{{ getTotalDays(selectedDocument.date_receive, selectedDocument.date_release) }} <span class="stat-unit">days</span></span>
                      </div>
                    </div>

                    <div class="summary-stat-card">
                      <div class="stat-icon-wrapper holiday-icon"><i class="bi bi-calendar-x"></i></div>
                      <div class="stat-info">
                        <span class="stat-label">Non-Working Days</span>
                        <span class="stat-value">{{ getNonWorkingDays(selectedDocument.date_receive, selectedDocument.date_release) }} <span class="stat-unit">skipped</span></span>
                      </div>
                    </div>

                    <div class="summary-stat-card">
                      <div class="stat-icon-wrapper age-icon"><i class="bi bi-hourglass-split"></i></div>
                      <div class="stat-info">
                        <span class="stat-label">Document Age</span>
                        <span class="stat-value">{{ getDocumentAgeDisplay(selectedDocument.date_receive) }}</span>
                      </div>
                    </div>

                   
                  </div>

                  <div class="summary-section-title"><i class="bi bi-info-circle"></i> Status</div>
                  <div class="summary-card full-width">
                    <div class="summary-icon-wrapper meta-icon"><i class="bi bi-info-circle"></i></div>
                    <div class="summary-info">
                      <label>Current Status</label>
                      <span :class="['status-badge', getStatusClass(selectedDocument?.status)]">{{ selectedDocument?.status || "Unknown" }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Incoming Tab -->
            <div v-show="viewerActiveTab === 'incoming'" class="incoming-document-panel">
             
              <!-- Incoming Document Info -->
              <div v-if="selectedDocument" class="incoming-info-summary">
                <div class="incoming-info-grid">
                  <div class="incoming-info-item">
                    <span class="incoming-info-label">Date Received</span>
                    <span class="incoming-info-value">
                      <i class="bi bi-calendar-check"></i>
                      {{ formatDate(selectedDocument.date_receive || selectedDocument.date_received || selectedDocument.created_at) }}
                      <small v-if="selectedDocument.time_receive">at {{ formatTime(selectedDocument.time_receive) }}</small>
                    </span>
                  </div>
                  <div class="incoming-info-item">
                    <span class="incoming-info-label">Received By</span>
                    <span class="incoming-info-value">
                      <i class="bi bi-person-check"></i>
                      {{ getFullName(selectedDocument.received_by) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="incoming-content">
                <div class="incoming-pdf-viewer">
                  <div class="incoming-pdf-header">
                    <div class="incoming-pdf-title"><i class="bi bi-file-pdf"></i> Document Preview</div>
                    <div class="incoming-pdf-controls">
                      <button class="btn-pdf-control" title="Zoom In" :disabled="incomingPdfLoadError" @click="zoomInIncoming">
                        <i class="bi bi-zoom-in"></i>
                      </button>
                      <button class="btn-pdf-control" title="Zoom Out" :disabled="incomingPdfLoadError" @click="zoomOutIncoming">
                        <i class="bi bi-zoom-out"></i>
                      </button>
                      <a v-if="getIncomingPdfUrl()" :href="getIncomingPdfUrl()" download class="btn-toolbar-download">
                        <i class="bi bi-download"></i> Download
                      </a>
                    </div>
                  </div>
                  <div class="incoming-pdf-wrapper">
                    <div v-if="incomingPdfLoading && !incomingPdfLoadError" class="pdf-state">
                      <div class="pdf-loader-animation">
                        <div class="pdf-loader-icon"><i class="bi bi-file-pdf"></i></div>
                        <div class="loader-spinner"></div>
                      </div>
                      <p class="pdf-state-text">Loading incoming document...</p>
                    </div>
                    <iframe v-show="!incomingPdfLoading && !incomingPdfLoadError" :src="getIncomingPdfUrl()" class="pdf-iframe"
                            :style="{ width: `${100 / incomingPdfZoom}%`, height: `${incomingPdfViewerHeight}px` }"
                            frameborder="0" @load="onIncomingPdfLoaded" @error="handleIncomingPdfError"></iframe>
                    <div v-if="incomingPdfLoadError" class="pdf-state pdf-error">
                      <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #ef4444"></i>
                      <h5 class="mt-3">Document Not Available</h5>
                      <p class="text-muted">The incoming document could not be loaded or doesn't exist.</p>
                      <button class="btn btn-outline-secondary btn-sm mt-3" @click="retryIncomingPdfLoad">
                        <i class="bi bi-arrow-repeat me-1"></i> Retry
                      </button>
                    </div>
                  </div>
                  <div v-if="!incomingPdfLoadError" class="pdf-footer">
                    <span class="pdf-zoom-level">Zoom: {{ Math.round(incomingPdfZoom * 100) }}%</span>
                    <span v-if="selectedDocument" class="pdf-page-info">File: {{ selectedDocument.tracking_number }}_draft.pdf</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Released Tab -->
            <div v-show="viewerActiveTab === 'released'" class="released-document-panel">
              <div class="released-document-header">
                <div class="released-document-title"><i class="bi bi-check-circle-fill"></i> Released Document</div>
                <div class="released-document-badge"><i class="bi bi-check2-circle"></i> Released</div>
              </div>
              <div class="released-document-content">
                <div v-if="selectedDocument" class="release-info-summary">
                  <div class="release-info-grid">
                    <div class="release-info-item">
                      <span class="release-info-label">Released Date</span>
                      <span class="release-info-value">
                        <i class="bi bi-calendar-check"></i> {{ formatDate(selectedDocument.date_release || selectedDocument.released_at) }}
                        <small v-if="selectedDocument.time_release">at {{ formatTime(selectedDocument.time_release) }}</small>
                      </span>
                    </div>
                    <div class="release-info-item">
                      <span class="release-info-label">Released By</span>
                      <span class="release-info-value">
                        <i class="bi bi-person-check"></i> 
                        {{ getFullName(selectedDocument.released_by) }}
                      </span>
                    </div>
                    <div class="release-info-item" v-if="selectedDocument.release_remarks">
                      <span class="release-info-label">Remarks</span>
                      <span class="release-info-value"><i class="bi bi-chat-left-text"></i> {{ selectedDocument.release_remarks }}</span>
                    </div>
                    <div class="release-info-item">
                      <span class="release-info-label">Processing Time</span>
                      <span class="release-info-value"><i class="bi bi-clock-history"></i> {{ getWorkingDays(selectedDocument.date_receive, selectedDocument.date_release) }} days</span>
                    </div>
                    <div class="release-info-item">
                      <span class="release-info-label">Office Routes</span>
                      <span class="release-info-value"><i class="bi bi-diagram-3"></i> {{ getOfficeRoutesCount(selectedDocument) }} route(s)</span>
                    </div>
                  </div>
                </div>

                <div class="released-pdf-viewer">
                  <div class="released-pdf-header">
                    <div class="released-pdf-title"><i class="bi bi-file-pdf"></i> Released Document Preview</div>
                    <div class="released-pdf-controls">
                      <button class="btn-pdf-control" title="Zoom In" :disabled="releasedPdfLoadError" @click="zoomInReleased">
                        <i class="bi bi-zoom-in"></i>
                      </button>
                      <button class="btn-pdf-control" title="Zoom Out" :disabled="releasedPdfLoadError" @click="zoomOutReleased">
                        <i class="bi bi-zoom-out"></i>
                      </button>
                      <a v-if="getReleasedPdfUrl()" :href="getReleasedPdfUrl()" download class="btn-toolbar-download">
                        <i class="bi bi-download"></i> Download
                      </a>
                    </div>
                  </div>
                  <div class="released-pdf-wrapper">
                    <div v-if="releasedPdfLoading && !releasedPdfLoadError" class="pdf-state">
                      <div class="pdf-loader-animation">
                        <div class="pdf-loader-icon"><i class="bi bi-file-pdf"></i></div>
                        <div class="loader-spinner"></div>
                      </div>
                      <p class="pdf-state-text">Loading released document...</p>
                    </div>
                    <iframe v-show="!releasedPdfLoading && !releasedPdfLoadError" :src="getReleasedPdfUrl()" class="pdf-iframe"
                            :style="{ width: `${100 / releasedPdfZoom}%`, height: `${releasedPdfViewerHeight}px` }"
                            frameborder="0" @load="onReleasedPdfLoaded" @error="handleReleasedPdfError"></iframe>
                    <div v-if="releasedPdfLoadError" class="pdf-state pdf-error">
                      <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #ef4444"></i>
                      <h5 class="mt-3">PDF Not Available</h5>
                      <p class="text-muted">The released document could not be loaded.</p>
                      <button class="btn btn-outline-secondary btn-sm mt-3" @click="retryReleasedPdfLoad">
                        <i class="bi bi-arrow-repeat me-1"></i> Retry
                      </button>
                    </div>
                  </div>
                  <div v-if="!releasedPdfLoadError" class="pdf-footer">
                    <span class="pdf-zoom-level">Zoom: {{ Math.round(releasedPdfZoom * 100) }}%</span>
                    <span v-if="selectedDocument" class="pdf-page-info">File: {{ selectedDocument.tracking_number }}_released.pdf</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- History Tab -->
            <div v-show="viewerActiveTab === 'history'" class="route-history-panel">
              <div class="route-history-header">
                <div class="route-history-title"><i class="bi bi-clock-history"></i> Route History</div>
                <div v-if="selectedDocument?.document_route?.length > 0" class="route-history-badge">
                  <i class="bi bi-arrow-left-right"></i> {{ selectedDocument.document_route.length }} Route{{ selectedDocument.document_route.length !== 1 ? "s" : "" }}
                </div>
              </div>
              <div class="route-history-content">
                <div v-if="routeHistoryLoading" class="route-state-box"><div class="loader-spinner"></div><p class="mt-3 text-muted">Loading...</p></div>
                <div v-else-if="!selectedDocument?.document_route?.length" class="route-state-box">
                  <div class="empty-icon-wrapper"><i class="bi bi-signpost-2"></i></div>
                  <h5 class="mt-3">No Route History</h5>
                  <p class="text-muted">This document hasn't been routed yet.</p>
                </div>
                <div v-else class="timeline-container">
                  <div v-for="(route, rIndex) in selectedDocument.document_route" :key="rIndex" class="timeline-item" :class="{ 'is-last': rIndex === selectedDocument.document_route.length - 1 }">
                    <div class="timeline-node" :class="getRouteNodeClass(route.status)">
                      <i :class="getRouteStatusIcon(route.status)"></i>
                    </div>
                    <div class="timeline-card" :class="{ 'active-card': isRouteActive(route) }">
                      <div class="timeline-card-header">
                        <div class="office-info">
                          <i class="bi bi-building-fill"></i>
                          <div class="office-text">
                            <span class="office-name">{{ route.office?.sub_office_name || route.sub_office_name || "Unknown Office" }}</span>
                          </div>
                        </div>
                        <span class="route-status-badge" :class="getRouteStatusClass(route.status)">
                          <i :class="getRouteStatusIcon(route.status)"></i> {{ route.status || "PENDING" }}
                        </span>
                      </div>
                      <div class="timeline-card-body">
                        <div class="info-grid-enhanced">
                          <div v-if="route.date_receive" class="info-card received-card">
                            <div class="info-card-body">
                              <div class="info-field">
                                <div class="field-icon"><i class="bi bi-calendar-event"></i></div>
                                <div class="field-content">
                                  <span class="field-label">Received On</span>
                                  <span class="field-value received-date"><i class="bi bi-clock me-1"></i> {{ formatDateTime(route.date_receive) }}</span>
                                </div>
                              </div>
                              <div v-if="route.received_by || route.received_by_name" class="info-field">
                                <div class="field-icon"><i class="bi bi-person-check"></i></div>
                                <div class="field-content">
                                  <span class="field-label">Received By</span>
                                  <span class="field-value received-by">{{ getPersonName(route.received_by, route.received_by_name) }}</span>
                                </div>
                              </div>
                              <div v-if="route.remarks" class="info-field remarks-field">
                                <div class="field-icon remarks-icon"><i class="bi bi-chat-left-text"></i></div>
                                <div class="field-content">
                                  <span class="field-label">Remarks</span>
                                  <span class="field-value remarks-text">{{ route.remarks }}</span>
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
      </div>
    </div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import PaginationComponent from "../../components/PaginationComponent.vue";

export default {
  name: "ReleasedTab",
  components: { PaginationComponent },
  emits: ["view-document", "download-document", "show-notification"],
  props: {
    documentTypes: {
      type: Array,
      default: () => ["Memorandum", "Letter", "Report", "Request", "Permit", "Certificate"]
    }
  },
  data() {
    return {
      released: {
        data: [],
        current_page: 1,
        from: 1,
        to: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      },
      searchQuery: "",
      docTypeFilter: "",
      perPage: 10,
      currentPage: 1,
      loading: false,

      // Release
      showReleaseModal: false,
      releaseDocument: null,
      releaseForm: { date: "", time: "", remarks: "", attachment: null },
      releasing: false,
      releaseError: "",
      isDragging: false,
      uploadProgress: 0,

      // Forward
      showForwardModal: false,
      forwardDocument: null,
      offices: [],
      officesLoading: false,
      selectedOffices: [],
      forwarding: false,
      forwardError: "",

      // Viewer
      showViewModal: false,
      selectedDocument: null,
      viewerActiveTab: "summary",
      routeHistoryLoading: false,

      // PDF viewers
      incomingPdfLoading: true,
      incomingPdfLoadError: false,
      incomingPdfZoom: 1,
      incomingPdfViewerHeight: 700,
      releasedPdfLoading: true,
      releasedPdfLoadError: false,
      releasedPdfZoom: 1,
      releasedPdfViewerHeight: 700
    };
  },
  mounted() {
    this.getDataRelease();
  },
  methods: {
    // ===== UTILITY METHODS =====
    getFullName(person) {
      if (!person) return "N/A";
      const firstname = person.firstname || "";
      const middlename = person.middlename || "";
      const lastname = person.lastname || "";
      const fullName = [firstname, middlename, lastname].filter(Boolean).join(" ");
      return fullName || "N/A";
    },

    // ===== DATA FETCHING =====
    async getDataRelease(page = 1) {
      this.loading = true;
      try {
        const response = await axios.get("/dts_denr/api/released", {
          params: {
            page,
            per_page: this.perPage,
            search: this.searchQuery,
            document_type: this.docTypeFilter
          }
        });
        this.released = response.data.data;
      } catch (error) {
        console.error("Error fetching data:", error);
        this.$emit("show-notification", {
          message: "Failed to load data.",
          type: "error"
        });
      } finally {
        this.loading = false;
      }
    },

    // ===== FILTERS =====
    applyFilters() {
      this.currentPage = 1;
      this.getDataRelease(1);
    },
    clearSearch() {
      this.searchQuery = "";
      this.applyFilters();
    },
    clearDocTypeFilter() {
      this.docTypeFilter = "";
      this.applyFilters();
    },
    clearAllFilters() {
      this.searchQuery = "";
      this.docTypeFilter = "";
      this.applyFilters();
    },
    changePage(page) {
      if (page >= 1 && page <= this.released.last_page && page !== this.currentPage) {
        this.currentPage = page;
        this.getDataRelease(page);
      }
    },
    changePerPage() {
      this.currentPage = 1;
      this.getDataRelease(1);
    },

    // ===== FORWARD =====
    openForwardModal(doc) {
      this.forwardDocument = doc;
      this.showForwardModal = true;
      this.selectedOffices = [];
      this.forwardError = "";
      this.fetchOffices();
    },
    closeForwardModal() {
      this.showForwardModal = false;
      this.forwardDocument = null;
      this.selectedOffices = [];
      this.forwardError = "";
    },
    clearForwardForm() {
      this.selectedOffices = [];
      this.forwardError = "";
    },
    isOfficeSelected(id) {
      return this.selectedOffices.some((o) => o.id === id);
    },
    toggleOfficeSelection(office) {
      if (this.forwarding) return;
      this.isOfficeSelected(office.id) ? this.removeOffice(office.id) : this.selectedOffices.push(office);
    },
    removeOffice(id) {
      this.selectedOffices = this.selectedOffices.filter((o) => o.id !== id);
    },

    async fetchOffices() {
      this.officesLoading = true;
      try {
        const response = await axios.get("/dts_denr/api/route-office-other");
        this.offices = response.data.data || [];
      } catch (error) {
        this.forwardError = "Failed to load offices.";
      } finally {
        this.officesLoading = false;
      }
    },

    async submitForwardDocument() {
      if (!this.selectedOffices.length) {
        this.forwardError = "Please select at least one office.";
        return;
      }
      this.forwarding = true;
      this.forwardError = "";
      try {
        await axios.post("/dts_denr/api/add-forward-document", {
          document_id: this.forwardDocument.id,
          tracking_number: this.forwardDocument.tracking_number,
          offices: this.selectedOffices.map((o) => o.id)
        });
        await Swal.fire({
          title: "Forwarded!",
          icon: "success",
          confirmButtonColor: "#1a4731",
          confirmButtonText: "OK"
        });
        this.closeForwardModal();
        this.$emit("show-notification", {
          message: "Document forwarded successfully!",
          type: "success"
        });
        this.getDataRelease(this.released.current_page);
      } catch (error) {
        this.forwardError = error.response?.data?.message || "Failed to forward document.";
        this.$emit("show-notification", {
          message: this.forwardError,
          type: "error"
        });
      } finally {
        this.forwarding = false;
      }
    },

    // ===== RELEASE =====
    openReleaseModal(doc) {
      this.releaseDocument = doc;
      this.showReleaseModal = true;
      this.releaseError = "";
      const now = new Date();
      this.releaseForm = {
        date: now.toISOString().split("T")[0],
        time: now.toTimeString().slice(0, 5),
        remarks: "",
        attachment: null
      };
      this.uploadProgress = 0;
    },
    closeReleaseModal() {
      this.showReleaseModal = false;
      this.releaseDocument = null;
      this.releaseError = "";
      this.releasing = false;
      this.releaseForm.attachment = null;
      this.uploadProgress = 0;
    },

    triggerFileInput() {
      if (!this.releasing) this.$refs.fileInput?.click();
    },
    handleFileChange(e) {
      if (e.target.files[0]) this.validateAndSetFile(e.target.files[0]);
    },
    handleDrop(e) {
      this.isDragging = false;
      if (e.dataTransfer.files[0]) this.validateAndSetFile(e.dataTransfer.files[0]);
    },
    validateAndSetFile(file) {
      if (file.type !== "application/pdf") {
        this.releaseError = "Please upload a PDF file only.";
        return;
      }
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
      if (!bytes) return "0 Bytes";
      const k = 1024,
        sizes = ["Bytes", "KB", "MB", "GB"];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    },

    async submitReleaseDocument() {
      if (!this.releaseForm.date || !this.releaseForm.time) {
        this.releaseError = "Please select date and time.";
        return;
      }
      if (!this.releaseForm.attachment) {
        this.releaseError = "Please upload an attachment.";
        return;
      }
      this.releasing = true;
      this.releaseError = "";
      this.uploadProgress = 0;
      try {
        const formData = new FormData();
        formData.append("document_id", this.releaseDocument.id);
        formData.append("tracking_number", this.releaseDocument.tracking_number);
        formData.append("date_released", this.releaseForm.date);
        formData.append("time_released", this.releaseForm.time);
        formData.append("remarks", this.releaseForm.remarks || "");
        formData.append("attachment", this.releaseForm.attachment);

        await axios.post("/dts_denr/api/release-document/" + this.releaseDocument.id, formData, {
          headers: { "Content-Type": "multipart/form-data" },
          onUploadProgress: (e) => {
            if (e.total) this.uploadProgress = Math.round((e.loaded / e.total) * 100);
          }
        });
        await Swal.fire({
          title: "Document released successfully!",
          icon: "success",
          confirmButtonColor: "#059669",
          confirmButtonText: "OK"
        });
        this.closeReleaseModal();
        this.$emit("show-notification", {
          message: "Document released successfully!",
          type: "success"
        });
        this.getDataRelease(this.released.current_page);
      } catch (error) {
        this.releaseError = error.response?.data?.message || "Failed to release document.";
        this.$emit("show-notification", {
          message: this.releaseError,
          type: "error"
        });
      } finally {
        this.releasing = false;
        this.uploadProgress = 0;
      }
    },

    // ===== VIEWER =====
    viewDocument(doc) {
      this.selectedDocument = doc;
      this.showViewModal = true;
      this.viewerActiveTab = "summary";
      this.incomingPdfLoading = true;
      this.incomingPdfLoadError = false;
      this.incomingPdfZoom = 1;
      this.incomingPdfViewerHeight = 700;
      this.releasedPdfLoading = true;
      this.releasedPdfLoadError = false;
      this.releasedPdfZoom = 1;
      this.releasedPdfViewerHeight = 700;
      this.routeHistoryLoading = false;
      this.$emit("view-document", doc);
    },
    closeViewModal() {
      this.showViewModal = false;
      this.selectedDocument = null;
      this.viewerActiveTab = "summary";
    },

    // ===== PDF HELPERS =====
    getIncomingPdfUrl() {
      if (!this.selectedDocument) return "";
      if (this.selectedDocument.draft_attachment)
        return `/dts_denr/storage/app/public/${this.selectedDocument.draft_attachment}`;
      return `/dts_denr/storage/app/public/attachments/${this.selectedDocument.tracking_number}/draft_attachment.pdf`;
    },
    getReleasedPdfUrl() {
      if (!this.selectedDocument) return "";
      if (this.selectedDocument.released_attachment)
        return `/dts_denr/storage/app/public/${this.selectedDocument.released_attachment}`;
      if (this.selectedDocument.release_attachment)
        return `/dts_denr/storage/app/public/${this.selectedDocument.release_attachment}`;
      return `/dts_denr/storage/app/public/attachments/${this.selectedDocument.tracking_number}/release_attachment.pdf`;
    },
    onIncomingPdfLoaded() {
      this.incomingPdfLoading = false;
      this.incomingPdfLoadError = false;
    },
    handleIncomingPdfError() {
      this.incomingPdfLoading = false;
      this.incomingPdfLoadError = true;
    },
    retryIncomingPdfLoad() {
      this.incomingPdfLoading = true;
      this.incomingPdfLoadError = false;
      this.$nextTick(() => {
        const iframe = document.querySelector(".incoming-pdf-wrapper iframe");
        if (iframe) iframe.src = iframe.src;
      });
    },
    onReleasedPdfLoaded() {
      this.releasedPdfLoading = false;
      this.releasedPdfLoadError = false;
    },
    handleReleasedPdfError() {
      this.releasedPdfLoading = false;
      this.releasedPdfLoadError = true;
    },
    retryReleasedPdfLoad() {
      this.releasedPdfLoading = true;
      this.releasedPdfLoadError = false;
      this.$nextTick(() => {
        const iframe = document.querySelector(".released-pdf-wrapper iframe");
        if (iframe) iframe.src = iframe.src;
      });
    },
    zoomInIncoming() {
      if (this.incomingPdfZoom < 2) {
        this.incomingPdfZoom += 0.25;
        this.incomingPdfViewerHeight = Math.round(700 / this.incomingPdfZoom);
      }
    },
    zoomOutIncoming() {
      if (this.incomingPdfZoom > 0.5) {
        this.incomingPdfZoom -= 0.25;
        this.incomingPdfViewerHeight = Math.round(700 / this.incomingPdfZoom);
      }
    },
    zoomInReleased() {
      if (this.releasedPdfZoom < 2) {
        this.releasedPdfZoom += 0.25;
        this.releasedPdfViewerHeight = Math.round(700 / this.releasedPdfZoom);
      }
    },
    zoomOutReleased() {
      if (this.releasedPdfZoom > 0.5) {
        this.releasedPdfZoom -= 0.25;
        this.releasedPdfViewerHeight = Math.round(700 / this.releasedPdfZoom);
      }
    },

    // ===== DATE HELPERS =====
    formatDate(date) {
      return date
        ? new Date(date).toLocaleDateString("en-PH", {
            year: "numeric",
            month: "short",
            day: "numeric"
          })
        : "N/A";
    },
    formatTime(t) {
      if (!t) return "";
      if (typeof t === "string" && (t.includes("AM") || t.includes("PM"))) return t;
      const [h, m] = t.split(":").map(Number);
      return `${h % 12 || 12}:${m.toString().padStart(2, "0")} ${h >= 12 ? "PM" : "AM"}`;
    },
    formatDateTime(date) {
      return date
        ? new Date(date).toLocaleString("en-PH", {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            hour12: true
          })
        : "N/A";
    },
    getPersonName(person, name) {
      if (person)
        return `${person.firstname || ""} ${person.middlename || ""} ${person.lastname || ""}`.trim();
      return name || "N/A";
    },

    // ===== WORKING DAYS CALCULATION =====
    isWeekend(date) {
      return date.getDay() === 6 || date.getDay() === 0;
    },
    isWorkingDay(date) {
      return !this.isWeekend(date);
    },
    getWorkingDays(start, end) {
      if (!start || !end) return 0;
      const s = new Date(start),
        e = new Date(end);
      s.setHours(0, 0, 0, 0);
      e.setHours(0, 0, 0, 0);
      if (s > e) return 0;
      let days = 0;
      for (let d = new Date(s); d <= e; d.setDate(d.getDate() + 1))
        if (this.isWorkingDay(d)) days++;
      return days;
    },
    getTotalDays(start, end) {
      if (!start || !end) return 0;
      const s = new Date(start),
        e = new Date(end);
      s.setHours(0, 0, 0, 0);
      e.setHours(0, 0, 0, 0);
      return s > e ? 0 : Math.ceil((e - s) / (1000 * 60 * 60 * 24)) + 1;
    },
    getNonWorkingDays(start, end) {
      return Math.max(0, this.getTotalDays(start, end) - this.getWorkingDays(start, end));
    },

    // ===== DOCUMENT AGE =====
    getDocumentAgeDisplay(date) {
      if (!date) return "N/A";
      const now = new Date();
      const d = new Date(date);
      d.setHours(0, 0, 0, 0);
      now.setHours(0, 0, 0, 0);

      if (d > now) return "0 days";

      const diffTime = Math.abs(now - d);
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays < 30) {
        return `${diffDays} ${diffDays === 1 ? "day" : "days"}`;
      } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30);
        const remainingDays = diffDays % 30;
        let result = `${months} ${months === 1 ? "month" : "months"}`;
        if (remainingDays > 0) {
          result += ` ${remainingDays} ${remainingDays === 1 ? "day" : "days"}`;
        }
        return result;
      } else {
        const years = Math.floor(diffDays / 365);
        const remainingMonths = Math.floor((diffDays % 365) / 30);
        const remainingDays = (diffDays % 365) % 30;
        let result = `${years} ${years === 1 ? "year" : "years"}`;
        if (remainingMonths > 0) {
          result += ` ${remainingMonths} ${remainingMonths === 1 ? "month" : "months"}`;
        }
        if (remainingDays > 0 && remainingMonths === 0) {
          result += ` ${remainingDays} ${remainingDays === 1 ? "day" : "days"}`;
        }
        return result;
      }
    },

    getOfficeRoutesCount(doc) {
      return doc?.document_route?.length || 0;
    },
 
    // ===== ROUTE HELPERS =====
    isRouteActive(route) {
      return ["In Progress", "Active", "Current"].includes(route.status) || route.is_current === true;
    },
    getRouteStatusType(status) {
      const s = (status || "").toLowerCase();
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
    getStatusClass(status) {
      const map = {
        "In-Progress": "status-in-progress",
        "For-Release": "status-for-release",
        Released: "status-released"
      };
      return map[status] || "";
    }
    
  }
};
</script>

<style scoped>
/* Modal Overlay */
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

/* Modal Dialogs */
.enhanced-modal {
  width: 100%;
  max-width: 620px;
  margin: 0 auto;
  animation: modalSlideUp 0.3s ease-out;
}
.release-modal {
  max-width: 700px;
}
.document-view-modal {
  max-width: 1400px;
  width: 95vw;
  max-height: 90vh;
}

/* Modal Content */
.square-modal {
  border: none;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
  background: #fff;
  max-height: 95vh;
  display: flex;
  flex-direction: column;
}

/* Modal Header */
.square-header {
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  color: white;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
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

/* Close Button */
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

/* Modal Body */
.modal-body-enhanced {
  padding: 20px;
  background: #f9fafb;
  overflow-y: auto;
  flex: 1;
}
.document-viewer-body {
  padding: 0;
  max-height: calc(90vh - 80px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Error Messages */
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

/* Form Elements */
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
  background: #fff;
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

/* Release Info Grid */
.release-doc-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 16px;
}
.release-info-card {
  background: #fff;
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

/* Release Date/Time */
.release-datetime-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 14px;
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

/* Upload Area */
.release-upload-area {
  border: 2px dashed #d1d5db;
  border-radius: 10px;
  padding: 24px 16px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #fafbfc;
  min-height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
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
.upload-file-preview {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 6px 10px;
  background: #fff;
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

/* Modal Actions */
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

/* Action Buttons */
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

/* Filters */
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
  background: #fff;
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
  background: #fff;
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
  background: #fff;
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

/* Active Filters */
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
  background: #fff;
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

/* Loader & Empty State */
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

/* Status Badges */
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

/* Document Viewer Tabs */
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
  background: #fff;
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
  color: #fff;
  box-shadow: 0 4px 12px rgba(45, 106, 79, 0.3);
}

/* Summary Panel */
.document-summary-layout {
  display: flex;
  height: calc(90vh - 140px);
  min-height: 400px;
  background: #f9fafb;
  padding: 20px;
  overflow-y: auto;
}
.summary-panel {
  flex: 1;
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.summary-panel-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.9rem;
  flex-shrink: 0;
}
.summary-panel-header i {
  color: #2d6a4f;
}
.summary-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px 20px;
}
.summary-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 8px;
}
.summary-card {
  display: flex;
  gap: 12px;
  padding: 10px 14px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  transition: all 0.2s ease;
}
.summary-card:hover {
  border-color: #cbd5e1;
  background: #f1f5f9;
}
.summary-card.full-width {
  grid-column: 1 / -1;
}
.summary-icon-wrapper {
  width: 36px;
  height: 36px;
  min-width: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  background: #e0f2fe;
  color: #0284c7;
}
.summary-icon-wrapper.type-icon {
  background: #fef3c7;
  color: #d97706;
}
.summary-icon-wrapper.subject-icon {
  background: #dcfce7;
  color: #16a34a;
}
.summary-icon-wrapper.sender-icon {
  background: #ede9fe;
  color: #7c3aed;
}
.summary-icon-wrapper.classification-icon {
  background: #cffafe;
  color: #0891b2;
}
.summary-icon-wrapper.date-icon {
  background: #fce7f3;
  color: #db2777;
}
.summary-icon-wrapper.desc-icon {
  background: #fff7ed;
  color: #ea580c;
}
.summary-icon-wrapper.meta-icon {
  background: #f1f5f9;
  color: #64748b;
}
.summary-icon-wrapper.release-icon {
  background: #d1fae5;
  color: #059669;
}
.summary-info {
  flex: 1;
  min-width: 0;
}
.summary-info label {
  display: block;
  font-size: 0.6rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}
.summary-value {
  font-size: 0.85rem;
  color: #1e293b;
  font-weight: 500;
  word-break: break-word;
  line-height: 1.4;
}
.classification-value {
  display: inline-block;
  padding: 2px 10px;
  background: #cffafe;
  color: #0891b2;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
  border: 1px solid #67e8f9;
}
.summary-section-title {
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 12px 0 8px 0;
  padding-bottom: 4px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 6px;
}
.summary-section-title i {
  color: #2d6a4f;
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
.description-text {
  font-size: 0.8rem;
  color: #475569;
  line-height: 1.5;
  margin: 0;
}

/* Statistics Grid */
.summary-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 10px;
  margin-bottom: 8px;
}
.summary-stat-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: linear-gradient(135deg, #f8fafc, #fff);
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  transition: all 0.3s ease;
}
.summary-stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  border-color: #93c5fd;
  transform: translateY(-1px);
}
.stat-icon-wrapper {
  width: 42px;
  height: 42px;
  min-width: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}
.processing-icon {
  background: #d1fae5;
  color: #059669;
}
.routes-icon {
  background: #e0e7ff;
  color: #3730a3;
}
.calendar-icon {
  background: #fef3c7;
  color: #d97706;
}
.holiday-icon {
  background: #fee2e2;
  color: #dc2626;
}
.age-icon {
  background: #fce7f3;
  color: #db2777;
}
.visited-icon {
  background: #ede9fe;
  color: #7c3aed;
}
.stat-info {
  flex: 1;
  min-width: 0;
}
.stat-label {
  display: block;
  font-size: 0.6rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.stat-value {
  display: block;
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
}
.stat-unit {
  font-size: 0.7rem;
  font-weight: 500;
  color: #94a3b8;
  margin-left: 2px;
}

/* Incoming Panel */
.incoming-document-panel {
  display: flex;
  flex-direction: column;
  height: calc(90vh - 140px);
  background: #fff;
  min-height: 400px;
}
.incoming-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  flex-shrink: 0;
}
.incoming-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.9rem;
}
.incoming-title i {
  color: #dc2626;
  font-size: 1.1rem;
}
.incoming-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 12px;
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 16px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #dc2626;
}

/* Incoming Info Summary */
.incoming-info-summary {
  padding: 12px 20px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
}
.incoming-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}
.incoming-info-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.incoming-info-label {
  font-size: 0.6rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.incoming-info-value {
  font-size: 0.85rem;
  font-weight: 500;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 6px;
}
.incoming-info-value i {
  color: #2563eb;
  font-size: 0.9rem;
}
.incoming-info-value small {
  font-size: 0.7rem;
  color: #64748b;
  font-weight: 400;
}

.incoming-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f9fafb;
}
.incoming-pdf-viewer {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #fff;
  overflow: hidden;
}
.incoming-pdf-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
  flex-wrap: wrap;
  gap: 8px;
}
.incoming-pdf-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.85rem;
}
.incoming-pdf-title i {
  color: #dc2626;
  font-size: 1.1rem;
}
.incoming-pdf-controls {
  display: flex;
  gap: 4px;
  align-items: center;
}
.incoming-pdf-wrapper {
  flex: 1;
  overflow: auto;
  background: #525659;
  position: relative;
  min-height: 300px;
}

/* PDF Viewer */
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
.pdf-iframe {
  border: none;
  display: block;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}
.pdf-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 16px;
  background: #fff;
  border-top: 1px solid #e5e7eb;
  font-size: 0.7rem;
  color: #64748b;
  flex-shrink: 0;
  flex-wrap: wrap;
  gap: 4px;
}
.pdf-zoom-level {
  font-weight: 600;
  color: #2d6a4f;
}
.pdf-page-info {
  font-weight: 500;
  color: #475569;
}

/* PDF Controls */
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
.btn-toolbar-download {
  background: #d1fae5;
  border: 1px solid #a7f3d0;
  color: #059669;
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
}
.btn-toolbar-download:hover {
  background: #a7f3d0;
  border-color: #059669;
  color: #047857;
}

/* Released Panel */
.released-document-panel {
  display: flex;
  flex-direction: column;
  height: calc(90vh - 140px);
  background: #fff;
  min-height: 400px;
}
.released-document-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  flex-shrink: 0;
}
.released-document-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.9rem;
}
.released-document-title i {
  color: #059669;
  font-size: 1.1rem;
}
.released-document-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 14px;
  background: #d1fae5;
  border: 1px solid #a7f3d0;
  border-radius: 16px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #059669;
}
.released-document-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #f9fafb;
}
.release-info-summary {
  padding: 12px 20px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
}
.release-info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}
.release-info-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.release-info-label {
  font-size: 0.6rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.release-info-value {
  font-size: 0.85rem;
  font-weight: 500;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 6px;
}
.release-info-value i {
  color: #059669;
  font-size: 0.9rem;
}
.release-info-value small {
  font-size: 0.7rem;
  color: #64748b;
  font-weight: 400;
}

.released-pdf-viewer {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #fff;
  overflow: hidden;
}
.released-pdf-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 16px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  flex-shrink: 0;
  flex-wrap: wrap;
  gap: 8px;
}
.released-pdf-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.85rem;
}
.released-pdf-title i {
  color: #059669;
  font-size: 1.1rem;
}
.released-pdf-controls {
  display: flex;
  gap: 4px;
  align-items: center;
}
.released-pdf-wrapper {
  flex: 1;
  overflow: auto;
  background: #525659;
  position: relative;
  min-height: 300px;
}

/* Route History */
.route-history-panel {
  display: flex;
  flex-direction: column;
  height: calc(90vh - 140px);
  background: #fff;
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
.route-state-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 200px;
}
.empty-icon-wrapper {
  font-size: 3rem;
  color: #9ca3af;
}

/* Timeline */
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
  border: 3px solid #fff;
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
  background: #fff;
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
  background: linear-gradient(135deg, #eff6ff, #fff);
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
  background: #fff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  transition: all 0.3s ease;
}
.received-card {
  border-left: 4px solid #3b82f6;
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
.remarks-text {
  font-style: italic;
  color: #78350f;
  background: #fffbeb;
  padding: 3px 8px;
  border-radius: 4px;
  border-left: 3px solid #f59e0b;
}

/* Forward Modal */
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
  background: #fff;
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
  background: #fff;
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
@keyframes pulse {
  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(37, 99, 235, 0.1);
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .document-view-modal {
    max-width: 98vw;
  }
  .summary-stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  }
  .info-grid-enhanced {
    grid-template-columns: 1fr;
  }
  .release-info-grid {
    grid-template-columns: 1fr;
  }
  .summary-grid {
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
  .release-doc-info-grid {
    grid-template-columns: 1fr;
  }
  .release-datetime-grid {
    grid-template-columns: 1fr;
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
    padding: 6px 10px;
    font-size: 10px;
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
  .timeline-container {
    padding-left: 32px;
  }
  .timeline-node {
    left: -28px;
    width: 22px;
    height: 22px;
    font-size: 0.6rem;
  }
  .released-pdf-header {
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
  }
  .incoming-pdf-header {
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
  }
  .incoming-pdf-controls {
    justify-content: flex-start;
  }
  .released-pdf-controls {
    justify-content: flex-start;
  }
  .incoming-info-grid {
    grid-template-columns: 1fr;
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
  .release-info-card {
    padding: 8px 10px;
  }
  .release-info-value {
    font-size: 0.75rem;
  }
  .upload-file-preview {
    flex-wrap: wrap;
  }
  .upload-file-icon {
    width: 32px;
    height: 32px;
    min-width: 32px;
    font-size: 1rem;
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
  .viewer-tab-btn {
    padding: 4px 8px;
    font-size: 9px;
  }
  .viewer-tab-btn i {
    font-size: 12px;
  }
  .summary-stats-grid {
    grid-template-columns: 1fr;
  }
  .released-document-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  .incoming-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
}
</style>