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
              <option v-for="type in documentTypes" :key="type" :value="type">
                {{ type }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div class="active-filters" v-if="searchQuery || docTypeFilter">
        <span class="active-filters-label">Active Filters:</span>
        <span v-if="searchQuery" class="filter-tag">
          <i class="bi bi-search"></i> "{{ searchQuery }}"
          <button @click="clearSearch" class="filter-tag-close">
            <i class="bi bi-x"></i>
          </button>
        </span>
        <span v-if="docTypeFilter" class="filter-tag">
          <i class="bi bi-funnel"></i> {{ docTypeFilter }}
          <button @click="clearDocTypeFilter" class="filter-tag-close">
            <i class="bi bi-x"></i>
          </button>
        </span>
        <button @click="clearAllFilters" class="clear-all-filters">
          Clear All
        </button>
      </div>

      <div class="results-summary">
        <span class="results-count">{{ documents.total }}</span>
        document(s) found in <strong>Received</strong>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="office-table">
        <thead>
          <tr>
            <th style="width: 4%">#</th>
            <th style="width: 15%">Tracking No.</th>
            <th style="width: 15%">Document Type</th>
            <th style="width: 20%">Subject/Title</th>
            <th style="width: 13%">Receive By</th>
            <th style="width: 13%">Date Received</th>
            <th style="width: 20%">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && documents.data.length === 0">
            <td colspan="7" class="text-center">
              <div class="loader-spinner"></div>
              Loading...
            </td>
          </tr>
          <tr v-else-if="!loading && documents.data.length === 0">
            <td colspan="7" class="text-center">
              <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #9ca3af"></i>
                <p class="mt-2 text-muted">
                  {{ searchQuery || docTypeFilter ? "No documents match your filters" : "No Received Documents" }}
                </p>
              </div>
            </td>
          </tr>
          <tr v-for="(document, index) in documents.data" :key="document.id">
            <td class="text-center">
              <span class="row-number">{{ (documents.current_page - 1) * documents.per_page + index + 1 }}</span>
            </td>
            <td>
              <span class="tracking-number">{{ document.document.tracking_number }}</span>
            </td>
            <td>
              <span class="doc-type-badge">{{ document.document.document_type?.document_type_name || document.document.document_type }}</span>
            </td>
            <td>
              <div 
                class="subject-text" 
                :class="{ 'confidential-blur-static': isDocumentConfidential(document.document.document_classification) }"
              >
                {{ document.document.subject }}
                <span v-if="isDocumentConfidential(document.document.document_classification)" class="confidential-label">CONFIDENTIAL</span>
              </div>
            </td>
            <td>
               <div class="date-received">
                <i class="bi bi-person-circle date-icon"></i>
                {{ document.received_by?.firstname || '' }} {{ document.received_by?.middlename || '' }} {{ document.received_by?.lastname || '' }}
               </div>
            </td>
            <td>
              <div class="date-received">
                <i class="bi bi-calendar3 date-icon"></i>
                {{ document.date_receive ? formatDate(document.date_receive) : "NOT YET RECEIVED" }}
              </div>
            </td>
            <td>
              <div class="action-buttons">
                <button class="btn-action btn-view" @click="viewDocument(document)" title="View Details">
                  <i class="bi bi-eye"></i>
                </button>
                <button class="btn-action btn-forward" @click="openForwardModal(document)" title="Forward Office">
                  <i class="bi bi-arrow-right-circle"></i>
                </button>
                <button class="btn-action btn-release" @click="openReleaseModal(document)" title="For Release">
                  <i class="bi bi-check2-circle"></i>
                </button>
                <!-- <button class="btn-action btn-archive" @click="openArchiveModal(document)" title="Archive">
                  <i class="bi bi-archive"></i>
                </button> -->
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <PaginationComponent
      :current-page="Number(documents.current_page)"
      :total-pages="Number(documents.last_page)"
      :total="Number(documents.total)"
      :per-page="Number(documents.per_page)"
      :from="Number(documents.from)"
      :to="Number(documents.to)"
      @page-change="changePage"
    />

    <!-- Forward Modal -->
    <div v-if="showForwardModal" class="modal-overlay" @click.self="closeForwardModal">
      <div class="modal-dialog enhanced-modal forward-modal-large">
        <div class="modal-content square-modal">
          <!-- Header -->
          <div class="square-header forward-header">
            <div class="d-flex align-items-center">
              <div class="square-icon forward-icon">
                <i class="bi bi-arrow-right-circle"></i>
              </div>
              <div>
                <h5 class="modal-title">Forward Document</h5>
                <small class="modal-subtitle">Route document to offices with memo slip</small>
              </div>
            </div>
            <button type="button" class="square-close" @click="closeForwardModal" :disabled="forwarding">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body forward-modal-body">
            <!-- Error Message -->
            <div v-if="forwardError" class="error-msg">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none" />
              </svg>
              <span>{{ forwardError }}</span>
            </div>

            <!-- Document Info Bar -->
            <div class="forward-doc-info-bar">
              <div class="doc-info-item">
                <span class="doc-info-label">Tracking</span>
                <span class="doc-info-value">{{ forwardDocument?.document?.tracking_number || "N/A" }}</span>
              </div>
              <div class="doc-info-divider"></div>
              <div class="doc-info-item">
                <span class="doc-info-label">Type</span>
                <span class="doc-type-badge">{{ forwardDocument?.document?.document_type?.document_type_name || forwardDocument?.document?.document_type || "N/A" }}</span>
              </div>
              <div class="doc-info-divider"></div>
              <div class="doc-info-item doc-info-subject">
                <span class="doc-info-label">Subject</span>
                <span class="doc-info-value subject-truncate">{{ forwardDocument?.document?.subject || "N/A" }}</span>
              </div>
              <div class="doc-info-divider"></div>
              <div class="doc-info-item">
                <span class="doc-info-label">Duration</span>
                <span class="duration-badge" :class="getDurationBadgeClass(getDurationValue())">
                  <i :class="getDurationIcon(getDurationValue())"></i>
                  {{ getDurationDisplay() }}
                </span>
              </div>
            </div>

            <!-- Main 3-Column Grid -->
            <div class="forward-main-grid">
              <!-- Column 1: Select Offices -->
              <div class="forward-col office-col">
                <div class="col-header">
                  <div class="col-header-left">
                    <i class="bi bi-building"></i>
                    <span>Select Offices</span>
                    <span class="required-star">*</span>
                  </div>
                  <span class="selection-count" v-if="selectedOffices.length > 0">
                    {{ selectedOffices.length }} selected
                  </span>
                </div>

                <div class="office-search-box">
                  <i class="bi bi-search office-search-icon"></i>
                  <input
                    type="text"
                    v-model="officeSearchQuery"
                    class="office-search-input"
                    placeholder="Search offices..."
                  />
                  <button v-if="officeSearchQuery" @click="clearOfficeSearch" class="office-search-clear">
                    <i class="bi bi-x-circle"></i>
                  </button>
                </div>

                <div v-if="officesLoading" class="loading-state">
                  <div class="loader-spinner" style="width: 30px; height: 30px;"></div>
                  <p class="mt-1 text-muted">Loading offices...</p>
                </div>

                <div v-else-if="filteredOffices.length === 0" class="empty-state-compact">
                  <i class="bi bi-building"></i>
                  <p>{{ officeSearchQuery ? "No offices match" : "No offices available" }}</p>
                </div>

                <div v-else class="office-list">
                  <div
                    v-for="office in filteredOffices"
                    :key="office.id"
                    class="office-item"
                    :class="{ selected: isOfficeSelected(office.id) }"
                    @click="toggleOfficeSelection(office)"
                  >
                    <div class="office-item-check">
                      <i :class="isOfficeSelected(office.id) ? 'bi bi-check-circle-fill' : 'bi bi-circle'"></i>
                    </div>
                    <div class="office-item-icon">
                      <i class="bi bi-building"></i>
                    </div>
                    <div class="office-item-info">
                      <span class="office-item-name">{{ office.sub_office_name }}</span>
                      <span class="office-item-code" v-if="office.office_code">{{ office.office_code }}</span>
                    </div>
                  </div>
                </div>

                <!-- Selected Offices Tags -->
                <div v-if="selectedOffices.length > 0" class="selected-tags">
                  <div class="selected-tags-header">
                    <i class="bi bi-check2-all"></i>
                    <span>Selected Offices</span>
                    <span class="badge-count">{{ selectedOffices.length }}</span>
                  </div>
                  <div class="selected-tags-list">
                    <span v-for="office in selectedOffices" :key="office.id" class="selected-tag">
                      {{ office.sub_office_name }}
                      <button @click="removeOffice(office.id)" class="remove-tag-btn" :disabled="forwarding">
                        <i class="bi bi-x"></i>
                      </button>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Column 2: Memo Slip Selection -->
              <div class="forward-col memo-col">
                <div class="col-header">
                  <div class="col-header-left">
                    <i class="bi bi-envelope-paper"></i>
                    <span>Memo Slip</span>
                  </div>
                  <span class="optional-badge">Optional</span>
                </div>

                <div v-if="memoSlipsLoading" class="loading-state">
                  <div class="loader-spinner" style="width: 30px; height: 30px;"></div>
                  <p class="mt-1 text-muted">Loading memo slips...</p>
                </div>

                <div v-else-if="memoSlips.length === 0" class="empty-state-compact">
                  <i class="bi bi-envelope-paper"></i>
                  <p>No active memo slips</p>
                </div>

                <div v-else class="memo-section">
                  <div class="memo-actions">
                    <button 
                      class="memo-action-btn select-all-btn" 
                      @click="selectAllMemoSlips" 
                      :disabled="memoSlips.length === 0 || forwarding"
                    >
                      <i class="bi bi-check-all"></i> All
                    </button>
                    <button 
                      class="memo-action-btn deselect-all-btn" 
                      @click="deselectAllMemoSlips" 
                      :disabled="selectedMemoSlips.length === 0 || forwarding"
                    >
                      <i class="bi bi-x"></i> Clear
                    </button>
                    <span class="memo-count-badge">
                      {{ selectedMemoSlips.length }} / {{ memoSlips.length }}
                    </span>
                  </div>

                  <div class="memo-list">
                    <div
                      v-for="memo in memoSlips"
                      :key="memo.id"
                      class="memo-item"
                      :class="{ selected: isMemoSlipSelected(memo.id) }"
                      @click="toggleMemoSlipSelection(memo)"
                    >
                      <div class="memo-item-check">
                        <i :class="isMemoSlipSelected(memo.id) ? 'bi bi-check-square-fill' : 'bi bi-square'"></i>
                      </div>
                      <div class="memo-item-content">
                        <span class="memo-item-name">{{ memo.memo_slip_name || 'No subject' }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Column 3: Duration, Attachment & Remarks -->
              <div class="forward-col details-col">
                <!-- Duration -->
                <div class="detail-section">
                  <div class="col-header">
                    <div class="col-header-left">
                      <i class="bi bi-clock"></i>
                      <span>Duration</span>
                      <span class="required-star">*</span>
                    </div>
                  </div>
                  
                  <div v-if="hasExistingDuration()" class="duration-display">
                    <div class="duration-display-box">
                      <div class="duration-display-icon">
                        <i :class="getDurationIcon(getDurationValue())"></i>
                      </div>
                      <div class="duration-display-info">
                        <span class="duration-display-label">Current Duration</span>
                        <span class="duration-display-value" :class="getDurationBadgeClass(getDurationValue())">
                          {{ getDurationDisplay() }}
                        </span>
                      </div>
                      <span class="readonly-badge">Read Only</span>
                    </div>
                    <div class="duration-note">
                      <i class="bi bi-info-circle-fill"></i>
                      <span>Duration is set and cannot be changed.</span>
                    </div>
                  </div>

                  <div v-else>
                    <select v-model="selectedDuration" class="duration-select" :disabled="forwarding">
                      <option value="">Select Duration</option>
                      <option value="Simple - 3 Days">Simple - 3 Days</option>
                      <option value="Complex - 15 Days">Complex - 15 Days</option>
                      <option value="Highly Technical - 45 Days">Highly Technical - 45 Days</option>
                    </select>
                    <div class="duration-hint" v-if="!selectedDuration">
                      <i class="bi bi-info-circle"></i>
                      <span>Please select a duration.</span>
                    </div>
                  </div>
                </div>

                <!-- Attachment -->
                <div class="detail-section attachment-section">
                  <div class="col-header">
                    <div class="col-header-left">
                      <i class="bi bi-paperclip"></i>
                      <span>Attachment</span>
                    </div>
                    <span class="optional-badge">Optional</span>
                  </div>

                  <div
                    v-if="!uploadedFile"
                    class="dropzone-compact"
                    @click="triggerFileUpload"
                    @dragover.prevent
                    @drop.prevent="handleDrop"
                  >
                    <input
                      type="file"
                      ref="fileInput"
                      class="d-none"
                      accept=".pdf"
                      @change="handleFileUpload"
                    />
                    <div class="dropzone-compact-content">
                      <i class="bi bi-cloud-arrow-up"></i>
                      <div>
                        <p class="dropzone-title">Click to upload or drag PDF</p>
                        <p class="dropzone-hint">PDF only (Max 20MB)</p>
                      </div>
                    </div>
                  </div>

                  <div v-if="uploadedFile" class="file-card-compact">
                    <div class="file-card-icon">
                      <i class="bi bi-file-pdf"></i>
                    </div>
                    <div class="file-card-info">
                      <span class="file-card-name">{{ uploadedFile.name }}</span>
                      <span class="file-card-size">{{ formatFileSize(uploadedFile.size) }}</span>
                    </div>
                    <div class="file-card-actions">
                      <button type="button" class="file-action-btn" @click="openFilePreview" title="View">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button type="button" class="file-action-btn" @click="triggerFileUpload" title="Replace">
                        <i class="bi bi-arrow-repeat"></i>
                      </button>
                      <button type="button" class="file-action-btn remove-btn" @click="removeForwardFile" title="Remove">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>
                  </div>

                  <div class="field-error" v-if="attachmentError">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ attachmentError }}
                  </div>
                </div>

                <!-- Remarks -->
                <div class="detail-section remarks-section">
                  <div class="col-header">
                    <div class="col-header-left">
                      <i class="bi bi-chat-left-text"></i>
                      <span>Remarks / Instructions</span>
                    </div>
                    <span class="optional-badge">Optional</span>
                  </div>
                  <textarea
                    v-model="remarks"
                    class="remarks-textarea"
                    rows="2"
                    placeholder="Add remarks or instructions..."
                    :disabled="forwarding"
                    maxlength="500"
                  ></textarea>
                  <div class="remarks-counter" :class="{ 'text-danger': remarks.length > 450 }">
                    {{ remarks.length }}/500
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="modal-actions forward-actions">
              <button type="button" class="btn btn-outline-secondary square-btn" @click="clearForwardForm" :disabled="forwarding">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
              </button>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-light square-btn" @click="closeForwardModal" :disabled="forwarding">
                  Cancel
                </button>
                <button
                  type="button"
                  class="btn-save square-btn"
                  @click="submitForwardDocument"
                  :disabled="forwarding || selectedOffices.length === 0 || (!hasExistingDuration() && !selectedDuration)"
                >
                  <span v-if="forwarding" class="spinner-border spinner-border-sm me-1" role="status"></span>
                  <i v-else class="bi bi-send-check me-1"></i>
                  {{ forwarding ? "Forwarding..." : "Forward" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Forward File Preview Modal -->
    <div v-if="showFilePreview" class="modal-overlay" @click.self="closeFilePreview">
      <div class="modal-dialog" style="max-width: 1200px; width: 95vw; max-height: 95vh;">
        <div class="modal-content square-modal">
          <div class="square-header" style="background: linear-gradient(135deg, #1e4d2b, #2d6a4f)">
            <h5 class="modal-title">File Preview</h5>
            <button type="button" class="square-close" @click="closeFilePreview">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body text-center" style="max-height: 85vh; overflow-y: auto; padding: 20px;">
            <iframe 
              v-if="uploadedFile && uploadedFile.type === 'application/pdf'" 
              :src="filePreviewUrl" 
              width="100%" 
              height="800px" 
              frameborder="0"
            ></iframe>
            <div v-else class="p-5 text-center">
              <i class="bi bi-file-pdf" style="font-size: 4rem; color: #6b7280;"></i>
              <p class="mt-3">Preview not available for this file type.</p>
              <p class="text-muted">{{ uploadedFile?.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Release Document Modal -->
    <div v-if="showReleaseModal" class="modal-overlay" @click.self="closeReleaseModal">
      <div class="modal-dialog enhanced-modal" style="max-width: 600px">
        <div class="modal-content square-modal">
          <div class="square-header" style="background: linear-gradient(135deg, #1e4d2b, #2d6a4f)">
            <div class="d-flex align-items-center">
              <div class="square-icon" style="background: rgba(255, 255, 255, 0.2)">
                <i class="bi bi-check2-circle"></i>
              </div>
              <div>
                <h5 class="modal-title">Release Document</h5>
                <small class="modal-subtitle">Confirm document release</small>
              </div>
            </div>
            <button type="button" class="square-close" @click="closeReleaseModal" :disabled="releasing">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body">
            <div v-if="releaseError" class="error-msg" role="alert">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none" />
              </svg>
              <span>{{ releaseError }}</span>
            </div>

            <div class="text-center mb-4">
              <div class="release-warning-icon">
                <i class="bi bi-question-circle-fill"></i>
              </div>
              <h5 class="mt-3" style="color: #1e293b; font-weight: 700;">Are you sure you want to release this document?</h5>
              <p class="text-muted" style="font-size: 0.85rem;">This action will mark the document as released and ready for the next step.</p>
            </div>

            <div class="release-doc-info">
              <div class="release-doc-row">
                <div class="release-doc-icon-wrapper">
                  <i class="bi bi-upc-scan"></i>
                </div>
                <div class="release-doc-content">
                  <span class="release-doc-label">Tracking Number</span>
                  <span class="release-doc-value">{{ releaseDocument?.document?.tracking_number || "N/A" }}</span>
                </div>
              </div>
              <div class="release-doc-row">
                <div class="release-doc-icon-wrapper">
                  <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="release-doc-content">
                  <span class="release-doc-label">Document Type</span>
                  <span class="release-doc-value">{{ releaseDocument?.document?.document_type?.document_type_name || releaseDocument?.document?.document_type || "N/A" }}</span>
                </div>
              </div>
              <div class="release-doc-row">
                <div class="release-doc-icon-wrapper">
                  <i class="bi bi-journal-text"></i>
                </div>
                <div class="release-doc-content">
                  <span class="release-doc-label">Subject</span>
                  <span class="release-doc-value">{{ releaseDocument?.document?.subject || "N/A" }}</span>
                </div>
              </div>
            </div>

            <div class="attachment-section mt-3">
              <label class="form-label">
                Attachment (PDF only)
              </label>

              <div
                v-if="!releaseUploadedFile"
                class="dropzone"
                @click="triggerReleaseFileUpload"
                @dragover.prevent
                @drop.prevent="handleReleaseDrop"
              >
                <input
                  type="file"
                  ref="releaseFileInput"
                  class="d-none"
                  accept=".pdf"
                  @change="handleReleaseFileUpload"
                />
                <div class="dropzone-content">
                  <div class="dropzone-icon">
                    <i class="bi bi-cloud-arrow-up"></i>
                  </div>
                  <h6 class="fw-semibold mb-1">Click to upload or drag PDF here</h6>
                  <p class="text-muted small mb-0">PDF only (Max 20MB)</p>
                </div>
              </div>

              <div v-if="releaseUploadedFile" class="single-file-card">
                <div class="file-info">
                  <div class="file-icon-wrap file-pdf">
                    <i class="bi bi-file-pdf"></i>
                  </div>
                  <div class="file-details">
                    <div class="file-name">{{ releaseUploadedFile.name }}</div>
                    <div class="file-size">{{ formatFileSize(releaseUploadedFile.size) }}</div>
                  </div>
                </div>
                <div class="file-actions">
                  <button
                    type="button"
                    class="btn-icon btn-icon-view"
                    @click="openReleaseFilePreview"
                    title="View"
                  >
                    <i class="bi bi-eye"></i>
                  </button>
                  <button
                    type="button"
                    class="btn-icon btn-icon-replace"
                    @click="triggerReleaseFileUpload"
                    title="Replace"
                  >
                    <i class="bi bi-arrow-repeat"></i>
                  </button>
                  <button
                    type="button"
                    class="btn-icon btn-icon-remove"
                    @click="removeReleaseFile"
                    title="Remove"
                  >
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
              </div>

              <div class="field-error" v-if="releaseAttachmentError">
                <i class="bi bi-exclamation-circle"></i>
                {{ releaseAttachmentError }}
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label">
                Remarks (Optional)
              </label>
              <textarea
                v-model="releaseRemarks"
                class="remarks-textarea"
                rows="3"
                placeholder="Add remarks for releasing this document..."
                :disabled="releasing"
                maxlength="500"
              ></textarea>
              <div class="remarks-counter" :class="{ 'text-danger': releaseRemarks.length > 450 }">
                {{ releaseRemarks.length }}/500
              </div>
            </div>

            <div class="system-datetime-box mt-3">
              <div class="system-datetime-header" style="background: #2d6a4f;">
                <i class="bi bi-clock-fill"></i>
                <span>System Date & Time (Philippine Standard Time)</span>
              </div>
              <div class="system-datetime-display">
                <div class="datetime-item">
                  <i class="bi bi-calendar3"></i>
                  <span class="datetime-label">Date:</span>
                  <span class="datetime-value">{{ currentPstDate }}</span>
                </div>
                <div class="datetime-item">
                  <i class="bi bi-clock"></i>
                  <span class="datetime-label">Time:</span>
                  <span class="datetime-value">{{ currentPstTime }}</span>
                </div>
              </div>
              <div class="system-datetime-footer">
                <i class="bi bi-info-circle"></i>
                <small>This date and time will be recorded as the official release timestamp.</small>
              </div>
            </div>

            <div class="modal-actions">
              <button
                type="button"
                class="btn btn-outline-secondary square-btn"
                @click="closeReleaseModal"
                :disabled="releasing"
              >
                Cancel
              </button>
              <button
                type="button"
                class="btn-save square-btn"
                @click="submitReleaseDocument"
                :disabled="releasing"
                style="background: linear-gradient(135deg, #1e4d2b, #2d6a4f);"
              >
                <span v-if="releasing" class="spinner-border spinner-border-sm me-1" role="status"></span>
                <i v-else class="bi bi-check2-circle me-1"></i>
                {{ releasing ? "Processing..." : "Confirm For Release" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Release File Preview Modal -->
    <div v-if="showReleaseFilePreview" class="modal-overlay" @click.self="closeReleaseFilePreview">
      <div class="modal-dialog" style="max-width: 1200px; width: 95vw; max-height: 95vh;">
        <div class="modal-content square-modal">
          <div class="square-header" style="background: linear-gradient(135deg, #1e4d2b, #2d6a4f)">
            <h5 class="modal-title">File Preview</h5>
            <button type="button" class="square-close" @click="closeReleaseFilePreview">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body text-center" style="max-height: 85vh; overflow-y: auto; padding: 20px;">
            <iframe 
              v-if="releaseUploadedFile && releaseUploadedFile.type === 'application/pdf'" 
              :src="releaseFilePreviewUrl" 
              width="100%" 
              height="800px" 
              frameborder="0"
            ></iframe>
            <div v-else class="p-5 text-center">
              <i class="bi bi-file-pdf" style="font-size: 4rem; color: #6b7280;"></i>
              <p class="mt-3">Preview not available for this file type.</p>
              <p class="text-muted">{{ releaseUploadedFile?.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Archive Document Modal -->
    <div v-if="showArchiveModal" class="modal-overlay" @click.self="closeArchiveModal">
      <div class="modal-dialog enhanced-modal" style="max-width: 550px">
        <div class="modal-content square-modal">
          <div class="modal-header-enhanced square-header" style="background: linear-gradient(135deg, #6b7280, #4b5563)">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon" style="background: rgba(255, 255, 255, 0.2)">
                <i class="bi bi-archive"></i>
              </div>
              <div>
                <h5 class="modal-title">Archive Document</h5>
              </div>
            </div>
            <button type="button" class="btn-close-custom square-close" @click="closeArchiveModal" :disabled="archiving">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body-enhanced">
            <div v-if="archiveError" class="error-msg" role="alert">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none" />
              </svg>
              <span>{{ archiveError }}</span>
            </div>

            <div class="text-center mb-4">
              <div class="archive-warning-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
              </div>
              <h5 class="mt-3" style="color: #1e293b; font-weight: 700;">Are you sure you want to archive this document?</h5>
              <p class="text-muted" style="font-size: 0.85rem;">This action will move the document to the archive. You can still access it from the archived records.</p>
            </div>

            <div class="archive-doc-info">
              <div class="archive-doc-row">
                <div class="archive-doc-icon-wrapper">
                  <i class="bi bi-upc-scan"></i>
                </div>
                <div class="archive-doc-content">
                  <span class="archive-doc-label">Tracking Number</span>
                  <span class="archive-doc-value">{{ archiveDocument?.document?.tracking_number || "N/A" }}</span>
                </div>
              </div>
              <div class="archive-doc-row">
                <div class="archive-doc-icon-wrapper">
                  <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="archive-doc-content">
                  <span class="archive-doc-label">Document Type</span>
                  <span class="archive-doc-value">{{ archiveDocument?.document?.document_type?.document_type_name || archiveDocument?.document?.document_type || "N/A" }}</span>
                </div>
              </div>
              <div class="archive-doc-row">
                <div class="archive-doc-icon-wrapper">
                  <i class="bi bi-journal-text"></i>
                </div>
                <div class="archive-doc-content">
                  <span class="archive-doc-label">Subject</span>
                  <span class="archive-doc-value">{{ archiveDocument?.document?.subject || "N/A" }}</span>
                </div>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label-enhanced">
                Archive Reason <span class="required-star">*</span>
              </label>
              <select v-model="archiveReason" class="archive-select" :disabled="archiving">
                <option value="">Select Reason</option>
                <option value="Completed">Completed</option>
                <option value="Inactive">Inactive</option>
                <option value="No Longer Needed">No Longer Needed</option>
                <option value="Duplicate">Duplicate</option>
                <option value="Other">Other</option>
              </select>
              <div class="archive-hint" v-if="!archiveReason">
                <i class="bi bi-info-circle"></i>
                <span>Please select a reason for archiving this document.</span>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label-enhanced">
                Remarks (Optional)
              </label>
              <textarea
                v-model="archiveRemarks"
                class="remarks-textarea"
                rows="3"
                placeholder="Add remarks for archiving this document..."
                :disabled="archiving"
                maxlength="500"
              ></textarea>
              <div class="remarks-counter" :class="{ 'text-danger': archiveRemarks.length > 450 }">
                {{ archiveRemarks.length }}/500
              </div>
            </div>

            <div class="system-datetime-box mt-3">
              <div class="system-datetime-header" style="background: #6b7280;">
                <i class="bi bi-clock-fill"></i>
                <span>System Date & Time (Philippine Standard Time)</span>
              </div>
              <div class="system-datetime-display">
                <div class="datetime-item">
                  <i class="bi bi-calendar3"></i>
                  <span class="datetime-label">Date:</span>
                  <span class="datetime-value">{{ currentPstDate }}</span>
                </div>
                <div class="datetime-item">
                  <i class="bi bi-clock"></i>
                  <span class="datetime-label">Time:</span>
                  <span class="datetime-value">{{ currentPstTime }}</span>
                </div>
              </div>
              <div class="system-datetime-footer">
                <i class="bi bi-info-circle"></i>
                <small>This date and time will be recorded as the official archive timestamp.</small>
              </div>
            </div>

            <div class="modal-actions">
              <button
                type="button"
                class="btn btn-outline-secondary square-btn"
                @click="closeArchiveModal"
                :disabled="archiving"
              >
                Cancel
              </button>
              <button
                type="button"
                class="btn btn-archive-doc square-btn"
                @click="submitArchiveDocument"
                :disabled="archiving || !archiveReason"
              >
                <span v-if="archiving" class="spinner-border spinner-border-sm me-1" role="status"></span>
                <i v-else class="bi bi-archive me-1"></i>
                {{ archiving ? "Processing..." : "Confirm Archive" }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Viewer Modal -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="closeViewModal">
      <div class="modal-dialog enhanced-modal document-view-modal">
        <div class="modal-content square-modal">
          <div class="square-header document-header">
            <div class="d-flex align-items-center">
              <div class="square-icon document-icon">
                <i class="bi bi-file-earmark-pdf"></i>
              </div>
              <div>
                <h5 class="modal-title">Document Viewer</h5>
                <small class="modal-subtitle">
                  <span class="tracking-badge">{{ selectedDocument?.document?.tracking_number }}</span>
                  <span :class="['status-pill', getStatusClass(selectedDocument?.status)]">{{ selectedDocument?.status }}</span>
                </small>
              </div>
            </div>
            <div class="header-actions">
              <button type="button" class="square-close" @click="closeViewModal">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>

          <div class="modal-body document-viewer-body">
            <div class="document-viewer-tabs">
              <button class="viewer-tab-btn" :class="{ active: viewerActiveTab === 'details' }" @click="viewerActiveTab = 'details'">
                <i class="bi bi-info-circle-fill"></i>
                <span>Document Information</span>
              </button>
              <button class="viewer-tab-btn" :class="{ active: viewerActiveTab === 'history' }" @click="viewerActiveTab = 'history'">
                <i class="bi bi-clock-history"></i>
                <span>Route History</span>
              </button>
               <button class="viewer-tab-btn" :class="{ active: viewerActiveTab === 'progress' }" @click="viewerActiveTab = 'progress'">
                <i class="bi bi-clipboard-check"></i>
                <span>Progress Notes</span>
              </button>
            </div>

            <!-- Document Information Tab -->
            <div v-show="viewerActiveTab === 'details'" class="document-viewer-layout">
              <div class="details-panel">
                <div class="details-panel-header">
                  <i class="bi bi-info-circle-fill"></i>
                  <span>Document Information</span>
                </div>

                <div class="details-content" v-if="selectedDocument">
                  <div class="detail-card">
                    <div class="detail-icon-wrapper">
                      <i class="bi bi-upc-scan"></i>
                    </div>
                    <div class="detail-info">
                      <label>Tracking Number</label>
                      <span class="tracking-number-large">{{ selectedDocument.document.tracking_number }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper classification-icon">
                      <i class="bi bi-shield-lock"></i>
                    </div>
                    <div class="detail-info">
                      <label>Classification</label>
                      <span :class="['classification-badge-large', getClassificationBadgeClass(selectedDocument.document.document_classification)]">
                        {{ selectedDocument.document.document_classification || "N/A" }}
                      </span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper type-icon">
                      <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="detail-info">
                      <label>Document Type</label>
                      <span class="doc-type-badge-large">{{ selectedDocument.document.document_type?.document_type_name || selectedDocument.document.document_type }}</span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper subject-icon">
                      <i class="bi bi-journal-text"></i>
                    </div>
                    <div class="detail-info">
                      <label>Subject / Title</label>
                      <span class="detail-value" :class="{ 'confidential-blur-static': isConfidential }">
                        {{ selectedDocument.document.subject }}
                        <span v-if="isConfidential" class="confidential-label">CONFIDENTIAL</span>
                      </span>
                    </div>
                  </div>

                  <div class="detail-card">
                    <div class="detail-icon-wrapper sender-icon-card">
                      <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="detail-info">
                      <label>Sender / Origin</label>
                      <span class="detail-value" :class="{ 'confidential-blur-static': isConfidential }">
                        {{ selectedDocument.document.sender_name }}
                        <span v-if="isConfidential" class="confidential-label">CONFIDENTIAL</span>
                      </span>
                    </div>
                  </div>

                  <div class="detail-card duration-detail-card">
                    <div class="detail-icon-wrapper duration-icon">
                      <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="detail-info">
                      <label>Duration</label>
                      <span class="duration-badge-large" :class="getDurationBadgeClass(getDocumentDuration())">
                        <i :class="getDurationIcon(getDocumentDuration())"></i>
                        {{ getDocumentDurationDisplay() }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- PDF Preview -->
              <div class="pdf-panel">
                <div class="pdf-panel-header">
                  <div class="pdf-panel-title">
                    <i :class="isConfidential ? 'bi bi-shield-lock text-danger' : 'bi bi-file-pdf'"></i>
                    <span>{{ isConfidential ? 'Document Preview' : 'Document Preview' }}</span>
                  </div>
                  <div class="pdf-controls" v-if="!isConfidential">
                    <button class="btn-pdf-control" @click="zoomIn" title="Zoom In" :disabled="pdfLoadError">
                      <i class="bi bi-zoom-in"></i>
                    </button>
                    <button class="btn-pdf-control" @click="zoomOut" title="Zoom Out" :disabled="pdfLoadError">
                      <i class="bi bi-zoom-out"></i>
                    </button>
                  </div>
                </div>

                <div class="pdf-viewer-wrapper">
                  <!-- Confidential Notice -->
                  <div v-if="isConfidential" class="confidential-notice">
                    <div class="confidential-icon-wrapper">
                      <div class="confidential-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                      </div>
                      <div class="confidential-ring"></div>
                    </div>
                    <h3 class="confidential-title">CONFIDENTIAL DOCUMENT</h3>
                    <div class="confidential-divider"></div>
                    <p class="confidential-message">
                      This document is classified as <strong>CONFIDENTIAL</strong>.
                    </p>
                    <p class="confidential-sub-message">
                      PDF preview is restricted for security purposes.
                    </p>
                    <div class="confidential-details">
                      <div class="confidential-detail-item">
                        <i class="bi bi-upc-scan"></i>
                        <span>Tracking No: {{ selectedDocument?.document?.tracking_number }}</span>
                      </div>
                      <div class="confidential-detail-item">
                        <i class="bi bi-file-earmark-lock"></i>
                        <span>Classification: {{ selectedDocument?.document?.document_classification }}</span>
                      </div>
                    </div>
                    <div class="confidential-footer-note">
                      <i class="bi bi-exclamation-triangle-fill"></i>
                      <span>Unauthorized access prohibited.</span>
                    </div>
                  </div>

                  <!-- PDF Viewer -->
                  <template v-else>
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
                      :style="{ width: `${100 / pdfZoom}%`, height: `${pdfViewerHeight}px` }"
                      frameborder="0"
                      @load="onPdfLoaded"
                      @error="handlePdfError"
                    ></iframe>

                    <div v-if="pdfLoadError" class="pdf-state pdf-error">
                      <i class="bi bi-file-earmark-x" style="font-size: 4rem; color: #ef4444"></i>
                      <h5 class="mt-3">PDF Not Available</h5>
                      <p class="text-muted">The attachment could not be loaded.</p>
                      <button class="btn btn-outline-secondary btn-sm mt-3" @click="retryPdfLoad">
                        <i class="bi bi-arrow-repeat me-1"></i> Retry
                      </button>
                    </div>
                  </template>
                </div>

                <div class="pdf-footer" v-if="!pdfLoadError && !isConfidential">
                  <span class="pdf-zoom-level">Zoom: {{ Math.round(pdfZoom * 100) }}%</span>
                  <span class="pdf-page-info" v-if="selectedDocument">File: {{ selectedDocument.document.tracking_number }}_attachment.pdf</span>
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
                <div class="route-history-badge" v-if="getDocumentRoutes().length > 0">
                  <i class="bi bi-arrow-left-right"></i>
                  <span>{{ getDocumentRoutes().length }} Route{{ getDocumentRoutes().length !== 1 ? "s" : "" }}</span>
                </div>
              </div>

              <div class="route-history-content">
                <div v-if="routeHistoryLoading" class="route-state-box">
                  <div class="loader-spinner"></div>
                  <p class="mt-3 text-muted">Loading route history...</p>
                </div>

                <div v-else-if="getDocumentRoutes().length === 0" class="route-state-box">
                  <div class="empty-icon-wrapper">
                    <i class="bi bi-signpost-2"></i>
                  </div>
                  <h5 class="mt-3">No Route History</h5>
                  <p class="text-muted">This document hasn't been routed yet.</p>
                </div>

                <div v-else class="timeline-container">
                  <div v-for="(route, rIndex) in getDocumentRoutes()" :key="rIndex" class="timeline-item" :class="{ 'is-last': rIndex === getDocumentRoutes().length - 1 }">
                    <div class="timeline-node" :class="getRouteNodeClass(route.status)">
                      <i :class="getRouteStatusIcon(route.status)"></i>
                    </div>

                    <div class="timeline-card" :class="{ 'active-card': isRouteActive(route) }">
                      <div class="timeline-card-header">
                        <div class="office-info">
                          <i class="bi bi-building-fill"></i>
                          <div class="office-text">
                            <span class="office-name">{{ getRouteOfficeName(route) }}</span>
                          </div>
                        </div>
                        <span class="route-status-badge" :class="getRouteStatusClass(route.status)">
                          <i :class="getRouteStatusIcon(route.status)"></i>
                          {{ route.status || "PENDING" }}
                        </span>
                      </div>

                      <div class="timeline-card-body">
                        <div class="info-grid-enhanced">
                          <div class="info-card received-card" v-if="route.date_receive">
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

                              <div class="info-field" v-if="route.received_by || route.received_by_name">
                                <div class="field-icon">
                                  <i class="bi bi-person-check"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Received By</span>
                                  <span class="field-value">
                                    <template v-if="route.received_by">
                                      {{ route.received_by.firstname || "" }}
                                      {{ route.received_by.middlename ? route.received_by.middlename + " " : "" }}
                                      {{ route.received_by.lastname || "" }}
                                    </template>
                                    <template v-else-if="route.received_by_name">
                                      {{ route.received_by_name }}
                                    </template>
                                  </span>
                                </div>
                              </div>

                              <div class="info-field" v-if="route.remarks">
                                <div class="field-icon remarks-icon">
                                  <i class="bi bi-chat-left-text"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Remarks</span>
                                  <span class="field-value remarks-text">{{ route.remarks }}</span>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="info-card completed-card" v-if="route.date_document_out">
                            <div class="info-card-header">
                              <div class="info-card-icon completed-icon">
                                <i class="bi bi-check2-all"></i>
                              </div>
                              <span class="info-card-title">Completion Information</span>
                              <span class="info-card-status completed-status" v-if="route.status === 'Completed'">
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
                                    {{ formatDateTime(route.date_document_out) }}
                                  </span>
                                </div>
                              </div>

                              <div class="info-field" v-if="route.completed_by || route.completed_by_name">
                                <div class="field-icon">
                                  <i class="bi bi-person-check"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Completed By</span>
                                  <span class="field-value">
                                    <template v-if="route.completed_by">
                                      {{ route.completed_by.firstname || "" }}
                                      {{ route.completed_by.middlename ? route.completed_by.middlename + " " : "" }}
                                      {{ route.completed_by.lastname || "" }}
                                    </template>
                                    <template v-else-if="route.completed_by_name">
                                      {{ route.completed_by_name }}
                                    </template>
                                  </span>
                                </div>
                              </div>

                              <div class="info-field" v-if="route.remarks">
                                <div class="field-icon notes-icon">
                                  <i class="bi bi-sticky"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Completion Notes</span>
                                  <span class="field-value notes-text">{{ route.remarks }}</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="flow-section" v-if="route.from_office || route.to_office">
                          <div class="flow-container">
                            <div class="flow-node origin-node" v-if="route.from_office">
                              <div class="flow-node-icon">
                                <i class="bi bi-box-arrow-right"></i>
                              </div>
                              <div class="flow-node-content">
                                <span class="flow-node-label">From</span>
                                <span class="flow-node-value">{{ route.from_office }}</span>
                              </div>
                            </div>

                            <div class="flow-arrow" v-if="route.from_office && route.to_office">
                              <div class="arrow-line"></div>
                              <div class="arrow-icon">
                                <i class="bi bi-arrow-right-circle-fill"></i>
                              </div>
                              <div class="arrow-line"></div>
                            </div>

                            <div class="flow-node destination-node" v-if="route.to_office">
                              <div class="flow-node-icon">
                                <i class="bi bi-box-arrow-in-left"></i>
                              </div>
                              <div class="flow-node-content">
                                <span class="flow-node-label">To</span>
                                <span class="flow-node-value">{{ route.to_office }}</span>
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
             <!-- ==================== PROGRESS NOTES TAB ==================== -->
            <div v-show="viewerActiveTab === 'progress'" class="progress-notes-panel">
              <div class="progress-notes-header">
                <div class="progress-notes-title">
                  <i class="bi bi-clipboard-check"></i>
                  <span>Progress Notes</span>
                </div>
                <div class="progress-notes-badge" v-if="getMemoSlips().length > 0 || selectedDocument?.remarks">
                  <i class="bi bi-info-circle"></i>
                  <span>{{ (getMemoSlips().length > 0 ? getMemoSlips().length + ' Memo(s)' : '') }} 
                    {{ (getMemoSlips().length > 0 && selectedDocument?.remarks ? ' | ' : '') }}
                    {{ selectedDocument?.remarks ? 'With Remarks' : '' }}</span>
                </div>
              </div>

              <div class="progress-notes-content">
                <!-- Memo Slips Section -->
                <div class="progress-section memo-section">
                  <div class="section-header">
                    <div class="section-header-left">
                      <i class="bi bi-envelope-paper"></i>
                      <span>Memo Slip(s)</span>
                    </div>
                    <span class="section-badge">{{ getMemoSlips().length }} record(s)</span>
                  </div>
                  
                  <div v-if="getMemoSlips().length === 0" class="empty-state-compact">
                    <i class="bi bi-envelope-paper"></i>
                    <p>No memo slips attached to this document.</p>
                  </div>
                  
                  <div v-else class="memo-slips-grid">
                    <div 
                      v-for="(memo, index) in getMemoSlips()" 
                      :key="index" 
                      class="memo-slip-card"
                    >
                      <div class="memo-slip-number">{{ index + 1 }}</div>
                      <div class="memo-slip-content">
                        <i class="bi bi-file-text"></i>
                        <span>{{ memo }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Remarks Section -->
                <div class="progress-section remarks-section">
                  <div class="section-header">
                    <div class="section-header-left">
                      <i class="bi bi-chat-left-text"></i>
                      <span>Remarks / Instructions</span>
                    </div>
                    <span class="section-badge">{{ selectedDocument?.remarks ? 'Has Remarks' : 'No Remarks' }}</span>
                  </div>
                  
                  <div v-if="!selectedDocument?.remarks" class="empty-state-compact">
                    <i class="bi bi-chat-left-text"></i>
                    <p>No remarks or instructions added for this document.</p>
                  </div>
                  
                  <div v-else class="remarks-card">
                    <div class="remarks-content">
                      <i class="bi bi-quote"></i>
                      <p>{{ selectedDocument.remarks }}</p>
                    </div>
                    <div class="remarks-meta" v-if="selectedDocument.updated_at">
                      <i class="bi bi-clock"></i>
                      <span>Last updated: {{ formatDateTime(selectedDocument.updated_at) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Route Progress Summary -->
               
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
  name: "ReceivedTab",
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
    currentUserId: {
      type: [Number, String],
      default: null,
    },
    currentUserOfficeId: {
      type: [Number, String],
      default: null,
    },
  },
  data() {
    return {
      documents: { 
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
      loading: false,

      // Forward Modal
      showForwardModal: false,
      forwardDocument: null,
      offices: [],
      officesLoading: false,
      selectedOffices: [],
      forwarding: false,
      forwardError: "",
      officeSearchQuery: "",
      selectedDuration: "",
      remarks: "",
      uploadedFile: null,
      filePreviewUrl: "",
      attachmentError: "",

      // Memo Slip
      memoSlips: [],
      selectedMemoSlips: [],
      memoSlipsLoading: false,

      // Forward File Preview Modal
      showFilePreview: false,

      // Release Modal
      showReleaseModal: false,
      releaseDocument: null,
      releasing: false,
      releaseError: "",
      releaseRemarks: "",
      
      // Release File Upload
      releaseUploadedFile: null,
      releaseFilePreviewUrl: "",
      releaseAttachmentError: "",
      showReleaseFilePreview: false,

      // Archive Modal
      showArchiveModal: false,
      archiveDocument: null,
      archiving: false,
      archiveError: "",
      archiveReason: "",
      archiveRemarks: "",

      // View Modal
      showViewModal: false,
      selectedDocument: null,
      viewerActiveTab: "details",
      pdfLoading: true,
      pdfLoadError: false,
      pdfZoom: 1,
      pdfViewerHeight: 700,
      routeHistoryLoading: false,

      // PST Clock
      currentPstDate: "",
      currentPstTime: "",
      pstClockInterval: null,
    };
  },
  computed: {
    isConfidential() {
      const classification = this.selectedDocument?.document?.document_classification;
      return classification?.toUpperCase() === 'CONFIDENTIAL';
    },
    filteredOffices() {
      if (!this.officeSearchQuery) return this.offices;
      const query = this.officeSearchQuery.toLowerCase();
      return this.offices.filter(office => 
        office.sub_office_name?.toLowerCase().includes(query) ||
        office.office_code?.toLowerCase().includes(query)
      );
    },
  },
  mounted() {
    this.getMemoSlips();
    this.getDataReceived();
    this.updatePstDateTime();
    this.pstClockInterval = setInterval(() => {
      this.updatePstDateTime();
    }, 1000);
  },
  beforeDestroy() {
    if (this.pstClockInterval) {
      clearInterval(this.pstClockInterval);
    }
  },
  methods: {
    getMemoSlips() {
      if (!this.selectedDocument) return [];
      
      const route = this.selectedDocument;
      
      if (route.memo_slip_action) {
        try {
          const parsed = JSON.parse(route.memo_slip_action);
          if (Array.isArray(parsed)) {
            return parsed;
          }
        } catch {
          if (typeof route.memo_slip_action === 'string') {
            const memos = route.memo_slip_action.split(',').map(m => m.trim()).filter(m => m !== '');
            return memos;
          }
        }
      }
      
      return [];
    },
    // ===== PST CLOCK =====
    updatePstDateTime() {
      const now = new Date();
      const options = {
        timeZone: "Asia/Manila",
        year: "numeric",
        month: "long",
        day: "numeric",
      };
      const timeOptions = {
        timeZone: "Asia/Manila",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: true,
      };
      this.currentPstDate = now.toLocaleDateString("en-PH", options);
      this.currentPstTime = now.toLocaleTimeString("en-PH", timeOptions);
    },

    // ===== DATA FETCHING =====
    async getDataReceived(page = 1) {
      try {
        this.loading = true;
        const response = await axios.get("/dts_denr/api/document/receive", {
          params: { 
            page, 
            per_page: this.perPage, 
            search: this.searchQuery, 
            document_type: this.docTypeFilter 
          },
        });
        this.documents = response.data.data;
      } catch (error) {
        console.error("Error fetching received data:", error);
        this.$emit("show-notification", {
          message: "Failed to load received data.",
          type: "error",
        });
      } finally {
        this.loading = false;
      }
    },

    applyFilters() { 
      this.getDataReceived(1); 
    },
    
    clearSearch() { 
      this.searchQuery = ""; 
      this.getDataReceived(1); 
    },
    
    clearDocTypeFilter() { 
      this.docTypeFilter = ""; 
      this.getDataReceived(1); 
    },
    
    clearAllFilters() { 
      this.searchQuery = ""; 
      this.docTypeFilter = ""; 
      this.getDataReceived(1); 
    },
    
    changePage(page) {
      if (page >= 1 && page <= this.documents.last_page && page !== this.documents.current_page) {
        this.getDataReceived(page);
      }
    },
    
    changePerPage() { 
      this.getDataReceived(1); 
    },

    getCurrentUserOfficeId() {
      if (this.currentUserOfficeId) return this.currentUserOfficeId;
      const officeIdMeta = document.querySelector('meta[name="user-office-id"]');
      if (officeIdMeta) return officeIdMeta.getAttribute('content');
      if (window.Laravel && window.Laravel.userOfficeId) return window.Laravel.userOfficeId;
      return null;
    },

    // ===== FORWARD FILE HANDLING =====
    triggerFileUpload() {
      if (!this.forwarding && this.$refs.fileInput) {
        this.$refs.fileInput.click();
      }
    },

    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.processSingleFile(file);
      }
      event.target.value = '';
    },

    handleDrop(event) {
      const file = event.dataTransfer.files[0];
      if (file) {
        this.processSingleFile(file);
      }
    },

    processSingleFile(file) {
      this.attachmentError = "";
      
      if (file.type !== 'application/pdf') {
        this.attachmentError = "Only PDF files are allowed. Please upload a PDF document.";
        return;
      }

      const maxSize = 20 * 1024 * 1024;
      if (file.size > maxSize) {
        this.attachmentError = "File size exceeds 20MB limit.";
        return;
      }

      this.uploadedFile = file;
      
      if (file.type === 'application/pdf') {
        this.filePreviewUrl = URL.createObjectURL(file);
      }
    },

    removeForwardFile() {
      if (this.filePreviewUrl) {
        URL.revokeObjectURL(this.filePreviewUrl);
      }
      this.uploadedFile = null;
      this.filePreviewUrl = "";
      this.attachmentError = "";
    },

    openFilePreview() {
      if (!this.uploadedFile) return;
      
      if (this.uploadedFile.type === 'application/pdf') {
        this.showFilePreview = true;
      } else {
        this.$emit("show-notification", {
          message: "Preview only available for PDF files.",
          type: "info",
        });
      }
    },

    closeFilePreview() {
      this.showFilePreview = false;
    },

    // ===== RELEASE FILE HANDLING =====
    triggerReleaseFileUpload() {
      if (!this.releasing && this.$refs.releaseFileInput) {
        this.$refs.releaseFileInput.click();
      }
    },

    handleReleaseFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.processReleaseFile(file);
      }
      event.target.value = '';
    },

    handleReleaseDrop(event) {
      const file = event.dataTransfer.files[0];
      if (file) {
        this.processReleaseFile(file);
      }
    },

    processReleaseFile(file) {
      this.releaseAttachmentError = "";
      
      if (file.type !== 'application/pdf') {
        this.releaseAttachmentError = "Only PDF files are allowed. Please upload a PDF document.";
        return;
      }

      const maxSize = 20 * 1024 * 1024;
      if (file.size > maxSize) {
        this.releaseAttachmentError = "File size exceeds 20MB limit.";
        return;
      }

      this.releaseUploadedFile = file;
      
      if (file.type === 'application/pdf') {
        this.releaseFilePreviewUrl = URL.createObjectURL(file);
      }
    },

    removeReleaseFile() {
      if (this.releaseFilePreviewUrl) {
        URL.revokeObjectURL(this.releaseFilePreviewUrl);
      }
      this.releaseUploadedFile = null;
      this.releaseFilePreviewUrl = "";
      this.releaseAttachmentError = "";
    },

    openReleaseFilePreview() {
      if (!this.releaseUploadedFile) return;
      
      if (this.releaseUploadedFile.type === 'application/pdf') {
        this.showReleaseFilePreview = true;
      } else {
        this.$emit("show-notification", {
          message: "Preview only available for PDF files.",
          type: "info",
        });
      }
    },

    closeReleaseFilePreview() {
      this.showReleaseFilePreview = false;
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // ===== DURATION HELPERS =====
    getDurationValue() {
      if (!this.forwardDocument?.document) return null;
      return this.forwardDocument.document.set_user_duration_id?.duration || 
             this.forwardDocument.document.duration || null;
    },

    getDurationDisplay() {
      const duration = this.getDurationValue();
      if (!duration) return "Not Set";
      const map = { 
        'Simple': 'Simple - 3 Days', 
        'Complex': 'Complex - 15 Days', 
        'Highly Technical': 'Highly Technical - 45 Days' 
      };
      return map[duration] || duration;
    },

    getDurationBadgeClass(duration) {
      if (!duration) return 'duration-not-set';
      const d = duration.toLowerCase();
      if (d.includes('simple')) return 'duration-simple';
      if (d.includes('complex')) return 'duration-complex';
      if (d.includes('highly technical')) return 'duration-highly-technical';
      return 'duration-not-set';
    },

    getDurationIcon(duration) {
      if (!duration) return 'bi bi-clock';
      const d = duration.toLowerCase();
      if (d.includes('simple')) return 'bi bi-clock';
      if (d.includes('complex')) return 'bi bi-clock-history';
      if (d.includes('highly technical')) return 'bi bi-clock-fill';
      return 'bi bi-clock';
    },

    hasExistingDuration() {
      return this.getDurationValue() !== null;
    },

    getDocumentDuration() {
      if (!this.selectedDocument?.document) return null;
      return this.selectedDocument.document.set_user_duration_id?.duration || 
             this.selectedDocument.document.duration || null;
    },

    getDocumentDurationDisplay() {
      const duration = this.getDocumentDuration();
      if (!duration) return "Not Set";
      const map = { 
        'Simple': 'Simple - 3 Days', 
        'Complex': 'Complex - 15 Days', 
        'Highly Technical': 'Highly Technical - 45 Days' 
      };
      return map[duration] || duration;
    },

    // ===== CLASSIFICATION HELPERS =====
    getClassificationBadgeClass(classification) {
      if (!classification) return '';
      const c = classification.toUpperCase();
      if (c === 'CONFIDENTIAL') return 'classification-confidential';
      if (c === 'RESTRICTED') return 'classification-restricted';
      if (c === 'TOP SECRET') return 'classification-top-secret';
      return 'classification-general';
    },

    isDocumentConfidential(classification) {
      return classification?.toUpperCase() === 'CONFIDENTIAL';
    },

    // ===== MEMO SLIP METHODS =====
    async fetchMemoSlips() {
      this.memoSlipsLoading = true;
      try {
        const response = await axios.get("/dts_denr/api-get-foward-memo-slip");
        this.memoSlips = response.data.data || [];
      } catch (error) {
        console.error("Error fetching memo slips:", error);
        this.forwardError = "Failed to load memo slips.";
      } finally {
        this.memoSlipsLoading = false;
      }
    },

    isMemoSlipSelected(memoId) {
      return this.selectedMemoSlips.some((memo) => memo.id === memoId);
    },

    toggleMemoSlipSelection(memo) {
      if (this.forwarding) return;
      const index = this.selectedMemoSlips.findIndex((m) => m.id === memo.id);
      if (index > -1) {
        this.selectedMemoSlips.splice(index, 1);
      } else {
        this.selectedMemoSlips.push(memo);
      }
    },

    selectAllMemoSlips() {
      if (this.forwarding) return;
      this.selectedMemoSlips = [...this.memoSlips];
    },

    deselectAllMemoSlips() {
      if (this.forwarding) return;
      this.selectedMemoSlips = [];
    },

    // ===== FORWARD MODAL =====
    openForwardModal(doc) {
      this.forwardDocument = doc;
      this.showForwardModal = true;
      this.selectedOffices = [];
      this.selectedMemoSlips = [];
      this.forwardError = "";
      this.officeSearchQuery = "";
      this.selectedDuration = "";
      this.remarks = "";
      this.uploadedFile = null;
      this.filePreviewUrl = "";
      this.attachmentError = "";
      this.fetchOffices();
      this.fetchMemoSlips();
    },

    closeForwardModal() {
      this.removeForwardFile();
      this.showForwardModal = false;
      this.forwardDocument = null;
      this.selectedOffices = [];
      this.selectedMemoSlips = [];
      this.forwardError = "";
      this.officeSearchQuery = "";
      this.selectedDuration = "";
      this.remarks = "";
      this.memoSlips = [];
    },

    clearForwardForm() {
      this.selectedOffices = [];
      this.selectedMemoSlips = [];
      this.forwardError = "";
      this.officeSearchQuery = "";
      this.selectedDuration = "";
      this.remarks = "";
      this.removeForwardFile();
    },

    clearOfficeSearch() {
      this.officeSearchQuery = "";
    },

    async fetchOffices() {
      this.officesLoading = true;
      try {
        const response = await axios.get("/dts_denr/api/route-office-secratariat");
        this.offices = response.data.data || [];
      } catch (error) {
        console.error("Error fetching offices:", error);
        this.forwardError = "Failed to load offices.";
      } finally {
        this.officesLoading = false;
      }
    },

    isOfficeSelected(officeId) {
      return this.selectedOffices.some((office) => office.id === officeId);
    },

    toggleOfficeSelection(office) {
      if (this.forwarding) return;
      this.forwardError = "";
      const index = this.selectedOffices.findIndex((o) => o.id === office.id);
      if (index > -1) {
        this.selectedOffices.splice(index, 1);
      } else {
        this.selectedOffices.push(office);
      }
    },

    removeOffice(officeId) {
      this.selectedOffices = this.selectedOffices.filter((office) => office.id !== officeId);
    },

    // ===== SUBMIT FORWARD DOCUMENT =====
    async submitForwardDocument() {
      if (this.selectedOffices.length === 0) {
        this.forwardError = "Please select at least one office.";
        return;
      }

      if (!this.hasExistingDuration() && !this.selectedDuration) {
        this.forwardError = "Please select a duration.";
        return;
      }

      this.forwarding = true;
      this.forwardError = "";

      try {
        const formData = new FormData();
        
        const durationValue = this.hasExistingDuration() 
          ? this.getDurationValue() 
          : this.selectedDuration;

        const currentOfficeId = this.getCurrentUserOfficeId();
        const routeId = this.forwardDocument.id;

        formData.append('offices', JSON.stringify(this.selectedOffices));
        formData.append('memoSlips', JSON.stringify(this.selectedMemoSlips));
        formData.append('selectedDuration', durationValue);
        formData.append('remarks', this.remarks || '');
        formData.append('from_office_id', currentOfficeId || '');

        if (this.uploadedFile) {
          formData.append('attachments[]', this.uploadedFile);
        }

        const response = await axios.post(
          `/dts_denr/api/forward-other-office/${routeId}`,
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          }
        );

        await Swal.fire({
          title: "Forwarded!",
          text: response.data.message || `Document forwarded to ${this.selectedOffices.length} office(s) successfully.`,
          icon: "success",
          confirmButtonColor: "#1a4731",
        });

        this.closeForwardModal();
        this.$emit("show-notification", {
          message: "Document forwarded successfully!",
          type: "success",
        });
        this.getDataReceived(this.documents.current_page);

      } catch (error) {
        console.error("Forward error details:", error);

        let errorMessage = "Failed to forward document. Please try again.";

        if (error.response) {
          errorMessage = error.response.data?.message || errorMessage;
          if (error.response.data?.errors) {
            const errors = error.response.data.errors;
            const errorMessages = Object.values(errors).flat();
            errorMessage = errorMessages.join(', ');
          }
        } else if (error.request) {
          errorMessage = "No response from server. Please check your connection.";
        }

        this.forwardError = errorMessage;
        this.$emit("show-notification", {
          message: errorMessage,
          type: "error"
        });
      } finally {
        this.forwarding = false;
      }
    },

    // ===== RELEASE MODAL =====
    openReleaseModal(doc) {
      this.releaseDocument = doc;
      this.showReleaseModal = true;
      this.releaseError = "";
      this.releaseRemarks = "";
      this.releaseUploadedFile = null;
      this.releaseFilePreviewUrl = "";
      this.releaseAttachmentError = "";
    },

    closeReleaseModal() {
      this.removeReleaseFile();
      this.showReleaseModal = false;
      this.releaseDocument = null;
      this.releaseError = "";
      this.releaseRemarks = "";
    },

    async submitReleaseDocument() {
      this.releasing = true;
      this.releaseError = "";

      try {
        const formData = new FormData();
        formData.append('document_route_id', this.releaseDocument.id);
        formData.append('tracking_number', this.releaseDocument.document.tracking_number);
        formData.append('remarks', this.releaseRemarks || '');
        formData.append('released_at', new Date().toISOString());

        if (this.releaseUploadedFile) {
          formData.append('attachments[]', this.releaseUploadedFile);
        }

        const response = await axios.post("/dts_denr/api/for-release-document", formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        await Swal.fire({
          title: "For Released to Records!",
          text: response.data.message || "Document has been released successfully.",
          icon: "success",
          confirmButtonColor: "#1a4731",
          confirmButtonText: "OK",
        });

        this.closeReleaseModal();
        this.$emit("show-notification", {
          message: "Document released successfully!",
          type: "success",
        });
        this.getDataReceived(this.documents.current_page);
      } catch (error) {
        console.error("Release document error:", error);
        const message = error.response?.data?.message || "Failed to release document. Please try again.";
        this.releaseError = message;
        this.$emit("show-notification", { message, type: "error" });
      } finally {
        this.releasing = false;
      }
    },

    // ===== ARCHIVE MODAL =====
    openArchiveModal(doc) {
      this.archiveDocument = doc;
      this.showArchiveModal = true;
      this.archiveError = "";
      this.archiveReason = "";
      this.archiveRemarks = "";
    },

    closeArchiveModal() {
      this.showArchiveModal = false;
      this.archiveDocument = null;
      this.archiveError = "";
      this.archiveReason = "";
      this.archiveRemarks = "";
    },

    async submitArchiveDocument() {
      if (!this.archiveReason) {
        this.archiveError = "Please select a reason for archiving.";
        return;
      }

      this.archiving = true;
      this.archiveError = "";

      try {
        const response = await axios.post("/dts_denr/api/archive-document", {
          document_route_id: this.archiveDocument.id,
          tracking_number: this.archiveDocument.document.tracking_number,
          reason: this.archiveReason,
          remarks: this.archiveRemarks || "",
          archived_at: new Date().toISOString(),
        });

        await Swal.fire({
          title: "Archived!",
          text: response.data.message || "Document has been archived successfully.",
          icon: "success",
          confirmButtonColor: "#1a4731",
          confirmButtonText: "OK",
        });

        this.closeArchiveModal();
        this.$emit("show-notification", {
          message: "Document archived successfully!",
          type: "success",
        });
        this.getDataReceived(this.documents.current_page);
      } catch (error) {
        console.error("Archive document error:", error);
        const message = error.response?.data?.message || "Failed to archive document. Please try again.";
        this.archiveError = message;
        this.$emit("show-notification", { message, type: "error" });
      } finally {
        this.archiving = false;
      }
    },

    // ===== VIEW MODAL =====
    viewDocument(doc) {
      this.selectedDocument = doc;
      this.showViewModal = true;
      this.viewerActiveTab = "details";
      this.pdfLoading = true;
      this.pdfLoadError = false;
      this.pdfZoom = 1;
      this.pdfViewerHeight = 700;
    },

    closeViewModal() {
      this.showViewModal = false;
      this.selectedDocument = null;
    },

    getDocumentRoutes() {
      return this.selectedDocument?.document?.document_route || [];
    },

    getRouteOfficeName(route) {
      return route.office?.sub_office_name || route.sub_office_name || "Unknown Office";
    },

    getPdfUrl(document) {
      if (!document?.document?.tracking_number) return "";
      if (document.document.draft_attachment) {
        return `/dts_denr/storage/app/public/${document.document.draft_attachment}`;
      }
      return `/dts_denr/storage/app/public/attachments/${document.document.tracking_number}/draft_attachment.pdf`;
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
        this.pdfViewerHeight = Math.round(700 / this.pdfZoom);
      }
    },

    zoomOut() {
      if (this.pdfZoom > 0.5) {
        this.pdfZoom -= 0.25;
        this.pdfViewerHeight = Math.round(700 / this.pdfZoom);
      }
    },

    isRouteActive(route) {
      return ["In Progress", "Active", "Current"].includes(route.status) || route.is_current === true;
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
      const map = {
        completed: "bi bi-check-circle-fill",
        active: "bi bi-arrow-repeat",
        rejected: "bi bi-x-circle-fill",
        pending: "bi bi-clock-fill",
      };
      return map[this.getRouteStatusType(status)] || "bi bi-clock-fill";
    },

    // ===== UTILITY =====
    formatDate(dateString) {
      if (!dateString) return "N/A";
      return new Date(dateString).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
      });
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
        "PENDING": "status-in-progress",
        "In-Progress": "status-in-progress",
        "For-Release": "status-for-release",
        "Released": "status-released",
        "Completed": "status-released",
      };
      return map[status] || "";
    },

    refreshData() {
      this.getDataReceived(1);
    },
  },
};
</script>

<style scoped>
/* ===== FILTER CONTROLS ===== */
.filter-controls { margin-bottom: 20px; }
.search-filter-row { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
.search-box-wrapper { flex: 1; min-width: 200px; }
.search-box { position: relative; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.search-input { width: 100%; padding: 10px 35px 10px 36px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; background: #fff; transition: all 0.3s ease; }
.search-input:focus { border-color: #2d6a4f; box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1); }
.search-clear-btn { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; }
.search-clear-btn:hover { color: #ef4444; }
.per-page-wrapper { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
.per-page-label { font-size: 13px; color: #6b7280; font-weight: 500; }
.per-page-select { padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 13px; background: #fff; outline: none; cursor: pointer; transition: all 0.3s ease; }
.per-page-select:focus { border-color: #2d6a4f; }
.filter-wrapper { min-width: 180px; }
.filter-box { position: relative; }
.filter-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
.filter-select { width: 100%; padding: 10px 12px 10px 36px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 13px; background: #fff; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; transition: all 0.3s ease; }
.filter-select:focus { border-color: #2d6a4f; box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.1); }

.active-filters { display: flex; align-items: center; gap: 8px; margin-top: 12px; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; flex-wrap: wrap; }
.active-filters-label { font-size: 12px; font-weight: 600; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; }
.filter-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #fff; border: 1px solid #86efac; border-radius: 20px; font-size: 12px; color: #166534; font-weight: 500; }
.filter-tag-close { background: none; border: none; color: #6b7280; cursor: pointer; padding: 0; }
.filter-tag-close:hover { color: #ef4444; }
.clear-all-filters { padding: 4px 12px; background: none; border: 1px solid #86efac; border-radius: 6px; font-size: 12px; color: #166534; cursor: pointer; font-weight: 600; transition: all 0.2s; }
.clear-all-filters:hover { background: #dcfce7; border-color: #166534; }
.results-summary { margin-top: 10px; font-size: 13px; color: #6b7280; }
.results-count { font-weight: 700; color: #2d6a4f; font-size: 16px; }

/* ===== TABLE ===== */
.table-responsive { overflow-x: auto; margin-top: 16px; }
.office-table { width: 100%; border-collapse: collapse; font-size: 13px; background: #fff; }
.office-table th, .office-table td { border: 1px solid #f3f4f6; padding: 12px 14px; text-align: left; vertical-align: middle; }
.office-table thead th { background: #f8fafc; font-weight: 700; color: #374151; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; border-bottom: 2px solid #e5e7eb; }
.office-table tbody tr:hover { background: #f0fdf4; }
.office-table tbody tr:nth-child(even) { background: #fafafa; }
.office-table tbody tr:nth-child(even):hover { background: #f0fdf4; }

.row-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 6px; background: #f3f4f6; color: #6b7280; font-size: 12px; font-weight: 600; }
.tracking-number { font-weight: 700; color: #2d6a4f; font-family: "Courier New", monospace; font-size: 13px; padding: 2px 8px; background: #f0fdf4; border-radius: 4px; }
.doc-type-badge { display: inline-block; padding: 3px 10px; background: #e0e7ff; color: #3730a3; border-radius: 4px; font-size: 11px; font-weight: 600; border: 1px solid #c7d2fe; }
.subject-text { color: #1e293b; font-weight: 500; line-height: 1.4; font-size: 13px; position: relative; }
.date-received { color: #64748b; font-size: 12px; display: flex; align-items: center; gap: 6px; }
.date-icon { color: #94a3b8; font-size: 13px; }

/* ===== ACTION BUTTONS ===== */
.action-buttons { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
.btn-action { background: none; border: 1px solid transparent; border-radius: 6px; padding: 6px 10px; cursor: pointer; font-size: 0.95rem; transition: all 0.2s; }
.btn-view { color: #6366f1; border-color: #c7d2fe; background: #eef2ff; }
.btn-view:hover { background: #ddd6fe; transform: scale(1.05); }
.btn-forward { color: #0891b2; border-color: #67e8f9; background: #cffafe; }
.btn-forward:hover { background: #67e8f9; transform: scale(1.05); }
.btn-release { color: #059669; border-color: #6ee7b7; background: #d1fae5; }
.btn-release:hover { background: #6ee7b7; transform: scale(1.05); }
.btn-archive { color: #6b7280; border-color: #d1d5db; background: #f3f4f6; }
.btn-archive:hover { background: #e5e7eb; transform: scale(1.05); }

/* ===== CONFIDENTIAL ===== */
.confidential-blur-static { filter: blur(8px); user-select: none; pointer-events: none; position: relative; }
.confidential-blur-static .confidential-label { display: inline-block; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.7rem; font-weight: 800; color: #dc2626; background: rgba(255,255,255,0.95); padding: 2px 10px; border-radius: 4px; border: 2px solid #dc2626; letter-spacing: 1px; text-transform: uppercase; pointer-events: none; white-space: nowrap; filter: none; }
.confidential-label { display: inline-block; font-size: 0.7rem; font-weight: 800; color: #dc2626; background: rgba(255,255,255,0.95); padding: 2px 10px; border-radius: 4px; border: 2px solid #dc2626; letter-spacing: 1px; text-transform: uppercase; margin-left: 8px; white-space: nowrap; }

/* ===== CLASSIFICATION BADGES ===== */
.classification-confidential { display: inline-block; padding: 3px 10px; background: #fee2e2; color: #991b1b; border-radius: 4px; font-size: 11px; font-weight: 700; border: 1px solid #fecaca; text-transform: uppercase; }
.classification-restricted { display: inline-block; padding: 3px 10px; background: #fed7aa; color: #9a3412; border-radius: 4px; font-size: 11px; font-weight: 700; border: 1px solid #fdba74; text-transform: uppercase; }
.classification-top-secret { display: inline-block; padding: 3px 10px; background: #fce7f3; color: #9d174d; border-radius: 4px; font-size: 11px; font-weight: 700; border: 1px solid #f9a8d4; text-transform: uppercase; }
.classification-general { display: inline-block; padding: 3px 10px; background: #d1fae5; color: #065f46; border-radius: 4px; font-size: 11px; font-weight: 700; border: 1px solid #a7f3d0; text-transform: uppercase; }
.classification-badge-large { display: inline-block; padding: 4px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

/* ===== DURATION BADGES ===== */
.duration-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.duration-simple { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
.duration-complex { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
.duration-highly-technical { background: #fce7f3; color: #9d174d; border: 1px solid #f9a8d4; }
.duration-not-set { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }
.duration-badge-large { display: inline-flex; align-items: center; gap: 6px; padding: 4px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; }
.duration-detail-card { background: #f0fdf4; border-color: #86efac; }

/* ===== MODAL ===== */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.55); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 1050; animation: fadeIn 0.2s ease-out; }
.enhanced-modal { width: 100%; margin: 0 15px; animation: slideUp 0.3s ease-out; }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.square-modal { border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.3); background: #fff; }

.square-header { background: linear-gradient(135deg, #1e4d2b, #2d6a4f); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); }
.square-icon { width: 38px; height: 38px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 12px; }
.modal-title { font-weight: 700; font-size: 1.1rem; }
.modal-subtitle { font-size: 0.8rem; opacity: 0.85; }
.square-close { background: rgba(255,255,255,0.15); border: none; color: #fff; width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; }
.square-close:hover { background: rgba(255,255,255,0.3); }
.square-close:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-body { padding: 16px 20px; background: #f9fafb; max-height: calc(90vh - 120px); overflow-y: auto; }
.modal-body::-webkit-scrollbar { width: 4px; }
.modal-body::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 2px; }

.error-msg { display: flex; align-items: center; gap: 6px; background: #fef0ef; border: 1px solid #f5c6c3; border-radius: 6px; padding: 8px 12px; font-size: 12px; color: #c0392b; margin-bottom: 12px; font-weight: 500; }

/* ===== REDESIGNED FORWARD MODAL ===== */
.forward-modal-large { max-width: 1200px; width: 98vw; }

.forward-header { background: linear-gradient(135deg, #0f3b2a, #1a5a3a); padding: 14px 24px; }
.forward-icon { background: rgba(255,255,255,0.2); }

.forward-modal-body { padding: 20px 24px; max-height: calc(90vh - 80px); overflow-y: auto; background: #f8fafc; }

.forward-doc-info-bar { display: flex; align-items: center; gap: 16px; padding: 10px 16px; background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 16px; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.doc-info-item { display: flex; align-items: center; gap: 6px; }
.doc-info-label { font-size: 0.6rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }
.doc-info-value { font-size: 0.85rem; font-weight: 600; color: #1e293b; }
.doc-info-subject { flex: 1; min-width: 150px; }
.subject-truncate { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block; }
.doc-info-divider { width: 1px; height: 24px; background: #e5e7eb; flex-shrink: 0; }

.forward-main-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

.forward-col { display: flex; flex-direction: column; background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }

.col-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
.col-header-left { display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 0.8rem; color: #1e293b; }
.col-header-left i { color: #2d6a4f; font-size: 0.9rem; }
.required-star { color: #dc2626; margin-left: 2px; }
.optional-badge { font-size: 0.55rem; font-weight: 600; padding: 2px 8px; background: #f3f4f6; color: #6b7280; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.3px; }
.selection-count { font-size: 0.7rem; font-weight: 600; color: #059669; background: #d1fae5; padding: 1px 8px; border-radius: 10px; }

/* Office Column */
.office-col { display: flex; flex-direction: column; }
.office-search-box { position: relative; padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
.office-search-icon { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.8rem; }
.office-search-input { width: 100%; padding: 6px 30px 6px 28px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.75rem; outline: none; background: #fff; transition: all 0.3s ease; }
.office-search-input:focus { border-color: #2d6a4f; box-shadow: 0 0 0 2px rgba(45,106,79,0.1); }
.office-search-clear { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 2px; }
.office-search-clear:hover { color: #ef4444; }

.office-list { flex: 1; overflow-y: auto; max-height: 280px; padding: 4px 0; }
.office-list::-webkit-scrollbar { width: 4px; }
.office-list::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 2px; }

.office-item { display: flex; align-items: center; gap: 8px; padding: 6px 12px; cursor: pointer; transition: all 0.15s ease; border-left: 2px solid transparent; }
.office-item:hover { background: #f0fdf4; }
.office-item.selected { background: #f0fdf4; border-left-color: #059669; }
.office-item-check { font-size: 1rem; color: #d1d5db; min-width: 18px; display: flex; align-items: center; justify-content: center; }
.office-item.selected .office-item-check { color: #059669; }
.office-item-icon { width: 26px; height: 26px; min-width: 26px; border-radius: 6px; background: #e0e7ff; display: flex; align-items: center; justify-content: center; color: #3730a3; font-size: 0.7rem; }
.office-item.selected .office-item-icon { background: #d1fae5; color: #059669; }
.office-item-info { flex: 1; min-width: 0; }
.office-item-name { display: block; font-size: 0.75rem; font-weight: 600; color: #1e293b; line-height: 1.2; }
.office-item-code { display: block; font-size: 0.55rem; color: #64748b; }

.selected-tags { padding: 8px 12px; border-top: 1px solid #e5e7eb; background: #f0fdf4; max-height: 80px; overflow-y: auto; }
.selected-tags-header { display: flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.65rem; color: #166534; margin-bottom: 4px; }
.badge-count { background: #d1fae5; color: #059669; padding: 0 6px; border-radius: 10px; font-size: 0.6rem; }
.selected-tags-list { display: flex; flex-wrap: wrap; gap: 4px; }
.selected-tag { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: #fff; border: 1px solid #86efac; border-radius: 12px; font-size: 0.65rem; font-weight: 600; color: #166534; }
.remove-tag-btn { background: none; border: none; color: #6b7280; cursor: pointer; padding: 0; font-size: 0.7rem; transition: color 0.2s; }
.remove-tag-btn:hover { color: #dc2626; }
.remove-tag-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Memo Column */
.memo-col { display: flex; flex-direction: column; }
.memo-section { flex: 1; display: flex; flex-direction: column; }
.memo-actions { display: flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap; }
.memo-action-btn { padding: 2px 10px; border: none; border-radius: 4px; font-size: 0.65rem; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 3px; }
.select-all-btn { background: #d1fae5; color: #059669; }
.select-all-btn:hover:not(:disabled) { background: #a7f3d0; }
.deselect-all-btn { background: #fee2e2; color: #dc2626; }
.deselect-all-btn:hover:not(:disabled) { background: #fecaca; }
.memo-action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.memo-count-badge { font-size: 0.65rem; font-weight: 600; color: #64748b; margin-left: auto; }

.memo-list { flex: 1; overflow-y: auto; max-height: 280px; padding: 4px 0; }
.memo-list::-webkit-scrollbar { width: 4px; }
.memo-list::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 2px; }

.memo-item { display: flex; align-items: center; gap: 8px; padding: 6px 12px; cursor: pointer; transition: all 0.15s ease; border-left: 2px solid transparent; }
.memo-item:hover { background: #f0fdf4; }
.memo-item.selected { background: #f0fdf4; border-left-color: #059669; }
.memo-item-check { font-size: 0.95rem; color: #d1d5db; min-width: 18px; display: flex; align-items: center; justify-content: center; }
.memo-item.selected .memo-item-check { color: #059669; }
.memo-item-content { flex: 1; min-width: 0; }
.memo-item-name { display: block; font-size: 0.75rem; font-weight: 500; color: #1e293b; line-height: 1.2; }

/* Details Column */
.details-col { display: flex; flex-direction: column; gap: 0; }
.detail-section { padding: 10px 14px; border-bottom: 1px solid #e5e7eb; }
.detail-section:last-child { border-bottom: none; }
.detail-section .col-header { padding: 0 0 6px 0; background: none; border-bottom: none; }

.duration-display { display: flex; flex-direction: column; gap: 6px; }
.duration-display-box { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; }
.duration-display-icon { width: 28px; height: 28px; min-width: 28px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; color: #059669; }
.duration-display-info { flex: 1; }
.duration-display-label { display: block; font-size: 0.55rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }
.duration-display-value { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
.readonly-badge { font-size: 0.55rem; font-weight: 600; color: #6b7280; background: #e5e7eb; padding: 1px 8px; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.3px; }
.duration-note { display: flex; align-items: center; gap: 4px; padding: 4px 8px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 0.6rem; color: #166534; }
.duration-note i { color: #16a34a; font-size: 0.7rem; }

.duration-select { width: 100%; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.75rem; background: #fff; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; transition: all 0.3s ease; }
.duration-select:focus { border-color: #2d6a4f; box-shadow: 0 0 0 2px rgba(45,106,79,0.1); }
.duration-select:disabled { opacity: 0.6; cursor: not-allowed; }
.duration-hint { display: flex; align-items: center; gap: 6px; padding: 6px 10px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 4px; font-size: 0.7rem; color: #92400e; margin-top: 4px; }
.duration-hint i { color: #d97706; }

/* Dropzone Compact */
.dropzone-compact { border: 2px dashed #d1d5db; border-radius: 6px; padding: 12px 16px; cursor: pointer; transition: all 0.3s ease; background: #fafafa; }
.dropzone-compact:hover { border-color: #2d6a4f; background: #f0fdf4; }
.dropzone-compact-content { display: flex; align-items: center; gap: 12px; }
.dropzone-compact-content i { font-size: 1.5rem; color: #9ca3af; transition: all 0.3s ease; }
.dropzone-compact:hover .dropzone-compact-content i { color: #2d6a4f; }
.dropzone-title { font-size: 0.75rem; font-weight: 600; color: #1e293b; margin: 0; }
.dropzone-hint { font-size: 0.6rem; color: #94a3b8; margin: 0; }

.file-card-compact { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 6px; transition: all 0.3s ease; }
.file-card-compact:hover { border-color: #2d6a4f; background: #f0fdf4; }
.file-card-icon { width: 32px; height: 32px; min-width: 32px; border-radius: 6px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; font-size: 1rem; }
.file-card-info { flex: 1; min-width: 0; }
.file-card-name { display: block; font-size: 0.7rem; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-card-size { font-size: 0.55rem; color: #94a3b8; }
.file-card-actions { display: flex; gap: 3px; }
.file-action-btn { width: 26px; height: 26px; border: none; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.7rem; background: #f1f5f9; color: #64748b; }
.file-action-btn:hover { background: #e2e8f0; }
.file-action-btn.remove-btn:hover { background: #fee2e2; color: #dc2626; }

.remarks-textarea { width: 100%; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.75rem; outline: none; background: #fff; transition: all 0.3s ease; resize: vertical; font-family: inherit; min-height: 50px; }
.remarks-textarea:focus { border-color: #2d6a4f; box-shadow: 0 0 0 2px rgba(45,106,79,0.1); }
.remarks-textarea:disabled { opacity: 0.6; cursor: not-allowed; }
.remarks-counter { text-align: right; font-size: 0.6rem; color: #94a3b8; margin-top: 2px; }
.remarks-counter.text-danger { color: #dc2626; }

.loading-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; text-align: center; }
.loading-state .text-muted { font-size: 0.7rem; color: #94a3b8; }
.empty-state-compact { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; text-align: center; color: #94a3b8; }
.empty-state-compact i { font-size: 1.5rem; margin-bottom: 4px; color: #d1d5db; }
.empty-state-compact p { font-size: 0.7rem; margin: 0; }

.forward-actions { margin-top: 16px; padding-top: 12px; border-top: 1px solid #e5e7eb; }
.modal-actions { display: flex; justify-content: space-between; align-items: center; }
.d-flex { display: flex; }
.gap-2 { gap: 8px; }
.me-1 { margin-right: 4px; }
.btn { padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; border: 1px solid transparent; }
.btn-outline-secondary { background: transparent; border-color: #d1d5db; color: #6b7280; }
.btn-outline-secondary:hover { background: #f3f4f6; }
.btn-light { background: #f3f4f6; border-color: #e5e7eb; color: #1e293b; }
.btn-light:hover { background: #e5e7eb; }
.btn-save { background: linear-gradient(135deg, #1e4d2b, #2d6a4f); color: #fff; border: none; box-shadow: 0 2px 12px rgba(26,71,49,0.3); }
.btn-save:hover:not(:disabled) { box-shadow: 0 4px 16px rgba(26,71,49,0.4); transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
.square-btn { border-radius: 6px !important; }
.spinner-border-sm { width: 1rem; height: 1rem; border-width: 0.15em; }

/* ===== RELEASE MODAL ===== */
.release-warning-icon { display: inline-flex; align-items: center; justify-content: center; width: 70px; height: 70px; border-radius: 50%; background: #dbeafe; font-size: 2.5rem; color: #2563eb; border: 3px solid #bfdbfe; }
.release-doc-info { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; display: flex; flex-direction: column; gap: 10px; }
.release-doc-row { display: flex; align-items: center; gap: 12px; }
.release-doc-icon-wrapper { width: 36px; height: 36px; min-width: 36px; border-radius: 8px; background: #dbeafe; display: flex; align-items: center; justify-content: center; color: #2563eb; font-size: 1rem; }
.release-doc-content { flex: 1; min-width: 0; }
.release-doc-label { display: block; font-size: 0.65rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.release-doc-value { display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; }

/* ===== ARCHIVE MODAL ===== */
.archive-warning-icon { display: inline-flex; align-items: center; justify-content: center; width: 70px; height: 70px; border-radius: 50%; background: #fef3c7; font-size: 2.5rem; color: #d97706; border: 3px solid #fde68a; }
.archive-doc-info { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; display: flex; flex-direction: column; gap: 10px; }
.archive-doc-row { display: flex; align-items: center; gap: 12px; }
.archive-doc-icon-wrapper { width: 36px; height: 36px; min-width: 36px; border-radius: 8px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #6b7280; font-size: 1rem; }
.archive-doc-content { flex: 1; min-width: 0; }
.archive-doc-label { display: block; font-size: 0.65rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.archive-doc-value { display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; }

.archive-select { width: 100%; padding: 6px 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 0.8rem; background: #fff; outline: none; cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
.archive-select:focus { border-color: #6b7280; box-shadow: 0 0 0 2px rgba(107,114,128,0.1); }
.archive-select:disabled { opacity: 0.6; cursor: not-allowed; }
.archive-hint { display: flex; align-items: center; gap: 6px; padding: 6px 10px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 4px; font-size: 0.7rem; color: #92400e; margin-top: 4px; }
.archive-hint i { color: #d97706; }

/* ===== SYSTEM DATETIME ===== */
.system-datetime-box { margin-top: 16px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe; border-radius: 10px; overflow: hidden; }
.system-datetime-header { padding: 10px 16px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 8px; color: #fff; }
.system-datetime-header i { color: rgba(255,255,255,0.8); }
.system-datetime-display { padding: 16px; display: flex; gap: 24px; flex-wrap: wrap; }
.datetime-item { display: flex; align-items: center; gap: 8px; }
.datetime-item i { color: #2563eb; font-size: 1.1rem; }
.datetime-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
.datetime-value { font-size: 0.95rem; font-weight: 700; color: #1e293b; font-family: "Courier New", monospace; }
.system-datetime-footer { padding: 8px 16px; background: rgba(37, 99, 235, 0.05); border-top: 1px solid #bfdbfe; display: flex; align-items: center; gap: 6px; font-size: 0.75rem; color: #64748b; }
.system-datetime-footer i { color: #2563eb; }

/* ===== ATTACHMENT SECTION ===== */
.attachment-section { margin-top: 4px; }
.dropzone { border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px 16px; text-align: center; cursor: pointer; transition: all 0.3s ease; background: #fafafa; }
.dropzone:hover { border-color: #2d6a4f; background: #f0fdf4; }
.dropzone-content { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.dropzone-icon { font-size: 2rem; color: #9ca3af; margin-bottom: 4px; transition: all 0.3s ease; }
.dropzone:hover .dropzone-icon { color: #2d6a4f; transform: translateY(-2px); }
.dropzone h6 { font-size: 0.85rem; color: #1e293b; margin-bottom: 2px; }
.dropzone .text-muted { font-size: 0.7rem; color: #94a3b8; }

.single-file-card { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f8fafc; border: 2px solid #e5e7eb; border-radius: 8px; gap: 12px; transition: all 0.3s ease; }
.single-file-card:hover { border-color: #2d6a4f; background: #f0fdf4; }
.file-info { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }
.file-icon-wrap { width: 40px; height: 40px; min-width: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.file-icon-wrap.file-pdf { background: #fee2e2; color: #dc2626; }
.file-details { flex: 1; min-width: 0; }
.file-name { font-size: 0.8rem; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-size { font-size: 0.65rem; color: #94a3b8; }
.file-actions { display: flex; gap: 4px; }
.btn-icon { width: 30px; height: 30px; border: none; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.8rem; }
.btn-icon-view { background: #dbeafe; color: #2563eb; }
.btn-icon-view:hover { background: #bfdbfe; transform: scale(1.05); }
.btn-icon-replace { background: #fef3c7; color: #d97706; }
.btn-icon-replace:hover { background: #fde68a; transform: scale(1.05); }
.btn-icon-remove { background: #fee2e2; color: #dc2626; }
.btn-icon-remove:hover { background: #fecaca; transform: scale(1.05); }
.field-error { display: flex; align-items: center; gap: 6px; margin-top: 4px; padding: 6px 10px; background: #fef0ef; border: 1px solid #f5c6c3; border-radius: 4px; font-size: 0.7rem; color: #c0392b; }

/* ===== DOCUMENT VIEWER ===== */
.document-view-modal { max-width: 1600px; width: 98vw; max-height: 95vh; }
.document-viewer-body { padding: 0; max-height: calc(95vh - 80px); overflow: hidden; display: flex; flex-direction: column; }
.document-viewer-tabs { display: flex; gap: 4px; padding: 10px 16px; background: #f8fafc; border-bottom: 2px solid #e5e7eb; flex-shrink: 0; }
.viewer-tab-btn { display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; color: #64748b; font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.3s ease; }
.viewer-tab-btn:hover { background: #f0fdf4; border-color: #86efac; color: #2d6a4f; transform: translateY(-1px); }
.viewer-tab-btn.active { background: linear-gradient(135deg, #2d6a4f, #1e4d2b); border-color: #2d6a4f; color: #fff; box-shadow: 0 4px 12px rgba(45,106,79,0.3); }

.document-viewer-layout { display: grid; grid-template-columns: 280px 1fr; height: calc(95vh - 140px); min-height: 500px; }
.details-panel { background: #fff; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; overflow: hidden; }
.details-panel-header { display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; font-weight: 700; color: #1e293b; font-size: 0.85rem; flex-shrink: 0; }
.details-content { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.details-content::-webkit-scrollbar { width: 4px; }
.details-content::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 2px; }

.detail-card { display: flex; gap: 10px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; }
.detail-icon-wrapper { width: 32px; height: 32px; min-width: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; background: #e0f2fe; color: #0284c7; }
.detail-icon-wrapper.classification-icon { background: #fee2e2; color: #991b1b; }
.detail-icon-wrapper.type-icon { background: #fef3c7; color: #d97706; }
.detail-icon-wrapper.subject-icon { background: #dcfce7; color: #16a34a; }
.detail-icon-wrapper.sender-icon-card { background: #ede9fe; color: #7c3aed; }
.detail-icon-wrapper.duration-icon { background: #f0fdf4; color: #16a34a; }
.detail-info { flex: 1; min-width: 0; }
.detail-info label { display: block; font-size: 0.6rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
.detail-value { font-size: 0.8rem; color: #1e293b; font-weight: 500; word-break: break-word; line-height: 1.3; position: relative; }
.tracking-number-large { font-size: 0.85rem; font-weight: 700; color: #2d6a4f; font-family: "Courier New", monospace; background: #f0fdf4; padding: 2px 8px; border-radius: 4px; display: inline-block; }
.doc-type-badge-large { display: inline-block; padding: 2px 10px; background: #e0e7ff; color: #3730a3; border-radius: 4px; font-size: 0.75rem; font-weight: 600; border: 1px solid #c7d2fe; }

/* ===== PDF PANEL ===== */
.pdf-panel { display: flex; flex-direction: column; background: #f8fafc; overflow: hidden; }
.pdf-panel-header { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; background: #fff; border-bottom: 1px solid #e5e7eb; flex-shrink: 0; }
.pdf-panel-title { display: flex; align-items: center; gap: 6px; font-weight: 700; color: #1e293b; font-size: 0.85rem; }
.pdf-panel-title i { color: #dc2626; font-size: 1rem; }
.text-danger { color: #dc2626 !important; }
.pdf-controls { display: flex; gap: 4px; align-items: center; }
.btn-pdf-control { background: #f1f5f9; border: 1px solid #e5e7eb; color: #475569; width: 28px; height: 28px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.8rem; }
.btn-pdf-control:hover:not(:disabled) { background: #e2e8f0; border-color: #94a3b8; color: #1e293b; }
.btn-pdf-control:disabled { opacity: 0.5; cursor: not-allowed; }

.pdf-viewer-wrapper { flex: 1; overflow: auto; background: #525659; position: relative; min-height: 500px; }
.pdf-viewer-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
.pdf-viewer-wrapper::-webkit-scrollbar-track { background: #3a3d40; }
.pdf-viewer-wrapper::-webkit-scrollbar-thumb { background: #6b7280; border-radius: 4px; }
.pdf-iframe { border: none; display: block; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }

.pdf-state { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 500px; color: #9ca3af; padding: 30px; }
.pdf-loader-animation { position: relative; width: 60px; height: 60px; margin-bottom: 12px; }
.pdf-loader-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.5rem; color: #dc2626; z-index: 2; }
.pdf-state-text { color: #d1d5db; font-size: 0.85rem; margin-top: 6px; }
.pdf-error { color: #fca5a5; }
.pdf-error h5 { color: #fca5a5; font-weight: 600; font-size: 1rem; }

.pdf-footer { display: flex; justify-content: space-between; align-items: center; padding: 6px 16px; background: #fff; border-top: 1px solid #e5e7eb; font-size: 0.7rem; color: #64748b; flex-shrink: 0; }
.pdf-zoom-level { font-weight: 600; color: #2d6a4f; }
.pdf-page-info { font-family: "Courier New", monospace; }

/* ===== ROUTE HISTORY ===== */
.route-history-panel { display: flex; flex-direction: column; height: calc(95vh - 140px); background: #fff; min-height: 500px; }
.route-history-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: #f8fafc; border-bottom: 2px solid #e5e7eb; flex-shrink: 0; }
.route-history-title { display: flex; align-items: center; gap: 8px; font-weight: 700; color: #1e293b; font-size: 0.9rem; }
.route-history-title i { color: #2d6a4f; font-size: 1rem; }
.route-history-badge { display: flex; align-items: center; gap: 4px; padding: 4px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 16px; font-size: 0.7rem; font-weight: 600; color: #166534; }
.route-history-content { flex: 1; overflow-y: auto; padding: 16px; background: #f9fafb; }
.route-history-content::-webkit-scrollbar { width: 6px; }
.route-history-content::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 3px; }

.route-state-box { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 200px; text-align: center; }
.empty-icon-wrapper { width: 60px; height: 60px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #94a3b8; margin-bottom: 12px; }

.timeline-container { position: relative; padding-left: 35px; }
.timeline-container::before { content: ""; position: absolute; left: 12px; top: 10px; bottom: 10px; width: 2px; background: linear-gradient(180deg, #cbd5e1, #e2e8f0); }
.timeline-item { position: relative; margin-bottom: 20px; }
.timeline-item.is-last { margin-bottom: 0; }
.timeline-node { position: absolute; left: -30px; top: 0; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; z-index: 2; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 0.65rem; }
.node-completed { background: #059669; }
.node-active { background: #2563eb; animation: pulse 2s infinite; }
.node-pending { background: #d97706; }
.node-rejected { background: #dc2626; }
@keyframes pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(37,99,235,0.4); } 50% { box-shadow: 0 0 0 6px rgba(37,99,235,0.1); } }

.timeline-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; transition: all 0.3s ease; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.timeline-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-1px); }
.timeline-card.active-card { border-left: 3px solid #2563eb; background: linear-gradient(135deg, #eff6ff, #fff); }

.timeline-card-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap; gap: 8px; }
.office-info { display: flex; align-items: center; gap: 8px; }
.office-info i { font-size: 1rem; color: #2d6a4f; }
.office-name { font-size: 0.85rem; font-weight: 700; color: #1e293b; line-height: 1.2; }

.route-status-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 0.65rem; font-weight: 700; padding: 3px 10px; border-radius: 16px; text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap; }
.badge-completed { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }
.badge-active { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
.badge-pending { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.badge-rejected { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

.timeline-card-body { padding: 12px 14px; }
.info-grid-enhanced { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
.info-card { background: #fff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; }
.received-card { border-left: 3px solid #3b82f6; }
.completed-card { border-left: 3px solid #10b981; }

.info-card-header { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
.info-card-icon { width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: #fff; flex-shrink: 0; }
.completed-icon { background: linear-gradient(135deg, #10b981, #059669); }
.info-card-title { font-weight: 700; font-size: 0.65rem; color: #1e293b; text-transform: uppercase; letter-spacing: 0.3px; flex: 1; }
.info-card-status { font-size: 0.55rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; }
.completed-status { background: #d1fae5; color: #059669; }

.info-card-body { padding: 8px 12px; display: flex; flex-direction: column; gap: 6px; }
.info-field { display: flex; gap: 8px; align-items: flex-start; }
.field-icon { width: 20px; height: 20px; min-width: 20px; border-radius: 4px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #64748b; }
.remarks-icon { background: #fffbeb; color: #d97706; }
.notes-icon { background: #f0fdf4; color: #059669; }
.field-content { flex: 1; min-width: 0; }
.field-label { display: block; font-size: 0.55rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 1px; }
.field-value { display: block; font-size: 0.75rem; color: #1e293b; font-weight: 500; word-break: break-word; line-height: 1.3; }
.received-date { color: #2563eb; font-weight: 600; }
.completed-date { color: #059669; font-weight: 600; }
.remarks-text { font-style: italic; color: #78350f; background: #fffbeb; padding: 2px 8px; border-radius: 4px; border-left: 2px solid #f59e0b; font-size: 0.7rem; }
.notes-text { background: #f0fdf4; padding: 2px 8px; border-radius: 4px; border-left: 2px solid #10b981; font-size: 0.7rem; }
.text-muted { color: #94a3b8; }

/* ===== FLOW SECTION ===== */
.flow-section { margin-top: 6px; padding: 10px 14px; background: linear-gradient(135deg, #f8fafc, #f1f5f9); border-radius: 8px; border: 1px solid #e5e7eb; }
.flow-container { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.flow-node { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 120px; padding: 6px 10px; background: #fff; border-radius: 6px; border: 1px solid #e5e7eb; }
.origin-node { border-left: 3px solid #3b82f6; }
.destination-node { border-left: 3px solid #10b981; }
.flow-node-icon { width: 24px; height: 24px; min-width: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #fff; }
.origin-node .flow-node-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.destination-node .flow-node-icon { background: linear-gradient(135deg, #10b981, #059669); }
.flow-node-content { flex: 1; min-width: 0; }
.flow-node-label { display: block; font-size: 0.5rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }
.flow-node-value { display: block; font-size: 0.75rem; font-weight: 600; color: #1e293b; word-break: break-word; }
.flow-arrow { display: flex; align-items: center; gap: 2px; flex-shrink: 0; padding: 0 2px; }
.arrow-line { width: 16px; height: 2px; background: linear-gradient(90deg, #94a3b8, #cbd5e1); border-radius: 1px; }
.arrow-icon { font-size: 1rem; color: #94a3b8; display: flex; align-items: center; justify-content: center; animation: arrowPulse 1.5s ease-in-out infinite; }
@keyframes arrowPulse { 0%, 100% { transform: translateX(0); opacity: 1; } 50% { transform: translateX(3px); opacity: 0.6; } }

/* ===== LOADER ===== */
.loader-spinner { width: 30px; height: 30px; border: 2px solid #e5e7eb; border-top-color: #2d6a4f; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 6px; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state { padding: 16px; text-align: center; }

/* ===== DOCUMENT HEADER ===== */
.document-header { padding: 12px 20px; }
.document-icon { background: rgba(220,38,38,0.2); }
.header-actions { display: flex; align-items: center; gap: 6px; }
.tracking-badge { background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px; font-family: "Courier New", monospace; font-size: 0.7rem; margin-right: 6px; }
.status-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); }

/* ===== CONFIDENTIAL NOTICE ===== */
.confidential-notice { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 500px; padding: 30px; background: linear-gradient(135deg, #1a1a2e, #16213e 50%, #0f3460); text-align: center; }
.confidential-icon-wrapper { position: relative; width: 90px; height: 90px; margin-bottom: 16px; display: flex; align-items: center; justify-content: center; }
.confidential-icon { width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #dc2626, #991b1b); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #fff; z-index: 2; box-shadow: 0 0 30px rgba(220,38,38,0.4); }
.confidential-ring { position: absolute; width: 90px; height: 90px; border-radius: 50%; border: 2px solid rgba(220,38,38,0.3); animation: ringPulse 2s ease-in-out infinite; }
@keyframes ringPulse { 0%, 100% { transform: scale(1); opacity: 0.3; } 50% { transform: scale(1.1); opacity: 0.6; } }
.confidential-title { font-size: 1.4rem; font-weight: 800; color: #dc2626; letter-spacing: 2px; margin-bottom: 6px; text-shadow: 0 0 20px rgba(220,38,38,0.3); }
.confidential-divider { width: 60px; height: 2px; background: linear-gradient(90deg, transparent, #dc2626, transparent); margin-bottom: 12px; }
.confidential-message { color: #e2e8f0; font-size: 0.9rem; margin-bottom: 6px; }
.confidential-sub-message { color: #94a3b8; font-size: 0.8rem; margin-bottom: 16px; max-width: 350px; }
.confidential-details { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; padding: 12px 16px; background: rgba(255,255,255,0.05); border-radius: 6px; border: 1px solid rgba(255,255,255,0.1); }
.confidential-detail-item { display: flex; align-items: center; gap: 8px; color: #cbd5e1; font-size: 0.75rem; font-family: "Courier New", monospace; }
.confidential-detail-item i { color: #dc2626; font-size: 0.85rem; }
.confidential-footer-note { display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.2); border-radius: 4px; color: #fca5a5; font-size: 0.65rem; max-width: 400px; }

/* ===== STATUS BADGE ===== */
.status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.status-in-progress { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.status-for-release { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }
.status-released { background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; }

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) { 
  .document-view-modal { max-width: 100vw; width: 100vw; }
  .document-viewer-layout { grid-template-columns: 260px 1fr; } 
}
@media (max-width: 1024px) {
  .forward-main-grid {
    grid-template-columns: 1fr 1fr;
  }
  .details-col {
    grid-column: span 2;
  }
}
@media (max-width: 992px) {
  .document-viewer-layout { grid-template-columns: 1fr; grid-template-rows: auto 1fr; height: calc(95vh - 140px); }
  .details-panel { border-right: none; border-bottom: 1px solid #e5e7eb; max-height: 300px; }
  .pdf-viewer-wrapper { min-height: 500px; }
  .info-grid-enhanced { grid-template-columns: 1fr; }
  .flow-container { flex-direction: column; gap: 6px; }
  .flow-node { width: 100%; min-width: unset; }
  .flow-arrow { transform: rotate(90deg); padding: 0; }
  .arrow-line { width: 24px; }
}
@media (max-width: 768px) {
  .forward-modal-large { max-width: 100vw; width: 100vw; margin: 0; border-radius: 0; max-height: 100vh; }
  .forward-modal-body { padding: 12px 16px; max-height: calc(100vh - 70px); }
  .forward-main-grid {
    grid-template-columns: 1fr;
  }
  .details-col {
    grid-column: span 1;
  }
  .forward-doc-info-bar { flex-direction: column; align-items: flex-start; gap: 4px; }
  .doc-info-divider { display: none; }
  .doc-info-subject { width: 100%; min-width: unset; }
  .subject-truncate { max-width: 100%; }
  .office-list, .memo-list { max-height: 200px; }
  .modal-actions { flex-direction: column; gap: 8px; align-items: stretch; }
  .modal-actions .d-flex { justify-content: center; }
  .square-btn { width: 100%; justify-content: center; }
  .col-header { padding: 8px 12px; }
  .office-item, .memo-item { padding: 4px 10px; }
  
  .document-view-modal { max-width: 100vw; width: 100vw; margin: 0; border-radius: 0; max-height: 100vh; }
  .document-viewer-tabs { padding: 6px 10px; gap: 3px; }
  .viewer-tab-btn { padding: 6px 12px; font-size: 11px; }
  .details-panel { max-height: 250px; }
  .pdf-viewer-wrapper { min-height: 400px; }
  .pdf-state { min-height: 400px; }
  .confidential-notice { min-height: 400px; }
  .route-history-content { padding: 12px; }
  .timeline-container { padding-left: 30px; }
  .timeline-node { left: -26px; width: 20px; height: 20px; font-size: 0.55rem; }
  .info-card-body { padding: 6px 10px; gap: 4px; }
  .info-field { flex-direction: column; gap: 2px; }
  .field-icon { width: 20px; height: 20px; min-width: 20px; font-size: 0.65rem; }
  .flow-node { padding: 4px 8px; }
  .flow-node-value { font-size: 0.7rem; }
  .flow-section { padding: 8px 10px; }
  .confidential-notice { padding: 20px; }
  .confidential-title { font-size: 1.2rem; }
}
@media (max-width: 576px) {
  .forward-header { padding: 10px 14px; }
  .forward-header .modal-title { font-size: 0.9rem; }
  .forward-header .modal-subtitle { font-size: 0.65rem; }
  .forward-doc-info-bar { padding: 8px 12px; }
  .doc-info-item { font-size: 0.7rem; }
  .office-search-box { padding: 6px 10px; }
  .office-search-input { font-size: 0.7rem; padding: 4px 24px 4px 24px; }
  .selected-tags { max-height: 60px; }
  .selected-tag { font-size: 0.6rem; }
  .dropzone-compact-content { gap: 8px; }
  .dropzone-compact-content i { font-size: 1.2rem; }
  .dropzone-title { font-size: 0.65rem; }
  .file-card-compact { padding: 6px 10px; }
  .file-card-name { font-size: 0.6rem; }
  .memo-actions { padding: 4px 10px; }
  .memo-action-btn { font-size: 0.6rem; padding: 2px 8px; }
  .detail-section { padding: 8px 10px; }
  .remarks-textarea { min-height: 40px; font-size: 0.7rem; }
  
  .document-header { padding: 10px 14px; }
  .viewer-tab-btn span { display: none; }
  .viewer-tab-btn i { font-size: 14px; }
  .detail-card { padding: 8px; }
  .detail-icon-wrapper { width: 28px; height: 28px; min-width: 28px; font-size: 0.8rem; }
  .btn-pdf-control { width: 26px; height: 26px; font-size: 0.7rem; }
  .pdf-footer { flex-direction: column; gap: 2px; align-items: flex-start; }
  .route-history-header { padding: 10px 14px; flex-direction: column; gap: 6px; align-items: flex-start; }
  .active-filters { flex-direction: column; align-items: flex-start; }
  .action-buttons { gap: 4px; }
  .btn-action { padding: 4px 8px; font-size: 0.8rem; }
}
.d-none { display: none !important; }
/* ===== PROGRESS NOTES PANEL ===== */
.progress-notes-panel {
  display: flex;
  flex-direction: column;
  height: calc(90vh - 140px);
  background: #ffffff;
  min-height: 500px;
}

.progress-notes-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  flex-shrink: 0;
}

.progress-notes-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  color: #1e293b;
  font-size: 1rem;
}

.progress-notes-title i {
  color: #7c3aed;
  font-size: 1.2rem;
}

.progress-notes-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: #ede9fe;
  border: 1px solid #c4b5fd;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #5b21b6;
}

.progress-notes-content {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
  background: #f9fafb;
}

.progress-notes-content::-webkit-scrollbar {
  width: 8px;
}

.progress-notes-content::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.progress-notes-content::-webkit-scrollbar-thumb {
  background: #94a3b8;
  border-radius: 4px;
}

.progress-section {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  padding: 16px 20px;
  margin-bottom: 20px;
}

.progress-section:last-child {
  margin-bottom: 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 2px solid #f3f4f6;
}

.section-header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  color: #1e293b;
  font-size: 0.9rem;
}

.section-header-left i {
  font-size: 1rem;
  color: #7c3aed;
}

.section-badge {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 10px;
  background: #f3f4f6;
  color: #6b7280;
  border-radius: 12px;
}

.empty-state-compact {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 20px;
  text-align: center;
  color: #94a3b8;
}

.empty-state-compact i {
  font-size: 2rem;
  margin-bottom: 8px;
  color: #d1d5db;
}

.empty-state-compact p {
  font-size: 0.85rem;
  margin: 0;
}

/* ===== MEMO SLIPS GRID ===== */
.memo-slips-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 10px;
}

.memo-slip-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.memo-slip-card:hover {
  border-color: #f59e0b;
  background: #fef3c7;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);
}

.memo-slip-number {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  min-width: 24px;
  border-radius: 50%;
  background: #f59e0b;
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 700;
}

.memo-slip-content {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  color: #78350f;
}

.memo-slip-content i {
  color: #d97706;
  font-size: 0.85rem;
}

/* ===== REMARKS CARD ===== */
.remarks-card {
  background: #f0fdf4;
  border: 1px solid #86efac;
  border-radius: 8px;
  padding: 16px;
}

.remarks-content {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.remarks-content i {
  font-size: 1.2rem;
  color: #16a34a;
  flex-shrink: 0;
  margin-top: 2px;
}

.remarks-content p {
  margin: 0;
  font-size: 0.9rem;
  color: #1e293b;
  line-height: 1.6;
  font-style: italic;
}

.remarks-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #bbf7d0;
  font-size: 0.7rem;
  color: #6b7280;
}

.remarks-meta i {
  font-size: 0.7rem;
  color: #16a34a;
}

</style>