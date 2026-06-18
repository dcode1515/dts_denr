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
              v-model="localSearch"
              @input="applyFilters"
              class="search-input"
              placeholder="Search by tracking no, subject, sender..."
            />
            <button
              v-if="localSearch"
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
            v-model="localPerPage"
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
              v-model="localDocTypeFilter"
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

        <div class="dropdown">
          <button
            class="btn-create-document dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <svg
              width="18"
              height="18"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
            >
              <line x1="12" y1="5" x2="12" y2="19" />
              <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            My Document
          </button>
          <ul
            class="dropdown-menu dropdown-menu-end shadow-sm border-0"
          >
            <li>
              <a
                class="dropdown-item py-2"
                href="javascript:void(0)"
                @click="$emit('open-tracking')"
              >
                <i class="bi bi-bookmark-check me-2 text-success"></i>My
                Tracking
              </a>
            </li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <a
                class="dropdown-item py-2"
                href="javascript:void(0)"
                @click="$emit('open-create')"
              >
                <i class="bi bi-plus-circle me-2 text-success"></i
                >Create New Tracking
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div
        class="active-filters"
        v-if="localSearch || localDocTypeFilter"
      >
        <span class="active-filters-label">Active Filters:</span>
        <span v-if="localSearch" class="filter-tag">
          <i class="bi bi-search"></i> "{{ localSearch }}"
          <button
            @click="clearSearch"
            class="filter-tag-close"
          >
            <i class="bi bi-x"></i>
          </button>
        </span>
        <span v-if="localDocTypeFilter" class="filter-tag">
          <i class="bi bi-funnel"></i> {{ localDocTypeFilter }}
          <button
            @click="clearDocTypeFilter"
            class="filter-tag-close"
          >
            <i class="bi bi-x"></i>
          </button>
        </span>
        <button
          @click="clearAllFilters"
          class="clear-all-filters"
        >
          Clear All
        </button>
      </div>

      <div class="results-summary">
        <span class="results-count">{{ data.total }}</span>
        document(s) found in <strong>{{ tabLabel }}</strong>
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
          <tr v-if="loading && data.data.length === 0">
            <td colspan="8" class="text-center">
              <div class="loader-spinner"></div>
              Loading...
            </td>
          </tr>
          <tr
            v-else-if="
              !loading && data.data.length === 0
            "
          >
            <td colspan="8" class="text-center">
              <div class="empty-state">
                <i
                  class="bi bi-inbox"
                  style="font-size: 3rem; color: #9ca3af"
                ></i>
                <p class="mt-2 text-muted">
                  {{
                    localSearch || localDocTypeFilter
                      ? "No documents match your filters"
                      : "No Data Found"
                  }}
                </p>
              </div>
            </td>
          </tr>
          <tr
            v-for="(item, index) in data.data"
            :key="item.id"
          >
            <td class="text-center">
              <span class="row-number">{{
                (data.current_page - 1) * data.per_page +
                index +
                1
              }}</span>
            </td>
            <td>
              <span class="tracking-number">{{
                item.tracking_number
              }}</span>
            </td>
            <td>{{ item.document_classification }}</td>
            <td>
              <span class="doc-type-badge">{{
                item.document_type?.document_type_name ||
                item.document_type
              }}</span>
            </td>
            <td>
              <div class="subject-text">{{ item.subject }}</div>
            </td>
            <td>
              <div class="sender-text">
                <i class="bi bi-person-circle sender-icon"></i>
                {{ item.sender_name }}
              </div>
            </td>
            <td>
              <div class="date-received">
                <i class="bi bi-calendar3 date-icon"></i
                >{{ formatDate(item.date_receive) }} At
                {{ formatTime(item.time_receive) }}
              </div>
            </td>
            <td>
              <div class="action-buttons">
                <button
                  class="btn-action btn-view"
                  @click="$emit('view-document', item)"
                  title="View Details"
                >
                  <i class="bi bi-eye"></i>
                </button>

                <button
                  class="btn-action btn-download"
                  @click="$emit('forward-document', item)"
                  title="Forward Office"
                >
                  <i class="bi bi-arrow-right-circle"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Reusable Pagination Component -->
    <PaginationComponent
      :current-page="Number(data.current_page)"
      :total-pages="Number(data.last_page)"
      :total="Number(data.total)"
      :per-page="Number(data.per_page)"
      :from="Number(data.from)"
      :to="Number(data.to)"
      @page-change="changePage"
    />
  </div>
</template>

<script>
import PaginationComponent from "../../components/PaginationComponent.vue";

export default {
  name: "InProgressTab",
  components: {
    PaginationComponent,
  },
  props: {
    data: {
      type: Object,
      required: true,
      default: () => ({
        data: [],
        current_page: 1,
        from: 1,
        to: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      }),
    },
    loading: {
      type: Boolean,
      default: false,
    },
    documentTypes: {
      type: Array,
      required: true,
    },
    tabLabel: {
      type: String,
      default: "In Progress",
    },
    search: {
      type: String,
      default: "",
    },
    docTypeFilter: {
      type: String,
      default: "",
    },
    perPage: {
      type: Number,
      default: 10,
    },
  },
  emits: [
    "update:search",
    "update:docTypeFilter",
    "update:perPage",
    "apply-filters",
    "page-change",
    "view-document",
    "forward-document",
    "open-tracking",
    "open-create",
  ],
  computed: {
    localSearch: {
      get() {
        return this.search;
      },
      set(value) {
        this.$emit("update:search", value);
      },
    },
    localDocTypeFilter: {
      get() {
        return this.docTypeFilter;
      },
      set(value) {
        this.$emit("update:docTypeFilter", value);
      },
    },
    localPerPage: {
      get() {
        return this.perPage;
      },
      set(value) {
        this.$emit("update:perPage", value);
      },
    },
  },
  methods: {
    applyFilters() {
      this.$emit("apply-filters");
    },
    clearSearch() {
      this.localSearch = "";
      this.applyFilters();
    },
    clearDocTypeFilter() {
      this.localDocTypeFilter = "";
      this.applyFilters();
    },
    clearAllFilters() {
      this.localSearch = "";
      this.localDocTypeFilter = "";
      this.applyFilters();
    },
    changePage(page) {
      this.$emit("page-change", page);
    },
    changePerPage() {
      this.$emit("page-change", 1);
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
  },
};
</script>

<style scoped>
/* All styles from the original In Progress section */
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

@media (max-width: 768px) {
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

  .active-filters {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>