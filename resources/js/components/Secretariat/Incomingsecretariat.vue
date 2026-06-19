<!-- IncomingSecretariat.vue -->
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
        <button @click="notification.show = false" class="notification-close">
          <i class="bi bi-x"></i>
        </button>
      </div>

      <!-- Vertical Tabs Design -->
      <div class="tabs-vertical-container">
        <div class="tabs-vertical-nav">
          <button
            class="tab-vertical-button"
            :class="{ active: activeTab === 'pending-tab' }"
            @click="switchTab('pending-tab')"
          >
            <div class="tab-vertical-icon">
              <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                :stroke="activeTab === 'pending-tab' ? '#ffffff' : '#059669'"
                stroke-width="2"
              >
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">PENDING</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>

          <button
            class="tab-vertical-button"
            :class="{ active: activeTab === 'receive-tab' }"
            @click="switchTab('receive-tab')"
          >
            <div class="tab-vertical-icon">
              <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                :stroke="activeTab === 'receive-tab' ? '#ffffff' : '#059669'"
                stroke-width="2"
              >
                <path
                  d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                />
                <polyline points="14 2 14 8 20 8" />
                <line x1="12" y1="12" x2="12" y2="18" />
                <polyline points="9 15 12 18 15 15" />
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">RECEIVE</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>

          <button
            class="tab-vertical-button"
            :class="{ active: activeTab === 'forward-tab' }"
            @click="switchTab('forward-tab')"
          >
            <div class="tab-vertical-icon">
              <svg
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                :stroke="activeTab === 'forward-tab' ? '#ffffff' : '#059669'"
                stroke-width="2"
              >
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>
            </div>
            <div class="tab-vertical-content">
              <span class="tab-vertical-label">FORWARDED</span>
            </div>
            <div class="tab-vertical-indicator"></div>
          </button>
        </div>

        <div class="tabs-vertical-body">
          <!-- ==================== PENDING TAB ==================== -->
          <div v-show="activeTab === 'pending-tab'">
            <PendingTab
              ref="pendingTab"
              :document-types="documentTypes"
              @view-document="viewDocument"
              @download-document="downloadDocument"
              @show-notification="showNotification"
            />
          </div>

          <!-- ==================== RECEIVE TAB ==================== -->
          <div v-show="activeTab === 'receive-tab'">
            <ReceiveTab
              ref="receiveTab"
              :document-types="documentTypes"
              @view-document="viewDocument"
              @download-document="downloadDocument"
              @release-document="handleReleaseDocument"
              @show-notification="showNotification"
            />
          </div>

          <!-- ==================== FORWARDED TAB ==================== -->
          <div v-show="activeTab === 'forward-tab'">
            <ForwardedTab
              ref="forwardedTab"
              :document-types="documentTypes"
              @view-document="viewDocument"
              @download-document="downloadDocument"
              @show-notification="showNotification"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PendingTab from "./PendingTab.vue";
import ReceiveTab from "./ReceiveTab.vue";
import ForwardedTab from "./ForwardedTab.vue";

export default {
  name: "IncomingSecretariat",
  components: {
    PendingTab,
    ReceiveTab,
    ForwardedTab,
  },
  props: {
    headerIcon: { type: String, default: "bi bi-inbox" },
    headerTitle: { type: String, default: "Incoming Documents" },
    headerSubtitle: {
      type: String,
      default: "Track and manage incoming document workflow",
    },
  },
  emits: [
    "process-document",
    "view-document",
    "download-document",
  ],
  data() {
    return {
      activeTab: "pending-tab",
      currentDateTime: "",
      dateTimeInterval: null,

      documentTypes: [
        "Memorandum",
        "Letter",
        "Report",
        "Request",
        "Permit",
        "Certificate",
      ],

      notification: {
        show: false,
        message: "",
        type: "success",
        timeout: null,
      },
    };
  },
  mounted() {
    this.updateDateTime();
    this.dateTimeInterval = setInterval(() => this.updateDateTime(), 1000);
  },
  beforeUnmount() {
    if (this.dateTimeInterval) clearInterval(this.dateTimeInterval);
    if (this.notification.timeout) clearTimeout(this.notification.timeout);
  },
  methods: {
    // ===== TAB SWITCHING =====
    switchTab(tab) {
      this.activeTab = tab;
      
      // Refresh data when switching to receive tab
      if (tab === 'receive-tab' && this.$refs.receiveTab) {
        this.$refs.receiveTab.refreshData();
      }
      
      // Refresh data when switching to forwarded tab
      if (tab === 'forward-tab' && this.$refs.forwardedTab) {
        this.$refs.forwardedTab.refreshData();
      }
    },

    // ===== DATE/TIME =====
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

    // ===== NOTIFICATION =====
    showNotification({ message, type = "success" }) {
      if (this.notification.timeout) clearTimeout(this.notification.timeout);
      this.notification.show = true;
      this.notification.message = message;
      this.notification.type = type;
      this.notification.timeout = setTimeout(() => {
        this.notification.show = false;
      }, 3000);
    },

    // ===== EVENT HANDLERS FROM CHILD COMPONENTS =====
    viewDocument(doc) {
      this.$emit("view-document", doc);
    },

    downloadDocument(doc) {
      this.$emit("download-document", doc);
    },

    handleReleaseDocument(doc) {
      this.$emit("process-document", doc);
      
      // Refresh receive tab data after release
      if (this.$refs.receiveTab) {
        setTimeout(() => {
          this.$refs.receiveTab.refreshData();
        }, 500);
      }
      
      // Refresh forwarded tab data after release
      if (this.$refs.forwardedTab) {
        setTimeout(() => {
          this.$refs.forwardedTab.refreshData();
        }, 500);
      }
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
}

@media (max-width: 576px) {
  .notification-toast {
    left: 10px;
    right: 10px;
    min-width: auto;
    max-width: none;
    top: 10px;
  }
}
</style>