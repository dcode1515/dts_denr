<template>
  <div>
    <!-- Loading Overlay -->
    <transition name="fade">
      <div v-if="isLoading" class="loading-overlay">
        <div class="loading-content">
          <div class="loading-spinner">
            <div class="spinner-circle"></div>
            <div class="spinner-logo">
              <img :src="getImageUrlLogo()" alt="Logo" style="width: 50px" />
            </div>
          </div>
          <h4 class="loading-title">Processing Your Document</h4>
          <p class="loading-message">{{ loadingMessage }}</p>
          <div class="loading-progress">
            <div class="loading-bar"></div>
          </div>
          <p class="loading-hint">
            Please wait while we generate your tracking number...
          </p>
        </div>
      </div>
    </transition>

    <!-- Success Modal -->
    <transition name="fade">
      <div
        v-if="showSuccessModal"
        class="modal-overlay"
        @click.self="closeSuccessModal"
      >
        <div class="success-modal-box">
          <div class="success-modal-content text-center">
            <div class="success-icon-wrapper">
              <div class="success-icon-circle">
                <i class="bi bi-check-circle-fill"></i>
              </div>
            </div>
            <h3 class="success-title">Document Submitted!</h3>
            <p class="success-message">
              Your document has been updated successfully.
            </p>

            <div class="tracking-number-box">
              <p class="tracking-label">YOUR TRACKING NUMBER</p>
              <div class="tracking-number-display">
                <span class="tracking-number-text">{{
                  generatedTrackingNumber ||
                  (incomingData && incomingData.tracking_number)
                }}</span>
                <button
                  class="copy-btn"
                  @click="copyTrackingNumber"
                  title="Copy tracking number"
                >
                  <i :class="copied ? 'bi bi-check2' : 'bi bi-clipboard'"></i>
                </button>
              </div>
            </div>

            <div class="success-actions">
              <button class="btn-nav btn-next" @click="closeSuccessModal">
                <i class="bi bi-check2 me-1"></i> Done
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Wizard Form Card -->
    <div class="row">
      <div class="col-lg-10 mx-auto">
        <div class="wizard-card">
          <div class="wizard-body">
            <!-- Progress -->
            <div class="progress-wrapper">
              <div class="progress-track"></div>
              <div
                class="progress-fill"
                :style="{ width: progressWidth + '%' }"
              ></div>
              <div class="step-list">
                <div
                  v-for="(step, index) in steps"
                  :key="index"
                  class="step-node"
                  :class="{
                    active: currentStep === step.id,
                    completed: completedSteps.includes(step.id),
                  }"
                  @click="goToStep(step.id)"
                >
                  <div class="step-dot">
                    <i
                      v-if="completedSteps.includes(step.id)"
                      class="bi bi-check-lg"
                    ></i>
                    <span v-else>{{ step.id }}</span>
                  </div>
                  <span class="step-text">{{ step.label }}</span>
                </div>
              </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" enctype="multipart/form-data">
              <!-- Step 1 - Document Information -->
              <div
                class="step-panel"
                :class="{
                  'd-none': currentStep !== 1,
                  'animate-in': currentStep === 1,
                }"
              >
                <div class="row justify-content-center">
                  <div class="col-md-10">
                    <div class="section-head">
                      <div class="section-icon">
                        <i class="bi bi-file-earmark-text"></i>
                      </div>
                      <div>
                        <h6>Document Information</h6>
                        <p>Provide the basic information for this document.</p>
                      </div>
                    </div>

                    <div class="row g-3">
                      <!-- Tracking Number Display from Props -->
                      <div
                        class="col-md-12"
                        v-if="incomingData && incomingData.tracking_number"
                      >
                        <div class="field-group">
                          <label class="field-label">Tracking Number</label>
                          <div class="input-wrap">
                            <i class="bi bi-upc-scan"></i>
                            <input
                              type="text"
                              :value="incomingData.tracking_number"
                              class="form-input"
                              readonly
                              style="background: #f8fafc; cursor: default"
                            />
                          </div>
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="field-group">
                          <label class="field-label"
                            >Document Type <span class="req">*</span></label
                          >
                          <div class="input-wrap">
                            <i class="bi bi-list-check"></i>
                            <select
                              v-model="formData.document_type"
                              @change="validateField('document_type')"
                              class="form-input"
                              :class="{ 'input-error': errors.document_type }"
                            >
                              <option value="">Select Document Type</option>
                              <option
                                v-for="docType in documentTypes"
                                :key="docType.id"
                                :value="docType.id"
                              >
                                {{ docType.document_type_name || "Unnamed" }}
                              </option>
                            </select>
                          </div>
                          <div class="field-error" v-if="errors.document_type">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ errors.document_type }}
                          </div>
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="field-group">
                          <label class="field-label"
                            >Document Classification
                            <span class="req">*</span></label
                          >
                          <div class="classification-radio-group">
                            <label
                              class="classification-radio-item"
                              :class="{
                                selected:
                                  formData.document_classification ===
                                  'General',
                                'input-error':
                                  errors.document_classification &&
                                  !formData.document_classification,
                              }"
                            >
                              <input
                                type="radio"
                                value="General"
                                v-model="formData.document_classification"
                                @change="
                                  validateField('document_classification')
                                "
                                class="classification-radio"
                              />
                              <div class="radio-card-content">
                                <div class="radio-icon general-icon">
                                  <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <div class="radio-text">
                                  <span class="radio-label">General</span>
                                  <span class="radio-description"
                                    >Standard document</span
                                  >
                                </div>
                              </div>
                            </label>

                            <label
                              class="classification-radio-item"
                              :class="{
                                selected:
                                  formData.document_classification ===
                                  'Confidential',
                                'input-error':
                                  errors.document_classification &&
                                  !formData.document_classification,
                              }"
                            >
                              <input
                                type="radio"
                                value="Confidential"
                                v-model="formData.document_classification"
                                @change="
                                  validateField('document_classification')
                                "
                                class="classification-radio"
                              />
                              <div class="radio-card-content">
                                <div class="radio-icon confidential-icon">
                                  <i class="bi bi-shield-lock-fill"></i>
                                </div>
                                <div class="radio-text">
                                  <span class="radio-label">Confidential</span>
                                  <span class="radio-description"
                                    >Restricted access</span
                                  >
                                </div>
                              </div>
                            </label>
                          </div>
                          <div
                            class="field-error"
                            v-if="errors.document_classification"
                          >
                            <i class="bi bi-exclamation-circle"></i>
                            {{ errors.document_classification }}
                          </div>
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="field-group">
                          <label class="field-label"
                            >Sender Name <span class="req">*</span></label
                          >
                          <div class="input-wrap">
                            <i class="bi bi-person"></i>
                            <input
                              type="text"
                              v-model="formData.sender_name"
                              @blur="validateField('sender_name')"
                              class="form-input"
                              :class="{ 'input-error': errors.sender_name }"
                              placeholder="Enter sender's full name"
                            />
                          </div>
                          <div class="field-error" v-if="errors.sender_name">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ errors.sender_name }}
                          </div>
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="field-group">
                          <label class="field-label"
                            >Subject <span class="req">*</span></label
                          >
                          <div class="input-wrap input-wrap-textarea">
                            <i class="bi bi-journal-text"></i>
                            <textarea
                              v-model="formData.subject"
                              @blur="validateField('subject')"
                              rows="3"
                              class="form-input"
                              :class="{ 'input-error': errors.subject }"
                              placeholder="Enter document subject..."
                            ></textarea>
                          </div>
                          <div class="field-error" v-if="errors.subject">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ errors.subject }}
                          </div>
                        </div>
                      </div>

                      <!-- Date & Time Received -->
                      <div class="col-md-6">
                        <div class="field-group">
                          <label class="field-label"
                            >Date Received <span class="req">*</span></label
                          >
                          <div class="input-wrap">
                            <i class="bi bi-calendar-date"></i>
                            <input
                              type="date"
                              v-model="formData.date_received"
                              @change="validateField('date_received')"
                              class="form-input"
                              :class="{ 'input-error': errors.date_received }"
                            />
                          </div>
                          <div class="field-error" v-if="errors.date_received">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ errors.date_received }}
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="field-group">
                          <label class="field-label"
                            >Time Received <span class="req">*</span></label
                          >
                          <div class="input-wrap">
                            <i class="bi bi-clock"></i>
                            <input
                              type="time"
                              v-model="formData.time_received"
                              @change="validateField('time_received')"
                              class="form-input"
                              :class="{ 'input-error': errors.time_received }"
                            />
                          </div>
                          <div class="field-error" v-if="errors.time_received">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ errors.time_received }}
                          </div>
                        </div>
                      </div>

                      <div class="col-12">
                        <label class="field-label">
                          Attachment 
                          <span v-if="!hasExistingFile && !uploadedFile" class="req">*</span>
                        </label>

                        <!-- Display existing file from server (when no new file uploaded) -->
                        <div v-if="hasExistingFile && !uploadedFile" class="single-file-card mb-3">
                          <div class="file-info">
                            <div
                              class="file-icon-wrap"
                              :class="getFileColorClass(existingFileName)"
                            >
                              <i :class="getFileIcon(existingFileName)"></i>
                            </div>
                            <div class="file-details">
                              <div class="file-name">
                                {{ existingFileName }}
                              </div>
                              <div class="file-size text-success">
                                Current file
                              </div>
                            </div>
                          </div>
                          <div class="file-actions">
                            <button
                              type="button"
                              class="btn-icon btn-icon-view"
                              @click="viewExistingFile"
                              title="View"
                            >
                              <i class="bi bi-eye"></i>
                            </button>
                            <button
                              type="button"
                              class="btn-icon btn-icon-replace"
                              @click="prepareForNewUpload"
                              title="Replace"
                            >
                              <i class="bi bi-arrow-repeat"></i>
                            </button>
                          </div>
                        </div>

                        <!-- Upload dropzone - show when clicking replace or no existing file -->
                        <div
                          v-if="showUploadDropzone"
                          class="dropzone"
                          @click="triggerFileUpload"
                          @dragover.prevent
                          @drop.prevent="handleDrop"
                        >
                          <input
                            type="file"
                            ref="fileInput"
                            class="d-none"
                            accept=".pdf,.jpg,.jpeg,.png,.docx"
                            @change="handleFileUpload"
                          />
                          <div class="dropzone-content">
                            <div class="dropzone-icon">
                              <i class="bi bi-cloud-arrow-up"></i>
                            </div>
                            <h6 class="fw-semibold mb-1">
                              Click to upload or drag file here
                            </h6>
                            <p class="text-muted small mb-0">
                              PDF, JPG, PNG, DOCX (Max 5MB)
                            </p>
                          </div>
                          <!-- Cancel upload button -->
                          <button
                            v-if="hasExistingFileBackup"
                            type="button"
                            class="btn btn-sm btn-outline-secondary mt-3"
                            @click.stop="cancelNewUpload"
                          >
                            <i class="bi bi-x-circle me-1"></i> Cancel and keep existing file
                          </button>
                        </div>

                        <!-- Newly uploaded file card -->
                        <div v-if="uploadedFile" class="single-file-card">
                          <div class="file-info">
                            <div
                              class="file-icon-wrap"
                              :class="getFileColorClass(uploadedFile.name)"
                            >
                              <i :class="getFileIcon(uploadedFile.name)"></i>
                            </div>
                            <div class="file-details">
                              <div class="file-name">
                                {{ uploadedFile.name }}
                              </div>
                              <div class="file-size">
                                {{ formatFileSize(uploadedFile.size) }}
                              </div>
                            </div>
                          </div>
                          <div class="file-actions">
                            <button
                              type="button"
                              class="btn-icon btn-icon-view"
                              @click="openFileModal"
                              title="View"
                            >
                              <i class="bi bi-eye"></i>
                            </button>
                            <button
                              type="button"
                              class="btn-icon btn-icon-replace"
                              @click="triggerFileUpload"
                              title="Replace"
                            >
                              <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button
                              type="button"
                              class="btn-icon btn-icon-remove"
                              @click="removeUploadedFile"
                              title="Remove"
                            >
                              <i class="bi bi-x-lg"></i>
                            </button>
                          </div>
                        </div>

                        <div class="field-error" v-if="errors.attachment">
                          <i class="bi bi-exclamation-circle"></i>
                          {{ errors.attachment }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 2 - Review & Confirm -->
              <div
                class="step-panel"
                :class="{
                  'd-none': currentStep !== 2,
                  'animate-in': currentStep === 2,
                }"
              >
                <div class="section-head">
                  <div class="section-icon">
                    <i class="bi bi-clipboard-check"></i>
                  </div>
                  <div>
                    <h6>Review & Confirm</h6>
                    <p>
                      Please verify all information below before submission.
                    </p>
                  </div>
                </div>

                <div class="review-card">
                  <!-- Tracking Number Banner -->
                  <div
                    class="review-tracking-highlight"
                    v-if="incomingData && incomingData.tracking_number"
                  >
                    <div
                      class="d-flex align-items-center justify-content-between"
                    >
                      <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-upc-scan"></i>
                        <span class="review-tracking-label"
                          >Tracking Number</span
                        >
                      </div>
                      <span class="review-tracking-number">{{
                        incomingData.tracking_number
                      }}</span>
                    </div>
                  </div>

                  <!-- Group 1: Document Identity -->
                  <div class="review-group">
                    <div class="review-group-title">
                      <i class="bi bi-tag-fill"></i> Document Details
                    </div>
                    <div class="row g-3">
                      <div class="col-md-7">
                        <div class="review-item">
                          <div class="review-label">Document Type</div>
                          <div class="review-value review-highlight">
                            {{ getSelectedDocumentTypeName() || "—" }}
                          </div>
                        </div>
                      </div>
                      <div class="col-md-5">
                        <div class="review-item">
                          <div class="review-label">Classification</div>
                          <div class="review-value">
                            <span
                              v-if="formData.document_classification"
                              :class="
                                formData.document_classification ===
                                'Confidential'
                                  ? 'badge-confidential'
                                  : 'badge-general'
                              "
                            >
                              <i
                                :class="
                                  formData.document_classification ===
                                  'Confidential'
                                    ? 'bi bi-shield-lock-fill'
                                    : 'bi bi-file-earmark-text'
                                "
                                class="me-1"
                              ></i>
                              {{
                                capitalizeFirst(
                                  formData.document_classification
                                )
                              }}
                            </span>
                            <span v-else class="text-muted">—</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Group 2: Sender & Content -->
                  <div class="review-group">
                    <div class="review-group-title">
                      <i class="bi bi-person-vcard"></i> Sender & Content
                    </div>
                    <div class="row g-3">
                      <div class="col-md-4">
                        <div class="review-item">
                          <div class="review-label">Sender Name</div>
                          <div class="review-value review-bold">
                            {{ formData.sender_name || "—" }}
                          </div>
                        </div>
                      </div>
                      <div class="col-md-8">
                        <div class="review-item">
                          <div class="review-label">Subject</div>
                          <div class="review-value subject-text">
                            {{ formData.subject || "—" }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Group 3: Date, Time & Attachment -->
                  <div class="review-group border-bottom-0">
                    <div class="review-group-title">
                      <i class="bi bi-signpost-split-fill"></i> Receipt & Attachment
                    </div>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <div class="review-item">
                          <div class="review-label">Date & Time Received</div>
                          <div class="review-value review-bold">
                            <span
                              v-if="
                                formData.date_received && formData.time_received
                              "
                              class="datetime-badge"
                            >
                              <i class="bi bi-calendar-check me-1"></i>
                              {{ formatDisplayDate(formData.date_received) }}
                              <i class="bi bi-clock ms-2 me-1"></i>
                              {{ formatDisplayTime(formData.time_received) }}
                            </span>
                            <span v-else class="text-muted">—</span>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="review-item">
                          <div class="review-label">Attachment</div>
                          <div class="review-value">
                            <div v-if="uploadedFile" class="review-file-badge">
                              <div
                                class="file-icon-wrap-sm"
                                :class="getFileColorClass(uploadedFile.name)"
                              >
                                <i :class="getFileIcon(uploadedFile.name)"></i>
                              </div>
                              <div class="review-file-info">
                                <span class="file-name-sm">{{
                                  uploadedFile.name
                                }}</span>
                              </div>
                              <button
                                type="button"
                                class="btn-link-sm"
                                @click="openFileModal"
                              >
                                <i class="bi bi-eye me-1"></i>View
                              </button>
                            </div>
                            <div v-else-if="hasExistingFile" class="review-file-badge">
                              <div
                                class="file-icon-wrap-sm"
                                :class="getFileColorClass(existingFileName)"
                              >
                                <i :class="getFileIcon(existingFileName)"></i>
                              </div>
                              <div class="review-file-info">
                                <span class="file-name-sm">{{ existingFileName }}</span>
                              </div>
                              <span class="badge bg-success ms-2" style="font-size: 0.65rem;">Current</span>
                            </div>
                            <span v-else class="text-muted">—</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="alert-note">
                  <i class="bi bi-exclamation-triangle-fill"></i>
                  <span
                    >Once submitted, you cannot edit the document. Please ensure
                    all details are correct.</span
                  >
                </div>
              </div>

              <!-- Navigation -->
              <div class="wizard-nav">
                <button
                  type="button"
                  @click="prevStep"
                  class="btn-nav btn-prev"
                  :disabled="isLoading"
                  v-show="currentStep !== 1"
                >
                  <i class="bi bi-arrow-left"></i> Previous
                </button>
                <div class="nav-right">
                    <a :href="'/dts_denr/incoming-documents'" class="btn-nav btn-back">
                        <i class="bi bi-arrow-left"></i> Back
                        </a>
                  <button
                    type="button"
                    @click="nextStep"
                    class="btn-nav btn-next"
                    :disabled="isLoading"
                    v-show="currentStep !== 2"
                  >
                    Next Step <i class="bi bi-arrow-right"></i>
                  </button>
                  <button
                    type="submit"
                    class="btn-nav btn-submit"
                    :disabled="isLoading"
                    v-show="currentStep === 2"
                  >
                    <span v-if="!isLoading">
                      <i class="bi bi-check2-all"></i> Submit
                    </span>
                    <span v-if="isLoading">
                      <span
                        class="spinner-border spinner-border-sm me-2"
                      ></span>
                      Submitting...
                    </span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Attachment Preview Modal -->
    <div
      class="modal-overlay"
      v-if="showModal && !isLoading"
      @click.self="closeModal"
    >
      <transition name="modal">
        <div class="modal-box" v-if="showModal">
          <div class="modal-head">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-eye"></i>
              <h6 class="mb-0 fw-semibold">View Attachment</h6>
            </div>
            <button type="button" class="btn-close-modal" @click="closeModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body">
            <div class="preview-area" v-if="uploadedFile || modalFileUrl">
              <!-- PDF Preview -->
              <div
                v-if="isModalFilePDF()"
                class="preview-pdf-wrap"
              >
                <embed
                  :src="getModalFileUrl()"
                  type="application/pdf"
                  class="preview-pdf"
                />
                <div class="pdf-fallback">
                  <p class="text-muted small mb-2">
                    If PDF doesn't display, you can view it in a new tab.
                  </p>
                  <button
                    type="button"
                    class="btn-nav btn-next btn-sm"
                    @click="openInNewTab"
                  >
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open in New
                    Tab
                  </button>
                </div>
              </div>
              <!-- Image Preview -->
              <div
                v-else-if="isModalFileImage()"
                class="preview-image-wrap"
              >
                <img
                  :src="getModalFileUrl()"
                  :alt="getModalFileName()"
                  class="preview-image"
                />
              </div>
              <!-- DOCX Preview -->
              <div
                v-else-if="isModalFileDocx()"
                class="preview-docx-wrap"
              >
                <iframe
                  :src="getDocxViewerUrlForModal()"
                  class="preview-docx"
                  frameborder="0"
                ></iframe>
                <div class="docx-fallback">
                  <p class="text-muted small mb-2">
                    If document doesn't display properly, you can view it in a
                    new tab.
                  </p>
                  <button
                    type="button"
                    class="btn-nav btn-next btn-sm"
                    @click="openInNewTab"
                  >
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open in New
                    Tab
                  </button>
                </div>
              </div>
              <!-- Other file types -->
              <div v-else class="preview-placeholder">
                <div
                  class="placeholder-icon"
                  :class="getFileColorClass(getModalFileName())"
                >
                  <i :class="getFileIcon(getModalFileName())"></i>
                </div>
                <h5 class="fw-semibold mt-3 mb-1">{{ getModalFileName() }}</h5>
                <p class="text-muted small mt-2 mb-3">
                  Preview not available for this file type.
                </p>
                <button
                  type="button"
                  class="btn-nav btn-next btn-sm"
                  @click="openInNewTab"
                >
                  <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
                </button>
              </div>
            </div>
            <div v-else class="preview-empty">
              <i class="bi bi-folder2-open"></i>
              <p>No file selected</p>
            </div>

            <div v-if="uploadedFile" class="file-info-bar mt-3">
              <div class="d-flex align-items-center gap-2">
                <div
                  class="file-icon-wrap"
                  :class="getFileColorClass(uploadedFile.name)"
                >
                  <i :class="getFileIcon(uploadedFile.name)"></i>
                </div>
                <div>
                  <div class="file-name">{{ uploadedFile.name }}</div>
                  <div class="file-size">
                    {{ formatFileSize(uploadedFile.size) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-foot">
            <button type="button" class="btn-nav btn-prev" @click="closeModal">
              <i class="bi bi-x-lg me-1"></i> Close
            </button>
            <button
              type="button"
              v-if="uploadedFile || modalFileUrl"
              class="btn-nav btn-next btn-sm"
              @click="openInNewTab"
            >
              <i class="bi bi-box-arrow-up-right me-1"></i> Open in New Tab
            </button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" class="toast-notification" :class="toast.type">
        <i
          :class="
            toast.type === 'success'
              ? 'bi bi-check-circle-fill'
              : 'bi bi-exclamation-triangle-fill'
          "
        ></i>
        <span>{{ toast.message }}</span>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  props: {
    incoming: {
      type: [Object, String, Array],
      default: () => ({}),
    },
  },
  data() {
    return {
      documentTypes: [],
      currentStep: 1,
      completedSteps: [],
      steps: [
        { id: 1, label: "Info" },
        { id: 2, label: "Review" },
      ],
      formData: {
        document_type: "",
        document_classification: "",
        sender_name: "",
        subject: "",
        date_received: "",
        time_received: "",
        attachment: null,
      },
      uploadedFile: null,
      existingFileName: null,
      existingFilePath: null,
      hasExistingFileBackup: false, // Tracks if we had an existing file before showing upload
      showUploadDropzone: false, // Controls when to show the upload dropzone
      modalFileUrl: null,
      errors: {},
      validationRules: {
        document_type: { required: true },
        document_classification: { required: true },
        sender_name: { required: true, min: 2, max: 100 },
        subject: { required: true, min: 5 },
        date_received: { required: true },
        time_received: { required: true },
      },
      showModal: false,
      toast: { show: false, message: "", type: "success" },
      previewUrl: null,
      isLoading: false,
      loadingMessage: "",
      showSuccessModal: false,
      generatedTrackingNumber: "",
      copied: false,
    };
  },
  computed: {
    incomingData() {
      if (typeof this.incoming === "string") {
        try {
          return JSON.parse(this.incoming);
        } catch (e) {
          return {};
        }
      }
      return this.incoming || {};
    },
    progressWidth() {
      return ((this.currentStep - 1) / (this.steps.length - 1)) * 100;
    },
    hasExistingFile() {
      return !!this.existingFileName && !this.showUploadDropzone && !this.uploadedFile;
    },
  },
  methods: {
    getImageUrlLogo() {
      return "/dts_denr/storage/app/public/logo/denr1.png";
    },
    populateFormData() {
      const data = this.incomingData;
      if (!data || !data.id) return;

      // Clear any previous data to avoid conflicts
      this.clearAllData();

      // Populate form fields from incoming data
      this.formData.document_type = data.document_type_id || data.document_type || "";
      this.formData.document_classification = data.document_classification || "";
      this.formData.sender_name = data.sender_name || "";
      this.formData.subject = data.subject || "";
      this.formData.date_received = data.date_received || "";
      this.formData.time_received = data.time_received || "";

      // Handle existing attachment - build storage path
      if (data.draft_attachment) {
      
        if (typeof data.draft_attachment === 'string') {
          this.existingFileName = data.draft_attachment;
          this.existingFilePath = `/dts_denr/storage/app/public/${data.draft_attachment}`;
        } else if (typeof data.draft_attachment === 'object') {
          this.existingFileName = data.draft_attachment.file_name || data.draft_attachment.name;
          this.existingFilePath = data.draft_attachment.file_path || data.draft_attachment.path;
        }
        this.hasExistingFileBackup = true;
        this.showUploadDropzone = false;
      } else if (data.file_name) {
        this.existingFileName = data.file_name;
      
        this.existingFilePath = `/dts_denr/storage/app/public/${data.file_name}`;
        this.hasExistingFileBackup = true;
        this.showUploadDropzone = false;
      } else if (data.attachment) {
        this.existingFileName = data.attachment;
       
        this.existingFilePath = `/dts_denr/storage/app/public/${data.attachment}`;
        this.hasExistingFileBackup = true;
        this.showUploadDropzone = false;
      } else {
        this.hasExistingFileBackup = false;
        this.showUploadDropzone = true; // Show upload if no existing file
      }
    },
    clearAllData() {
      if (this.previewUrl) {
        URL.revokeObjectURL(this.previewUrl);
        this.previewUrl = null;
      }
      this.uploadedFile = null;
      this.existingFileName = null;
      this.existingFilePath = null;
      this.hasExistingFileBackup = false;
      this.showUploadDropzone = false;
      this.modalFileUrl = null;
      this.formData.attachment = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
    },
    prepareForNewUpload() {
      // Show upload dropzone but keep existing file data in backup
      this.showUploadDropzone = true;
      this.uploadedFile = null;
      this.formData.attachment = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
    },
    cancelNewUpload() {
      // Hide upload dropzone and restore existing file
      this.showUploadDropzone = false;
      this.uploadedFile = null;
      this.formData.attachment = null;
      this.modalFileUrl = null;
      if (this.previewUrl) {
        URL.revokeObjectURL(this.previewUrl);
        this.previewUrl = null;
      }
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
      this.showToast("Keeping existing file.", "success");
    },
    removeUploadedFile() {
      // Remove uploaded file and show existing if available
      if (this.previewUrl) {
        URL.revokeObjectURL(this.previewUrl);
        this.previewUrl = null;
      }
      this.uploadedFile = null;
      this.formData.attachment = null;
      this.modalFileUrl = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
      
      // If we had an existing file, show it again
      if (this.hasExistingFileBackup) {
        this.showUploadDropzone = false;
        this.showToast("Reverted to existing file.", "success");
      } else {
        this.showUploadDropzone = true;
        this.showToast("File removed.", "success");
      }
    },
    getModalFileName() {
      if (this.uploadedFile) return this.uploadedFile.name;
      if (this.existingFileName) return this.existingFileName;
      return '';
    },
    getModalFileUrl() {
      if (this.uploadedFile) {
        if (!this.previewUrl) {
          this.previewUrl = URL.createObjectURL(this.uploadedFile);
        }
        return this.previewUrl;
      }
      return this.modalFileUrl || this.existingFilePath || '';
    },
    isModalFilePDF() {
      const fileName = this.getModalFileName();
      const ext = fileName.split('.').pop().toLowerCase();
      if (ext === 'pdf') return true;
      if (this.uploadedFile && this.uploadedFile.type === 'application/pdf') return true;
      return false;
    },
    isModalFileImage() {
      const fileName = this.getModalFileName();
      const ext = fileName.split('.').pop().toLowerCase();
      return ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
    },
    isModalFileDocx() {
      const fileName = this.getModalFileName();
      const ext = fileName.split('.').pop().toLowerCase();
      return ext === 'docx' || ext === 'doc';
    },
    getDocxViewerUrlForModal() {
      const url = this.getModalFileUrl();
      return `https://docs.google.com/gview?url=${encodeURIComponent(url)}&embedded=true`;
    },
    getInitialDate() {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, "0");
      const day = String(now.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    getInitialTime() {
      const now = new Date();
      const hours = String(now.getHours()).padStart(2, "0");
      const minutes = String(now.getMinutes()).padStart(2, "0");
      return `${hours}:${minutes}`;
    },
    formatDisplayDate(dateStr) {
      if (!dateStr) return "—";
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(dateStr + "T00:00:00").toLocaleDateString(
        "en-US",
        options
      );
    },
    formatDisplayTime(timeStr) {
      if (!timeStr) return "—";
      const [hours, minutes] = timeStr.split(":");
      const date = new Date();
      date.setHours(hours, minutes);
      return date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
      });
    },
    capitalizeFirst(string) {
      if (!string) return "";
      return string.charAt(0).toUpperCase() + string.slice(1);
    },
    async fetchDocumentTypes() {
      try {
        const response = await axios.get("/dts_denr/api/get/document-type");
        let data = response.data;
        if (data.data && Array.isArray(data.data)) {
          data = data.data;
        } else if (!Array.isArray(data)) {
          data = [];
        }
        this.documentTypes = data;
      } catch (error) {
        console.error("Error fetching Document Types:", error);
        this.showToast(
          "Failed to load Document Types. Please refresh the page.",
          "error"
        );
        this.documentTypes = [];
      }
    },
    getSelectedDocumentTypeName() {
      const selected = this.documentTypes.find(
        (d) => d.id == this.formData.document_type
      );
      return selected ? selected.document_type_name || "Unnamed" : "—";
    },
    validateField(fieldName) {
      if (fieldName === "attachment") {
        if (!this.uploadedFile && !this.hasExistingFile && !this.hasExistingFileBackup) {
          this.errors.attachment = "Attachment is required.";
          return false;
        } else {
          delete this.errors.attachment;
          return true;
        }
      }

      const value = this.formData[fieldName];
      const rules = this.validationRules[fieldName];
      if (!rules) {
        delete this.errors[fieldName];
        return true;
      }
      let error = null;
      if (rules.required && (!value || value.toString().trim() === "")) {
        error = this.getFieldLabel(fieldName) + " is required.";
      } else if (value && rules.min && value.length < rules.min) {
        error =
          this.getFieldLabel(fieldName) +
          ` must be at least ${rules.min} characters.`;
      } else if (value && rules.max && value.length > rules.max) {
        error =
          this.getFieldLabel(fieldName) +
          ` must not exceed ${rules.max} characters.`;
      }
      if (error) {
        this.errors[fieldName] = error;
        return false;
      } else {
        delete this.errors[fieldName];
        return true;
      }
    },
    validateStep(step) {
      const map = {
        1: [
          "document_type",
          "document_classification",
          "sender_name",
          "subject",
          "date_received",
          "time_received",
        ],
      };
      let ok = true;
      (map[step] || []).forEach((f) => {
        if (!this.validateField(f)) ok = false;
      });
      return ok;
    },
    getFieldLabel(f) {
      return (
        {
          document_type: "Document Type",
          document_classification: "Document Classification",
          sender_name: "Sender Name",
          subject: "Subject",
          date_received: "Date Received",
          time_received: "Time Received",
          attachment: "Attachment",
        }[f] || f
      );
    },
    nextStep() {
      if (this.validateStep(this.currentStep)) {
        if (!this.completedSteps.includes(this.currentStep)) {
          this.completedSteps.push(this.currentStep);
        }
        if (this.currentStep < this.steps.length) {
          this.currentStep++;
        }
      } else {
        this.showToast(
          "Please fill in all required fields correctly.",
          "error"
        );
      }
    },
    prevStep() {
      if (this.currentStep > 1 && !this.isLoading) this.currentStep--;
    },
    goToStep(step) {
      if (this.isLoading) return;
      if (step < this.currentStep) {
        this.currentStep = step;
      } else if (step > this.currentStep) {
        let ok = true;
        for (let i = this.currentStep; i < step; i++) {
          if (!this.validateStep(i)) {
            ok = false;
            this.showToast(`Please complete Step ${i} first.`, "error");
            break;
          }
        }
        if (ok) this.currentStep = step;
      }
    },
    async handleSubmit() {
      if (this.validateStep(1)) {
        try {
          this.isLoading = true;
          this.loadingMessage = "Updating your document...";

          const formData = new FormData();

          formData.append("_method", "PUT");

          formData.append("document_type", this.formData.document_type);
          formData.append(
            "document_classification",
            this.formData.document_classification
          );
          formData.append("sender_name", this.formData.sender_name);
          formData.append("subject", this.formData.subject);
          formData.append("date_received", this.formData.date_received);
          formData.append("time_received", this.formData.time_received);

          // Handle file attachment - send new file if uploaded
          if (this.uploadedFile) {
            formData.append("draft_attachment", this.uploadedFile);
          }

          const documentId = this.incomingData.id;

          const [response] = await Promise.all([
            axios.post(
              `/dts_denr/api/update-document/${documentId}`,
              formData,
              {
                headers: {
                  "Content-Type": "multipart/form-data",
                },
              }
            ),
            new Promise((resolve) => setTimeout(resolve, 3000)),
          ]);

          this.isLoading = false;

          if (response.data.success) {
            this.generatedTrackingNumber = response.data.data.tracking_number;
            this.showSuccessModal = true;
            document.body.style.overflow = "hidden";
          } else {
            this.showToast(
              response.data.message || "Failed to update document.",
              "error"
            );
          }
        } catch (error) {
          this.isLoading = false;

          if (error.response) {
            if (error.response.data.errors) {
              const errors = error.response.data.errors;
              const firstError = Object.values(errors)[0];
              this.showToast(
                Array.isArray(firstError) ? firstError[0] : firstError,
                "error"
              );
            } else {
              this.showToast(
                error.response.data.message || "Server error occurred.",
                "error"
              );
            }
          } else if (error.request) {
            this.showToast(
              "Network error. Please check your connection.",
              "error"
            );
          } else {
            this.showToast("An unexpected error occurred.", "error");
          }
          console.error("Submission error:", error);
        }
      } else {
        this.showToast("Please complete all steps correctly.", "error");
        this.currentStep = 1;
      }
    },
    copyTrackingNumber() {
      const numberToCopy =
        this.generatedTrackingNumber ||
        (this.incomingData && this.incomingData.tracking_number);
      if (numberToCopy) {
        navigator.clipboard
          .writeText(numberToCopy)
          .then(() => {
            this.copied = true;
            setTimeout(() => {
              this.copied = false;
            }, 2000);
          })
          .catch(() => {
            this.showToast("Failed to copy tracking number", "error");
          });
      }
    },
    closeSuccessModal() {
      this.showSuccessModal = false;
      document.body.style.overflow = "";
      this.showToast("Document updated successfully!", "success");

      setTimeout(() => {
        window.location.href = "/dts_denr/incoming-documents";
      }, 100);
    },

    viewExistingFile() {
      if (this.existingFilePath) {
        this.modalFileUrl = this.existingFilePath;
        this.showModal = true;
        document.body.style.overflow = "hidden";
      }
    },
    triggerFileUpload() {
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
      this.$refs.fileInput.click();
    },
    handleFileUpload(e) {
      const files = Array.from(e.target.files);
      if (files.length > 0) {
        this.processSingleFile(files[0]);
      }
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
    },
    handleDrop(e) {
      const files = Array.from(e.dataTransfer.files);
      if (files.length > 0) {
        this.processSingleFile(files[0]);
      }
    },
    processSingleFile(file) {
      const validTypes = ["application/pdf"];
      const fileExt = file.name.split(".").pop().toLowerCase();

      if (!validTypes.includes(file.type) && fileExt !== "pdf") {
        this.showToast(
          "Invalid file type. Only PDF files are allowed.",
          "error"
        );
        return;
      }

      const maxSize = 50 * 1024 * 1024 * 1024;
      if (file.size > maxSize) {
        this.showToast("File size exceeds 50GB limit.", "error");
        return;
      }

      if (this.previewUrl) {
        URL.revokeObjectURL(this.previewUrl);
        this.previewUrl = null;
      }

      // Set new file
      this.uploadedFile = file;
      this.formData.attachment = file;
      this.showUploadDropzone = false;
      delete this.errors.attachment;
      this.showToast("PDF uploaded successfully!", "success");
    },
    getFileIcon(n) {
      const ext = n.split(".").pop().toLowerCase();
      if (ext === "pdf") return "bi bi-file-earmark-pdf";
      if (["jpg", "jpeg", "png"].includes(ext))
        return "bi bi-file-earmark-image";
      if (ext === "docx") return "bi bi-file-earmark-word";
      return "bi bi-file-earmark";
    },
    getFileColorClass(n) {
      const ext = n.split(".").pop().toLowerCase();
      if (ext === "pdf") return "color-pdf";
      if (["jpg", "jpeg", "png"].includes(ext)) return "color-img";
      if (ext === "docx") return "color-doc";
      return "color-default";
    },
    isImageFile(n) {
      return ["jpg", "jpeg", "png"].includes(n.split(".").pop().toLowerCase());
    },
    getImageUrl(f) {
      if (!this.previewUrl) {
        this.previewUrl = URL.createObjectURL(f);
      }
      return this.previewUrl;
    },
    getDocxViewerUrl(f) {
      const fileUrl = this.getImageUrl(f);
      return `https://docs.google.com/gview?url=${encodeURIComponent(
        fileUrl
      )}&embedded=true`;
    },
    openInNewTab() {
      if (this.uploadedFile) {
        const url = this.getImageUrl(this.uploadedFile);
        window.open(url, "_blank");
      } else if (this.modalFileUrl) {
        window.open(this.modalFileUrl, "_blank");
      }
    },
    formatFileSize(b) {
      if (b < 1024) return b + " B";
      if (b < 1048576) return (b / 1024).toFixed(1) + " KB";
      return (b / 1048576).toFixed(2) + " MB";
    },
    openFileModal() {
      if (!this.isLoading) {
        if (this.uploadedFile) {
          this.modalFileUrl = null;
        }
        this.showModal = true;
        document.body.style.overflow = "hidden";
      }
    },
    closeModal() {
      this.showModal = false;
      this.modalFileUrl = null;
      document.body.style.overflow = "";
    },
    showToast(msg, type = "success") {
      this.toast = { show: true, message: msg, type };
      setTimeout(() => {
        this.toast.show = false;
      }, 3500);
    },
  },
  mounted() {
    this.clearAllData();
    this.populateFormData();
    
    if (!this.formData.date_received) {
      this.formData.date_received = this.getInitialDate();
    }
    if (!this.formData.time_received) {
      this.formData.time_received = this.getInitialTime();
    }

    this.fetchDocumentTypes();

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        if (this.showModal) this.closeModal();
        if (this.showSuccessModal) this.closeSuccessModal();
      }
    });
  },
  beforeDestroy() {
    if (this.previewUrl) {
      URL.revokeObjectURL(this.previewUrl);
    }
    document.removeEventListener("keydown", () => {});
  },
  watch: {
    incoming: {
      handler(newVal) {
        if (newVal) {
          this.clearAllData();
          this.populateFormData();
          
          if (!this.formData.date_received) {
            this.formData.date_received = this.getInitialDate();
          }
          if (!this.formData.time_received) {
            this.formData.time_received = this.getInitialTime();
          }
          
          this.errors = {};
          this.completedSteps = [];
          this.currentStep = 1;
        }
      },
      deep: true
    }
  }
};
</script>




<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap");

/* Wizard Styles */
.wizard-card {
  background: #fff;
  border-radius: 1.125rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 8px 24px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  border: 1px solid rgba(226, 232, 240, 0.7);
}

.wizard-body {
  padding: 1.75rem 1.75rem 1.25rem;
}

.progress-wrapper {
  position: relative;
  margin: 1.75rem 0 2.25rem;
}

.progress-track {
  position: absolute;
  top: 17px;
  left: 48px;
  right: 48px;
  height: 3px;
  background: #e2e8f0;
  border-radius: 2px;
}

.progress-fill {
  position: absolute;
  top: 17px;
  left: 48px;
  height: 3px;
  background: linear-gradient(90deg, #0f766e, #14b8a6);
  border-radius: 2px;
  transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.step-list {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: space-around;
  max-width: 400px;
  margin: 0 auto;
}

.step-node {
  text-align: center;
  cursor: pointer;
  width: 80px;
  user-select: none;
}

.step-dot {
  width: 36px;
  height: 36px;
  margin: 0 auto 8px;
  background: #fff;
  border: 2px solid #e2e8f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.8rem;
  color: #94a3b8;
  transition: all 0.3s;
}

.step-node.active .step-dot {
  border-color: #0f766e;
  background: #0f766e;
  color: #fff;
  box-shadow: 0 0 0 5px rgba(15, 118, 110, 0.1);
  transform: scale(1.08);
}

.step-node.completed .step-dot {
  border-color: #0f766e;
  background: #0f766e;
  color: #fff;
}

.step-text {
  font-size: 0.65rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.step-node.active .step-text {
  color: #0f766e;
}

.section-head {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.section-icon {
  width: 36px;
  height: 36px;
  background: rgba(15, 118, 110, 0.07);
  border: 1px solid rgba(15, 118, 110, 0.1);
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f766e;
  font-size: 0.95rem;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.section-head h6 {
  font-size: 0.95rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.section-head p {
  font-size: 0.78rem;
  color: #94a3b8;
  margin: 0.15rem 0 0;
}

.field-group {
  margin-bottom: 1.15rem;
}

.field-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.35rem;
}

.req {
  color: #dc2626;
  font-weight: 700;
}

.input-wrap {
  position: relative;
}

.input-wrap > i {
  position: absolute;
  left: 0.85rem;
  top: 50%;
  transform: translateY(-50%);
  color: #b0bec5;
  font-size: 0.88rem;
  z-index: 2;
  pointer-events: none;
}

.input-wrap-textarea > i {
  top: 1.15rem;
  transform: none;
}

.form-input {
  width: 100%;
  padding: 0.65rem 0.9rem 0.65rem 2.5rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.625rem;
  font-size: 0.875rem;
  color: #0f172a;
  background: #fff;
  transition: all 0.2s;
  outline: none;
}

.form-input:focus {
  border-color: #0f766e;
  box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.07);
}

.input-wrap:focus-within > i {
  color: #0f766e;
}

.form-input.input-error {
  border-color: #dc2626;
  background: #fef2f2;
}

.field-error {
  font-size: 0.72rem;
  color: #dc2626;
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

textarea.form-input {
  resize: vertical;
  min-height: 76px;
}

/* Classification Radio Styles */
.classification-radio-group {
  display: flex;
  gap: 1rem;
}

.classification-radio-item {
  flex: 1;
  position: relative;
  cursor: pointer;
  border: 2px solid #e2e8f0;
  border-radius: 0.875rem;
  padding: 1rem;
  transition: all 0.3s;
  background: #fff;
}

.classification-radio-item:hover {
  border-color: #0f766e;
  background: #f0fdf4;
}

.classification-radio-item.selected {
  border-color: #0f766e;
  background: rgba(15, 118, 110, 0.04);
  box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.1);
  transform: translateY(-1px);
}

.classification-radio-item.selected .radio-label {
  color: #0f766e;
}

.classification-radio-item.selected .general-icon {
  background: rgba(59, 130, 246, 0.2);
  color: #2563eb;
}
.classification-radio-item.selected .confidential-icon {
  background: rgba(239, 68, 68, 0.2);
  color: #dc2626;
}
.classification-radio {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.radio-card-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.radio-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.general-icon {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.confidential-icon {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.radio-label {
  font-size: 0.85rem;
  font-weight: 600;
  display: block;
  color: #334155;
}

.radio-description {
  font-size: 0.72rem;
  color: #94a3b8;
  display: block;
}

/* Route Checkbox Styles */
.route-checklist {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
}

.route-checkbox-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem 0.9rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.5rem;
  transition: all 0.2s;
  background: #fff;
}

.route-checkbox-item:hover {
  border-color: #0f766e;
  background: #f0fdf4;
}

.route-checkbox {
  accent-color: #0f766e;
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.route-checkbox-label {
  font-size: 0.82rem;
  font-weight: 500;
  color: #475569;
  display: flex;
  align-items: center;
}

/* Dropzone */
.dropzone {
  border: 2px dashed #cbd5e1;
  border-radius: 0.875rem;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  background: #fafbfc;
}

.dropzone:hover {
  border-color: #0f766e;
  background: #f0fdf4;
}

.dropzone-icon {
  font-size: 2.2rem;
  color: #94a3b8;
  margin-bottom: 0.5rem;
}

/* Single File Card */
.single-file-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.75rem;
  background: #fff;
  transition: border-color 0.2s;
}

.single-file-card:hover {
  border-color: #0f766e;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.file-icon-wrap {
  width: 38px;
  height: 38px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.file-icon-wrap.color-pdf {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}
.file-icon-wrap.color-img {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}
.file-icon-wrap.color-doc {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}
.file-icon-wrap.color-default {
  background: rgba(148, 163, 184, 0.1);
  color: #94a3b8;
}

.file-details {
  min-width: 0;
}

.file-name {
  font-size: 0.82rem;
  font-weight: 600;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 220px;
}

.file-size {
  font-size: 0.7rem;
  color: #94a3b8;
}

.file-actions {
  display: flex;
  gap: 0.35rem;
  flex-shrink: 0;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.82rem;
}

.btn-icon-view {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}
.btn-icon-view:hover {
  background: rgba(59, 130, 246, 0.2);
}

.btn-icon-replace {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}
.btn-icon-replace:hover {
  background: rgba(245, 158, 11, 0.2);
}

.btn-icon-remove {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}
.btn-icon-remove:hover {
  background: rgba(239, 68, 68, 0.2);
}

/* Review Card Enhancements */
.review-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 0.875rem;
  margin-bottom: 1.25rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.review-tracking-highlight {
  background: linear-gradient(
    135deg,
    rgba(15, 118, 110, 0.05),
    rgba(20, 184, 166, 0.08)
  );
  border-bottom: 1px solid #e2e8f0;
  padding: 1rem 1.5rem;
}

.review-tracking-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.review-tracking-number {
  font-family: "JetBrains Mono", monospace;
  font-size: 0.95rem;
  font-weight: 700;
  color: #0f766e;
  background: rgba(255, 255, 255, 0.7);
  padding: 0.2rem 0.65rem;
  border-radius: 0.375rem;
  border: 1px solid rgba(15, 118, 110, 0.2);
}

.review-group {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px dashed #e2e8f0;
}

.review-group.border-bottom-0 {
  border-bottom: none;
}

.review-group-title {
  font-size: 0.75rem;
  font-weight: 700;
  color: #0f766e;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.review-item {
  background: #f8fafc;
  border: 1px solid #f1f5f9;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
  height: 100%;
}

.review-label {
  font-size: 0.68rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.35rem;
}

.review-value {
  font-size: 0.88rem;
  color: #1e293b;
  font-weight: 500;
  line-height: 1.4;
}

.subject-text {
  white-space: pre-wrap;
}

.review-highlight {
  font-weight: 700;
  color: #0f766e;
}

.review-bold {
  font-weight: 600;
  color: #0f172a;
}

.badge-general {
  display: inline-flex;
  align-items: center;
  padding: 0.35rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.badge-confidential {
  display: inline-flex;
  align-items: center;
  padding: 0.35rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.datetime-badge {
  display: inline-flex;
  align-items: center;
  font-family: "JetBrains Mono", monospace;
  font-size: 0.82rem;
  font-weight: 600;
  color: #0f766e;
  background: rgba(15, 118, 110, 0.05);
  padding: 0.3rem 0.65rem;
  border-radius: 0.375rem;
  border: 1px solid rgba(15, 118, 110, 0.1);
}

.route-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.route-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.3rem 0.65rem;
  border-radius: 0.375rem;
  font-size: 0.72rem;
  font-weight: 600;
  background: rgba(15, 118, 110, 0.1);
  color: #0f766e;
  border: 1px solid rgba(15, 118, 110, 0.15);
}

.review-file-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #fff;
  padding: 0.35rem 0.65rem;
  border-radius: 0.375rem;
  border: 1px solid #e2e8f0;
}

.file-icon-wrap-sm {
  width: 24px;
  height: 24px;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  flex-shrink: 0;
}

.file-name-sm {
  font-size: 0.78rem;
  font-weight: 600;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 130px;
  display: inline-block;
}

.btn-link-sm {
  background: none;
  border: none;
  color: #0f766e;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  transition: color 0.2s;
  margin-left: auto;
  flex-shrink: 0;
}

.btn-link-sm:hover {
  color: #0d5f59;
  text-decoration: underline;
}

.alert-note {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.85rem 1rem;
  border-radius: 0.625rem;
  background: #fffbeb;
  border: 1px solid #fde68a;
  font-size: 0.78rem;
  color: #92400e;
}

.alert-note i {
  font-size: 1rem;
  color: #f59e0b;
  flex-shrink: 0;
}

/* Navigation */
.wizard-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.75rem;
  padding-top: 1.25rem;
  border-top: 1px solid #e2e8f0;
}

.nav-right {
  display: flex;
  gap: 0.75rem;
  margin-left: auto;
}

.btn-nav {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.6rem 1.35rem;
  border: none;
  border-radius: 0.625rem;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s;
  outline: none;
}

.btn-prev {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.btn-prev:hover:not(:disabled) {
  background: #e2e8f0;
}

.btn-next {
  background: linear-gradient(135deg, #0f766e, #14b8a6);
  color: #fff;
  box-shadow: 0 2px 8px rgba(15, 118, 110, 0.25);
}

.btn-next:hover:not(:disabled) {
  box-shadow: 0 4px 14px rgba(15, 118, 110, 0.35);
  transform: translateY(-1px);
}

.btn-submit {
  background: linear-gradient(135deg, #0f766e, #14b8a6);
  color: #fff;
  box-shadow: 0 2px 8px rgba(15, 118, 110, 0.25);
  padding: 0.6rem 1.75rem;
}

.btn-submit:hover:not(:disabled) {
  box-shadow: 0 4px 14px rgba(15, 118, 110, 0.35);
  transform: translateY(-1px);
}

.btn-nav:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  transform: none !important;
}

.btn-sm {
  padding: 0.4rem 0.9rem;
  font-size: 0.75rem;
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.modal-box {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 640px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e2e8f0;
}

.btn-close-modal {
  width: 32px;
  height: 32px;
  border: none;
  background: #f1f5f9;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.btn-close-modal:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.modal-body {
  padding: 1.25rem;
  overflow-y: auto;
  flex: 1;
}

.modal-foot {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.65rem;
  padding: 0.85rem 1.25rem;
  border-top: 1px solid #e2e8f0;
}

/* Preview Area */
.preview-image-wrap {
  text-align: center;
}

.preview-image {
  max-width: 100%;
  max-height: 400px;
  border-radius: 0.5rem;
  object-fit: contain;
}

.preview-pdf-wrap,
.preview-docx-wrap {
  text-align: center;
}

.preview-pdf,
.preview-docx {
  width: 100%;
  height: 400px;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
}

.pdf-fallback,
.docx-fallback {
  margin-top: 0.75rem;
}

.preview-placeholder {
  text-align: center;
  padding: 2rem 0;
}

.placeholder-icon {
  width: 64px;
  height: 64px;
  border-radius: 1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
}

.preview-empty {
  text-align: center;
  padding: 3rem 0;
  color: #94a3b8;
}

.preview-empty i {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
  display: block;
}

.file-info-bar {
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}

/* Loading Overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-content {
  text-align: center;
  color: #fff;
  padding: 2.5rem;
  border-radius: 1.125rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  max-width: 400px;
  width: 90%;
}
.loading-spinner {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
}

.spinner-circle {
  width: 80px;
  height: 80px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: #22c55e;
  border-right-color: #15803d;
  border-radius: 50%;
  animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}

.spinner-logo {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 1.5rem;
  color: #22c55e;
  animation: pulse 2s ease-in-out infinite;
}

.loading-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #fff;
}

.loading-message {
  font-size: 0.9rem;
  color: #94a3b8;
  margin-bottom: 1.5rem;
}

.loading-progress {
  width: 100%;
  height: 3px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 1rem;
}

.loading-bar {
  height: 100%;
  width: 100%;
  background: linear-gradient(90deg, #15803d, #22c55e);
  animation: loadingBar 2.5s ease-in-out infinite;
  border-radius: 2px;
}

.loading-hint {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0;
}

/* Success Modal */
.success-modal-box {
  background: #fff;
  border-radius: 1.25rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 420px;
  overflow: hidden;
}

.success-modal-content {
  padding: 2.5rem 2rem 2rem;
}

.success-icon-wrapper {
  margin-bottom: 1.25rem;
}

.success-icon-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: rgba(15, 118, 110, 0.1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #0f766e;
}

.success-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 0.35rem;
}

.success-message {
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 1.5rem;
}

.tracking-number-box {
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 0.75rem;
  padding: 1rem 1.25rem;
  margin-bottom: 1.5rem;
}

.tracking-label {
  font-size: 0.65rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 0.35rem;
}

.tracking-number-display {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
}

.tracking-number-text {
  font-family: "JetBrains Mono", monospace;
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f766e;
  letter-spacing: 0.05em;
}

.copy-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #e2e8f0;
  background: #fff;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.copy-btn:hover {
  border-color: #0f766e;
  color: #0f766e;
}

.success-actions {
  display: flex;
  justify-content: center;
}

/* Toast Notification */
.toast-notification {
  position: fixed;
  top: 1.25rem;
  right: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.75rem 1.25rem;
  border-radius: 0.625rem;
  font-size: 0.82rem;
  font-weight: 600;
  z-index: 9999;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.toast-notification.success {
  background: #0f766e;
  color: #fff;
}

.toast-notification.error {
  background: #dc2626;
  color: #fff;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s;
}

.modal-enter,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s;
}

.toast-enter,
.toast-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.animate-in {
  animation: fadeInUp 0.35s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@keyframes loadingSlide {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(350%);
  }
}
.btn-back {
  background: #fff;
  color: #475569;
  border: 1.5px solid #e2e8f0;
}
</style>