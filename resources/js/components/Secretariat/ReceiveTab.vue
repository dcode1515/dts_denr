<!-- resources/js/components/tabs/ForReleaseTab.vue -->
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
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="search-clear-btn"
            >
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
        </div>
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
        <div class="filter-wrapper">
          <div class="filter-box">
            <i class="bi bi-funnel filter-icon"></i>
            <select
              v-model="docTypeFilter"
              @change="applyFilters"
              class="filter-select"
            >
              <option value="">All Document Types</option>
              <option
                v-for="type in documentTypes"
                :key="type"
                :value="type"
              >
                {{ type }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div
        class="active-filters"
        v-if="searchQuery || docTypeFilter"
      >
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
        <span class="results-count">{{ filteredDocuments.length }}</span>
        document(s) found in <strong>Forwarded</strong>
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
          <tr v-if="loading && documents.length === 0">
            <td colspan="7" class="text-center py-5">
              <div class="loader-spinner"></div>
              <div class="mt-2">Loading documents...</div>
            </td>
          </tr>
          <tr v-else-if="!loading && filteredDocuments.length === 0">
            <td colspan="7" class="text-center py-5">
              <div class="empty-state">
                <i
                  class="bi bi-file-earmark-x"
                  style="font-size: 3rem; color: #9ca3af"
                ></i>
                <p class="mt-2 text-muted">
                  {{
                    searchQuery || docTypeFilter
                      ? "No documents match your filters"
                      : "No For Receive Documents"
                  }}
                </p>
              </div>
            </td>
          </tr>
          <tr v-for="(doc, index) in paginatedDocuments" :key="doc.id">
            <td class="text-center">
              <span class="row-number">{{
                (currentPage - 1) * perPage + index + 1
              }}</span>
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
    <PaginationComponent
      :current-page="currentPage"
      :total-pages="totalPages"
      :total="filteredDocuments.length"
      :per-page="perPage"
      :from="startItem"
      :to="endItem"
      @page-change="changePage"
    />

    <!-- ================= ENHANCED DOCUMENT VIEWER MODAL ================= -->
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
                    >{{ selectedDocument?.status }}</span
                  >
                </small>
              </div>
            </div>
            <div class="header-actions">
              <a
                class="btn-header-action btn-header-update"
                :href="`/dts_denr/view-document/${selectedDocument?.token}`"
                title="Update Document"
              >
                <i class="bi bi-pencil-square"></i>
              </a>
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

            <!-- Document Information Tab -->
            <div
              v-show="viewerActiveTab === 'details'"
              class="document-viewer-layout"
            >
              <!-- Left Panel: Document Details -->
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
                      <span class="doc-type-badge-large">{{
                        selectedDocument.document_type?.document_type_name ||
                        selectedDocument.document_type
                      }}</span>
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
                          >at
                          {{ formatTime(selectedDocument.time_receive) }}</small
                        >
                      </span>
                    </div>
                  </div>

                  <div
                    class="detail-card description-card"
                    v-if="selectedDocument.description"
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
                        >{{ selectedDocument?.status || "Unknown" }}</span
                      >
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
                    <button
                      class="btn-pdf-control"
                      @click="zoomIn"
                      title="Zoom In"
                      :disabled="pdfLoadError"
                    >
                      <i class="bi bi-zoom-in"></i>
                    </button>
                    <button
                      class="btn-pdf-control"
                      @click="zoomOut"
                      title="Zoom Out"
                      :disabled="pdfLoadError"
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

                <div class="pdf-footer" v-if="!pdfLoadError">
                  <span class="pdf-zoom-level"
                    >Zoom: {{ Math.round(pdfZoom * 100) }}%</span
                  >
                  <span class="pdf-page-info" v-if="selectedDocument"
                    >File:
                    {{ selectedDocument.tracking_number }}_attachment.pdf</span
                  >
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
                  class="route-history-badge"
                  v-if="
                    selectedDocument?.document_route &&
                    selectedDocument.document_route.length > 0
                  "
                >
                  <i class="bi bi-arrow-left-right"></i>
                  <span
                    >{{ selectedDocument.document_route.length }} Route{{
                      selectedDocument.document_route.length !== 1 ? "s" : ""
                    }}</span
                  >
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
                            <span class="office-name">{{
                              route.office?.sub_office_name ||
                              route.sub_office_name ||
                              "Unknown Office"
                            }}</span>
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
                          <!-- Received Information Card -->
                          <div
                            class="info-card received-card"
                            v-if="route.date_receive"
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
                                class="info-field"
                                v-if="route.received_by || route.received_by_name"
                              >
                                <div class="field-icon">
                                  <i class="bi bi-person-check"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Received By</span>
                                  <span class="field-value received-by">
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

                              <div
                                class="info-field remarks-field"
                                v-if="route.remarks"
                              >
                                <div class="field-icon remarks-icon">
                                  <i class="bi bi-chat-left-text"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Remarks</span>
                                  <span class="field-value remarks-text">
                                    {{ route.remarks }}
                                  </span>
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- Completed Information Card -->
                          <div
                            class="info-card completed-card"
                            v-if="route.date_document_out"
                          >
                            <div class="info-card-header">
                              <div class="info-card-icon completed-icon">
                                <i class="bi bi-check2-all"></i>
                              </div>
                              <span class="info-card-title">Completion Information</span>
                              <span
                                class="info-card-status completed-status"
                                v-if="route.status === 'Completed'"
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
                                    {{ formatDateTime(route.date_document_out) }}
                                  </span>
                                </div>
                              </div>

                              <div
                                class="info-field"
                                v-if="route.completed_by || route.completed_by_name"
                              >
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

                              <div
                                class="info-field"
                                v-if="route.remarks"
                              >
                                <div class="field-icon notes-icon">
                                  <i class="bi bi-sticky"></i>
                                </div>
                                <div class="field-content">
                                  <span class="field-label">Completion Notes</span>
                                  <span class="field-value notes-text">
                                    {{ route.remarks }}
                                  </span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Flow Section -->
                        <div
                          class="flow-section-enhanced"
                          v-if="route.from_office || route.to_office"
                        >
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

                            <div class="flow-arrow-enhanced" v-if="route.from_office && route.to_office">
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

                        <div 
                          v-if="!route.date_receive && !route.date_document_out && !route.from_office && !route.to_office"
                          class="text-center py-3 text-muted"
                        >
                          <i class="bi bi-hourglass-split" style="font-size: 1.5rem;"></i>
                          <p class="mt-2 mb-0" style="font-size: 0.85rem;">Awaiting action</p>
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
  name: "ForReleaseTab",
  components: {
    PaginationComponent,
  },
  emits: [
    "view-document",
    "download-document",
    "release-document",
    "show-notification",
  ],
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
      // DOCUMENTS DATA
      documents: [],
      searchQuery: "",
      docTypeFilter: "",
      perPage: 10,
      currentPage: 1,
      loading: false,

      // VIEW MODAL
      showViewModal: false,
      selectedDocument: null,
      viewerActiveTab: "details",
      pdfLoading: true,
      pdfLoadError: false,
      pdfZoom: 1,
      pdfViewerHeight: 700,
      routeHistoryLoading: false,
    };
  },
  computed: {
    filteredDocuments() {
      let filtered = [...this.documents];
      if (this.docTypeFilter) {
        filtered = filtered.filter(
          (doc) => doc.document_type === this.docTypeFilter
        );
      }
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(
          (doc) =>
            doc.tracking_number?.toLowerCase().includes(query) ||
            doc.document_type?.toLowerCase().includes(query) ||
            doc.subject?.toLowerCase().includes(query) ||
            doc.sender_name?.toLowerCase().includes(query)
        );
      }
      return filtered;
    },
    paginatedDocuments() {
      const start = (this.currentPage - 1) * this.perPage;
      return this.filteredDocuments.slice(start, start + this.perPage);
    },
    totalPages() {
      return Math.ceil(this.filteredDocuments.length / this.perPage) || 1;
    },
    startItem() {
      return this.filteredDocuments.length === 0
        ? 0
        : (this.currentPage - 1) * this.perPage + 1;
    },
    endItem() {
      return Math.min(
        this.currentPage * this.perPage,
        this.filteredDocuments.length
      );
    },
  },
  mounted() {
    //this.getDocuments();
  },
  methods: {
    // ===== DATA FETCHING =====
    // async getDocuments() {
    //   try {
    //     this.loading = true;
    //     const response = await axios.get("/dts_denr/api/for-release");
    //     this.documents = response.data.data || [];
    //   } catch (error) {
    //     console.error("Error fetching for-release data:", error);
    //     this.$emit("show-notification", {
    //       message: "Failed to load for-release data.",
    //       type: "error",
    //     });
    //   } finally {
    //     this.loading = false;
    //   }
    // },

    refreshData() {
      this.getDocuments();
    },

    // ===== FILTER METHODS =====
    applyFilters() {
      this.currentPage = 1;
    },

    clearSearch() {
      this.searchQuery = "";
      this.currentPage = 1;
    },

    clearDocTypeFilter() {
      this.docTypeFilter = "";
      this.currentPage = 1;
    },

    clearAllFilters() {
      this.searchQuery = "";
      this.docTypeFilter = "";
      this.currentPage = 1;
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
        this.currentPage = page;
      }
    },

    changePerPage() {
      this.currentPage = 1;
    },

    // ===== DOCUMENT ACTIONS =====
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

    async releaseDocument(doc) {
      const result = await Swal.fire({
        title: "Release Document?",
        text: `Are you sure you want to release document ${doc.tracking_number}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#1a4731",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Yes, release it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
      });

      if (result.isConfirmed) {
        this.$emit("release-document", doc);
        this.$emit("show-notification", {
          message: `Document ${doc.tracking_number} released successfully!`,
          type: "success",
        });
        // Refresh the list after release
        await this.getDocuments();
      }
    },

    downloadDocument(doc) {
      this.$emit("download-document", doc);
    },

    // ===== PDF METHODS =====
    getPdfUrl(document) {
      if (!document || !document.tracking_number) return "";
      if (document.draft_attachment) {
        return `/dts_denr/storage/app/public/${document.draft_attachment}`;
      }
      const trackingNumber = document.tracking_number;
      return `/dts_denr/storage/app/public/attachments/${trackingNumber}/draft_attachment.pdf`;
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

    // ===== ROUTE HISTORY =====
    switchToHistoryTab() {
      this.viewerActiveTab = "history";
    },

    isRouteActive(route) {
      return (
        route.status === "In Progress" ||
        route.status === "Active" ||
        route.status === "Current" ||
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

    // ===== UTILITY METHODS =====
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
      if (
        typeof t === "string" &&
        (t.toUpperCase().includes("AM") || t.toUpperCase().includes("PM"))
      )
        return t;
      const [h, m] = t.split(":").map(Number),
        period = h >= 12 ? "PM" : "AM",
        displayHour = h % 12 || 12;
      return `${displayHour}:${m.toString().padStart(2, "0")} ${period}`;
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
  width: 220px;
  min-width: 220px;
  background: #f8fafc;
  border-right: 1px solid #e5e7eb;
  padding: 12px;
  gap: 6px;
}

.tab-vertical-button {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
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
  width: 3px;
  height: 0;
  background: linear-gradient(180deg, #2d6a4f, #1e4d2b);
  border-radius: 0 3px 3px 0;
  transition: height 0.3s ease;
}

.tab-vertical-button:hover {
  background: #f0fdf4;
  border-color: #86efac;
  transform: translateX(3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.tab-vertical-button.active {
  background: linear-gradient(135deg, #2d6a4f, #1e4d2b);
  border-color: #2d6a4f;
  color: #ffffff;
  transform: translateX(3px);
  box-shadow: 0 8px 25px rgba(45, 106, 79, 0.4);
}

.tab-vertical-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
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
  gap: 1px;
  flex: 1;
}

.tab-vertical-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.8px;
  color: #374151;
}

.tab-vertical-button.active .tab-vertical-label {
  color: #ffffff;
}

.tab-vertical-count {
  font-size: 17px;
  font-weight: 800;
  color: #2d6a4f;
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

/* ===== ACTIVE FILTERS ===== */
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

/* ===== STATUS BADGE ===== */
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

/* ===== LOADER ===== */
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

.empty-state {
  padding: 20px;
  text-align: center;
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

/* ===== ENHANCED DOCUMENT VIEWER ===== */
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
  padding: 12px 20px;
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  flex-shrink: 0;
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

.document-viewer-layout {
  display: grid;
  grid-template-columns: 380px 1fr;
  height: calc(90vh - 140px);
  min-height: 500px;
}

/* ===== DETAILS PANEL ===== */
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

.detail-card {
  display: flex;
  gap: 12px;
  padding: 14px;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  transition: all 0.2s ease;
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
  font-family: "Courier New", monospace;
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

/* ===== PDF PANEL ===== */
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
  font-family: "Courier New", monospace;
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

.route-history-content {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
  background: #f9fafb;
}

.route-history-content::-webkit-scrollbar {
  width: 8px;
}

.route-history-content::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.route-history-content::-webkit-scrollbar-thumb {
  background: #94a3b8;
  border-radius: 4px;
}

.route-state-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 300px;
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
  margin-bottom: 16px;
}

/* ===== TIMELINE ===== */
.timeline-container {
  position: relative;
  padding-left: 45px;
}

.timeline-container::before {
  content: "";
  position: absolute;
  left: 14px;
  top: 10px;
  bottom: 10px;
  width: 2px;
  background: linear-gradient(180deg, #cbd5e1 0%, #e2e8f0 100%);
}

.timeline-item {
  position: relative;
  margin-bottom: 32px;
}

.timeline-item.is-last {
  margin-bottom: 0;
}

.timeline-node {
  position: absolute;
  left: -38px;
  top: 0;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  z-index: 2;
  border: 3px solid #ffffff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  font-size: 0.8rem;
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

@keyframes pulse {
  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
  }
  50% {
    box-shadow: 0 0 0 8px rgba(37, 99, 235, 0.1);
  }
}

.timeline-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.3s ease;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.timeline-card:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
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
  padding: 16px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
  flex-wrap: wrap;
  gap: 12px;
}

.office-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.office-info i {
  font-size: 1.25rem;
  color: #2d6a4f;
}

.office-text {
  display: flex;
  flex-direction: column;
}

.office-name {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.3;
}

.route-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 5px 12px;
  border-radius: 20px;
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
  padding: 20px;
}

/* ===== ENHANCED INFO GRID ===== */
.info-grid-enhanced {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

/* ===== INFO CARDS ===== */
.info-card {
  background: #ffffff;
  border-radius: 12px;
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

.info-card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
}

.info-card-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: #ffffff;
  flex-shrink: 0;
}

.completed-icon {
  background: linear-gradient(135deg, #10b981, #059669);
}

.info-card-title {
  font-weight: 700;
  font-size: 0.8rem;
  color: #1e293b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  flex: 1;
}

.info-card-status {
  font-size: 0.65rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
}

.completed-status {
  background: #d1fae5;
  color: #059669;
}

.info-card-body {
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.info-field {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.field-icon {
  width: 28px;
  height: 28px;
  min-width: 28px;
  border-radius: 6px;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  color: #64748b;
}

.remarks-icon {
  background: #fffbeb;
  color: #d97706;
}

.notes-icon {
  background: #f0fdf4;
  color: #059669;
}

.field-content {
  flex: 1;
  min-width: 0;
}

.field-label {
  display: block;
  font-size: 0.65rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}

.field-value {
  display: block;
  font-size: 0.85rem;
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
  padding: 4px 10px;
  border-radius: 6px;
  border-left: 3px solid #f59e0b;
}

.notes-text {
  background: #f0fdf4;
  padding: 4px 10px;
  border-radius: 6px;
  border-left: 3px solid #10b981;
}

.text-muted {
  color: #94a3b8;
}

/* ===== FLOW SECTION ===== */
.flow-section-enhanced {
  margin-top: 8px;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.flow-container {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.flow-node {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 150px;
  padding: 10px 14px;
  background: #ffffff;
  border-radius: 10px;
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
  width: 32px;
  height: 32px;
  min-width: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
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
  font-size: 0.6rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.flow-node-value {
  display: block;
  font-size: 0.85rem;
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
  width: 20px;
  height: 2px;
  background: linear-gradient(90deg, #94a3b8, #cbd5e1);
  border-radius: 2px;
}

.arrow-icon {
  font-size: 1.4rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: arrowPulse 1.5s ease-in-out infinite;
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

/* ===== DOCUMENT HEADER ===== */
.document-header {
  padding: 16px 24px;
}

.document-icon {
  background: rgba(220, 38, 38, 0.2);
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

.tracking-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 2px 10px;
  border-radius: 12px;
  font-family: "Courier New", monospace;
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

/* ===== RESPONSIVE ===== */
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

  .info-grid-enhanced {
    grid-template-columns: 1fr;
  }

  .flow-container {
    flex-direction: column;
    gap: 8px;
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
    width: 30px;
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

  .timeline-container {
    padding-left: 35px;
  }

  .timeline-node {
    left: -30px;
    width: 24px;
    height: 24px;
    font-size: 0.7rem;
  }

  .info-card-body {
    padding: 10px 12px;
    gap: 8px;
  }

  .info-field {
    flex-direction: column;
    gap: 4px;
  }

  .field-icon {
    width: 24px;
    height: 24px;
    min-width: 24px;
    font-size: 0.75rem;
  }

  .flow-node {
    padding: 8px 12px;
  }

  .flow-node-value {
    font-size: 0.8rem;
  }

  .flow-section-enhanced {
    padding: 12px 14px;
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

  .info-card {
    border-radius: 8px;
  }

  .info-card-title {
    font-size: 0.7rem;
  }

  .field-value {
    font-size: 0.8rem;
  }

  .enhanced-modal {
    max-width: 95%;
    margin: 0 10px;
  }
}
</style>