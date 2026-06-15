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

      <!-- Table Controls -->
      <div class="table-controls">
        <div class="left-controls">
          <!-- Per Page Selector -->
          <div class="per-page-wrapper">
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
           
          </div>

          <!-- Search Box -->
          <div class="search-box-wrapper">
            <div class="search-box">
              <i class="bi bi-search search-icon"></i>
              <input
                type="text"
                v-model="searchQuery"
                @input="applyFilters"
                class="search-input"
                placeholder="Search by name, code, description..."
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
        </div>
        <button
          @click="openCreateModal"
          class="btn btn-sm shadow-sm"
          style="
            background-color: #2d6a4f;
            color: white;
            border: none;
            border-radius: 0;
            padding: 8px 16px;
          "
        >
          <i class="bi bi-plus-circle me-1"></i> Create Document Type
        </button>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="office-table">
          <thead>
            <tr>
              <th style="width: 5%">#</th>
              <th style="width: 25%">Document Type Name</th>
              <th style="width: 15%">Code</th>
            
              <th style="width: 15%">Status</th>
              <th style="width: 10%">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading && documentTypes.data.length === 0">
              <td colspan="6" class="text-center">
                <div class="loader-spinner"></div>
                Loading...
              </td>
            </tr>
            <tr v-else-if="!loading && documentTypes.data.length === 0">
              <td colspan="6" class="text-center">
                <i class="bi bi-inbox"></i> No Document Types Found
              </td>
            </tr>
            <tr v-for="(docType, index) in documentTypes.data" :key="docType.id">
              <td class="text-center">
                {{ (documentTypes.current_page - 1) * documentTypes.per_page + index + 1 }}
              </td>
              <td>
                <span class="office-name-text">{{ docType.document_type_name }}</span>
              </td>
              <td>
                <span class="badge bg-secondary">{{ docType.code }}</span>
              </td>
            
              <td>
                <span 
                  :class="['badge', docType.status === 'Active' ? 'bg-success' : 'bg-danger']"
                >
                  {{ docType.status || 'Active' }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button
                    class="btn-action btn-edit"
                    @click="openEditModal(docType)"
                    title="Edit"
                  >
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button
                    class="btn-action btn-delete"
                    @click="deleteDocumentType(docType.id)"
                    title="Delete"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-wrapper" v-if="documentTypes.total > 0">
        <div class="pagination-info">
          Showing {{ documentTypes.from }} to {{ documentTypes.to }} of
          {{ documentTypes.total }} entries
        </div>
        <div class="pagination-buttons">
          <button
            @click="changePage(1)"
            :disabled="documentTypes.current_page === 1"
            class="page-btn"
          >
            <i class="bi bi-chevron-double-left"></i>
          </button>
          <button
            @click="changePage(documentTypes.current_page - 1)"
            :disabled="documentTypes.current_page === 1"
            class="page-btn"
          >
            <i class="bi bi-chevron-left"></i>
          </button>

          <button
            v-for="page in displayedPages"
            :key="page"
            @click="changePage(page)"
            :class="['page-btn', { active: documentTypes.current_page === page }]"
          >
            {{ page }}
          </button>

          <button
            @click="changePage(documentTypes.current_page + 1)"
            :disabled="documentTypes.current_page === documentTypes.last_page"
            class="page-btn"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
          <button
            @click="changePage(documentTypes.last_page)"
            :disabled="documentTypes.current_page === documentTypes.last_page"
            class="page-btn"
          >
            <i class="bi bi-chevron-double-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- ================= CREATE / EDIT MODAL ================= -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-dialog enhanced-modal">
        <div class="modal-content square-modal">
          <div class="modal-header-enhanced square-header">
            <div class="d-flex align-items-center">
              <div class="modal-icon-wrapper square-icon">
                <i class="bi bi-file-earmark-text"></i>
              </div>
              <div>
                <h5 class="modal-title">
                  {{ isEditing ? 'Edit Document Type' : 'Create New Document Type' }}
                </h5>
                <small class="modal-subtitle">
                  {{
                    isEditing
                      ? 'Update document type details'
                      : 'Fill in the details to add a document type record'
                  }}
                </small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close-custom square-close"
              @click="closeModal"
              :disabled="loading"
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

            <form @submit.prevent="submitForm">
              <div class="row g-3">
                <!-- Document Type Name -->
                <div class="col-md-6">
                  <label for="name" class="form-label-enhanced">
                    Document Type Name <span class="required-star">*</span>
                  </label>
                  <div class="input-wrap">
                    <span class="input-icon">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                      </svg>
                    </span>
                    <input
                      type="text"
                      id="name"
                      v-model="form.name"
                      class="form-input"
                      :class="{ 'is-invalid': errors.name }"
                      placeholder="e.g., Memorandum, Letter, Report"
                      :disabled="loading"
                    />
                  </div>
                  <div
                    v-if="errors.name"
                    class="invalid-feedback d-block"
                  >
                    {{ errors.name }}
                  </div>
                </div>

                <!-- Code -->
                <div class="col-md-6">
                  <label for="code" class="form-label-enhanced">
                    Code <span class="required-star">*</span>
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
                      id="code"
                      v-model="form.code"
                      class="form-input"
                      :class="{ 'is-invalid': errors.code }"
                      placeholder="e.g., MEMO, LTR, RPT"
                      :disabled="loading"
                    />
                  </div>
                  <div
                    v-if="errors.code"
                    class="invalid-feedback d-block"
                  >
                    {{ errors.code }}
                  </div>
                </div>
              </div>

              <!-- Status -->
              <div class="mt-3">
                <label for="status" class="form-label-enhanced">
                  Status
                </label>
                <div class="input-wrap">
                  <span class="input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                      <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                  </span>
                  <select
                    id="status"
                    v-model="form.status"
                    class="form-input"
                    :class="{ 'is-invalid': errors.status }"
                    :disabled="loading"
                    style="padding-left: 42px;"
                  >
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                </div>
                <div v-if="errors.status" class="invalid-feedback d-block">
                  {{ errors.status }}
                </div>
              </div>

              <div class="modal-actions">
                <button
                  type="button"
                  class="btn btn-outline-secondary square-btn"
                  @click="resetForm"
                  :disabled="loading"
                >
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </button>
                <div class="d-flex gap-3">
                  <button
                    type="button"
                    class="btn btn-light square-btn"
                    @click="closeModal"
                    :disabled="loading"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="btn btn-save square-btn"
                    :disabled="loading"
                  >
                    <span
                      v-if="loading"
                      class="spinner-border spinner-border-sm me-1"
                      role="status"
                      aria-hidden="true"
                    ></span>
                    <i v-else class="bi bi-check2-circle me-1"></i>
                    {{
                      loading
                        ? 'Saving...'
                        : isEditing
                        ? 'Update Document Type'
                        : 'Save Document Type'
                    }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteConfirm"
      class="modal-overlay"
      @click.self="cancelDelete"
    >
      <div class="modal-dialog" style="max-width: 400px">
        <div class="modal-content square-modal">
          <div
            class="modal-header-enhanced square-header"
            style="background: linear-gradient(135deg, #b91c1c, #991b1b)"
          >
            <h5 class="modal-title">
              <i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete
            </h5>
            <button
              type="button"
              class="btn-close-custom square-close"
              @click="cancelDelete"
              :disabled="loading"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-body-enhanced text-center py-4">
            <p class="mb-4">
              Are you sure you want to delete
              <strong>{{ docTypeToDelete?.name }}</strong>?
            </p>
            <div class="d-flex justify-content-center gap-3">
              <button
                class="btn btn-light square-btn"
                @click="cancelDelete"
                :disabled="loading"
              >
                Cancel
              </button>
              <button
                class="btn btn-save square-btn"
                style="background: #dc2626"
                @click="confirmDelete"
                :disabled="loading"
              >
                <i class="bi bi-trash me-1"></i> Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "DocumentTypeList",
  props: {
    headerIcon: { type: String, default: "bi bi-file-earmark-text" },
    headerTitle: { type: String, default: "List of Document Types" },
    headerSubtitle: {
      type: String,
      default: "Directory of all document type classifications",
    },
  },
  emits: ["create-document-type", "update-document-type", "delete-document-type"],
  data() {
    return {
      searchQuery: "",
      perPage: 10,
      currentPage: 1,
      currentDateTime: "",
      dateTimeInterval: null,
      showModal: false,
      isEditing: false,
      editingId: null,
      loading: false,
      form: {
        name: "",
        code: "",
        status: "Active",
      },
      documentTypes: {
        data: [],
        current_page: 1,
        from: 1,
        to: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      },
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
      showDeleteConfirm: false,
      docTypeToDelete: null,
    };
  },
  computed: {
    displayedPages() {
      const pages = [];
      const total = this.documentTypes.last_page;
      const current = this.documentTypes.current_page;
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
  },
  mounted() {
    this.getDocumentTypes();
    this.updateDateTime();
    this.dateTimeInterval = setInterval(() => this.updateDateTime(), 1000);
  },
  beforeUnmount() {
    if (this.dateTimeInterval) clearInterval(this.dateTimeInterval);
    if (this.notification.timeout) clearTimeout(this.notification.timeout);
  },
  methods: {
    async getDocumentTypes(page = 1) {
      try {
        this.loading = true;
        const response = await axios.get("/dts_denr/api/document-type", {
          params: {
            page: page,
            per_page: this.perPage,
            search: this.searchQuery,
          },
        });
        this.documentTypes = response.data.data;
      } catch (error) {
        console.error("Error fetching document types:", error);
        this.showNotification(
          "Failed to load document types. Please try again.",
          "error"
        );
      } finally {
        this.loading = false;
      }
    },
    changePage(page) {
      if (
        page >= 1 &&
        page <= this.documentTypes.last_page &&
        page !== this.documentTypes.current_page
      ) {
        this.getDocumentTypes(page);
      }
    },
    changePerPage() {
      this.currentPage = 1;
      this.getDocumentTypes(1);
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
      this.isEditing = false;
      this.editingId = null;
      this.errors = {};
      this.formErrors.show = false;
      this.resetForm();
      this.showModal = true;
    },
    openEditModal(docType) {
      this.isEditing = true;
      this.editingId = docType.id;
      this.errors = {};
      this.formErrors.show = false;
      this.form = {
        name: docType.document_type_name || "",
        code: docType.code || "",
        status: docType.status || "Active",
      };
      this.showModal = true;
    },
    closeModal(force = false) {
      if (this.loading && !force) return;
      this.showModal = false;
      this.errors = {};
      this.formErrors.show = false;
      this.loading = false;
    },
    resetForm() {
      this.form = {
        name: "",
        code: "",
        status: "Active",
      };
      this.errors = {};
      this.formErrors.show = false;
    },
    validateForm() {
      this.errors = {};
      this.formErrors.show = false;
      const requiredFields = [
        "name",
        "code",
      ];
      const fieldLabels = {
        name: "Document Type Name",
        code: "Code",
      };
      let valid = true;
      for (const field of requiredFields) {
        if (!this.form[field] || !this.form[field].trim()) {
          this.errors[field] = `${fieldLabels[field]} is required.`;
          valid = false;
        }
      }
      if (!valid) {
        this.formErrors.show = true;
        this.formErrors.message = "Please correct the highlighted errors before saving.";
      }
      return valid;
    },
    async submitForm() {
      if (!this.validateForm()) {
        this.showNotification(
          "Please correct the highlighted errors before saving.",
          "error"
        );
        return;
      }

      this.loading = true;
      this.formErrors.show = false;
      const csrfToken = this.getCsrfToken();

      const url = this.isEditing
        ? `/dts_denr/api/document-type/${this.editingId}`
        : "/dts_denr/api/document-type";
      const method = this.isEditing ? "PUT" : "POST";

      const payload = {
        name: this.form.name.trim(),
        code: this.form.code.trim(),
        status: this.form.status,
      };

      try {
        const response = await fetch(url, {
          method: method,
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
            this.formErrors.message = "Please correct the highlighted errors before saving.";
            throw new Error("Validation failed");
          }
          throw new Error(data.message || "Request failed");
        }

        if (this.isEditing) {
          this.$emit("update-document-type", data.data);
        } else {
          this.$emit("create-document-type", data.data);
        }

        this.showNotification(
          `Document type "${payload.name}" ${this.isEditing ? "updated" : "created"} successfully!`,
          "success"
        );

        this.resetForm();
        this.closeModal(true);
        this.getDocumentTypes();

      } catch (error) {
        if (error.message !== "Validation failed") {
          this.formErrors.show = true;
          this.formErrors.message = error.message || "An error occurred while saving.";
          this.showNotification(
            error.message || "An error occurred while saving.",
            "error"
          );
        }
      } finally {
        this.loading = false;
      }
    },
    deleteDocumentType(id) {
      const docType = this.documentTypes.data.find((o) => o.id === id);
      this.docTypeToDelete = docType;
      this.showDeleteConfirm = true;
    },
    async confirmDelete() {
      if (!this.docTypeToDelete) return;

      const id = this.docTypeToDelete.id;
      const name = this.docTypeToDelete.name;

      this.loading = true;
      const csrfToken = this.getCsrfToken();

      try {
        const response = await fetch(`/dts_denr/api/document-type/${id}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
          },
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || 'Delete request failed');
        }

        const index = this.documentTypes.data.findIndex(o => o.id === id);
        if (index > -1) {
          this.documentTypes.data.splice(index, 1);
        }

        this.$emit('delete-document-type', { id, name });

        this.showNotification(
          `Document type "${name}" deleted successfully!`,
          'success'
        );

        await this.getDocumentTypes(this.documentTypes.current_page);

      } catch (error) {
        this.showNotification(
          error.message || 'An error occurred while deleting.',
          'error'
        );
      } finally {
        this.loading = false;
        this.cancelDelete();
      }
    },
    cancelDelete() {
      this.showDeleteConfirm = false;
      this.docTypeToDelete = null;
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
      this.getDocumentTypes(1);
    },
    clearSearch() {
      this.searchQuery = "";
      this.currentPage = 1;
      this.getDocumentTypes(1);
    },
  },
};
</script>

<style scoped>
/* ===== CSS Variables (matching login form) ===== */
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
  border-radius: 0;
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
  border-radius: 0;
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
  border-radius: 0;
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

/* ===== ERROR MESSAGE (matching login form) ===== */
.error-msg {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #FEF0EF;
  border: 1px solid #F5C6C3;
  border-radius: 7px;
  padding: 10px 14px;
  font-size: 12px;
  color: var(--error);
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

/* ===== INPUT WRAPPER (matching login form) ===== */
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

/* Select specific */
select.form-input {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235C7A6B' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  padding-right: 36px;
}

/* Validation states */
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
  border-radius: 0 !important;
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

/* ===== BADGES ===== */
.badge {
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.bg-secondary {
  background-color: #64748b;
  color: white;
}

.bg-success {
  background-color: #059669;
  color: white;
}

.bg-danger {
  background-color: #dc2626;
  color: white;
}

/* ===== ACTION BUTTONS (TABLE) ===== */
.action-buttons {
  display: flex;
  gap: 8px;
  align-items: center;
}

.btn-action {
  background: none;
  border: 1px solid transparent;
  border-radius: 4px;
  padding: 4px 8px;
  cursor: pointer;
  font-size: 1rem;
}

.btn-edit {
  color: #2563eb;
  border-color: #bfdbfe;
  background: #eff6ff;
}

.btn-edit:hover {
  background: #dbeafe;
}

.btn-delete {
  color: #dc2626;
  border-color: #fecaca;
  background: #fef2f2;
}

.btn-delete:hover {
  background: #fee2e2;
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

/* ===== TABLE CONTROLS ===== */
.table-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.left-controls {
  display: flex;
  align-items: center;
  gap: 15px;
  flex: 1;
  min-width: 0;
}

.per-page-wrapper {
  display: flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
  flex-shrink: 0;
}

.per-page-select {
  padding: 6px 8px;
  border: 1px solid #ced4da;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  outline: none;
}

.per-page-select:focus {
  border-color: #2d6a4f;
}

.per-page-label {
  font-size: 14px;
  color: #495057;
}

.search-box-wrapper {
  flex: 1;
  min-width: 150px;
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
  color: #6c757d;
}

.search-input {
  width: 100%;
  padding: 8px 30px 8px 32px;
  border: 1px solid #ced4da;
  border-radius: 6px;
  font-size: 14px;
  outline: none;
  box-sizing: border-box;
}

.search-input:focus {
  border-color: #2d6a4f;
}

.search-clear-btn {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
}

/* ===== TABLE ===== */
.table-responsive {
  overflow-x: auto;
}

.office-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  background: white;
}

.office-table th,
.office-table td {
  border: 1px solid #dee2e6;
  padding: 12px 14px;
  text-align: left;
  vertical-align: middle;
}

.office-table thead th {
  background: #f8f9fa;
  font-weight: 600;
  color: #212529;
  text-transform: uppercase;
}

.office-table tbody tr:hover {
  background: #f8f9fa;
}

.office-table tbody tr:nth-child(even) {
  background: #fafafa;
}

.empty-state {
  padding: 40px 20px;
}

.office-name-text {
  font-weight: 600;
  color: #1e293b;
}

.address-text {
  color: #475569;
  line-height: 1.4;
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
  font-size: 14px;
  color: #6c757d;
  white-space: nowrap;
}

.pagination-buttons {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.page-btn {
  border: 1px solid #dee2e6;
  background: white;
  color: #495057;
  padding: 6px 12px;
  cursor: pointer;
  border-radius: 4px;
  font-size: 14px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.page-btn:hover:not(:disabled) {
  background: #f1f3f5;
}

.page-btn.active {
  background: #2d6a4f;
  color: white;
  border-color: #2d6a4f;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 576px) {
  .pagination-wrapper {
    flex-direction: column;
    align-items: center;
  }
  .pagination-buttons {
    justify-content: center;
  }
}

@media (max-width: 500px) {
  .left-controls {
    flex-wrap: wrap;
  }
  .search-box-wrapper {
    flex: 1 1 100%;
    min-width: 100%;
  }
}

@media (max-width: 768px) {
  .table-controls {
    flex-direction: column;
    align-items: stretch;
  }
  .office-table {
    font-size: 11px;
  }
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