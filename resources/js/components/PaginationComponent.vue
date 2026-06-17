<template>
  <div class="pagination-wrapper" v-if="totalPages > 0">
    <div class="pagination-info">
      Showing {{ startItem }} to {{ endItem }} of {{ total }} entries
    </div>
    <div class="pagination-buttons">
      <button 
        @click="$emit('page-change', 1)" 
        :disabled="currentPage === 1" 
        class="page-btn"
        title="First Page"
      >
        <i class="bi bi-chevron-double-left"></i>
      </button>
      <button 
        @click="$emit('page-change', currentPage - 1)" 
        :disabled="currentPage === 1" 
        class="page-btn"
        title="Previous Page"
      >
        <i class="bi bi-chevron-left"></i>
      </button>
      <button 
        v-for="page in displayedPages" 
        :key="page"
        @click="page !== '...' && $emit('page-change', page)" 
        :class="['page-btn', { active: currentPage === page }]"
        :disabled="page === '...'"
      >
        {{ page }}
      </button>
      <button 
        @click="$emit('page-change', currentPage + 1)" 
        :disabled="currentPage === totalPages" 
        class="page-btn"
        title="Next Page"
      >
        <i class="bi bi-chevron-right"></i>
      </button>
      <button 
        @click="$emit('page-change', totalPages)" 
        :disabled="currentPage === totalPages" 
        class="page-btn"
        title="Last Page"
      >
        <i class="bi bi-chevron-double-right"></i>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaginationComponent',
  props: {
    currentPage: {
      type: Number,
      required: true,
      default: 1
    },
    totalPages: {
      type: Number,
      required: true,
      default: 0
    },
    total: {
      type: Number,
      required: true,
      default: 0
    },
    perPage: {
      type: Number,
      required: true,
      default: 10
    },
    from: {
      type: Number,
      default: 0
    },
    to: {
      type: Number,
      default: 0
    }
  },
  emits: ['page-change'],
  computed: {
    startItem() {
      if (this.total === 0) return 0;
      return this.from || ((this.currentPage - 1) * this.perPage) + 1;
    },
    endItem() {
      if (this.total === 0) return 0;
      return this.to || Math.min(this.currentPage * this.perPage, this.total);
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
        } else if (pages[pages.length - 1] !== '...') {
          pages.push('...');
        }
      }
      
      return pages;
    }
  }
};
</script>

<style scoped>
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
  min-width: 36px;
  height: 36px;
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

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .pagination-wrapper {
    flex-direction: column;
    align-items: center;
  }
  
  .pagination-buttons {
    justify-content: center;
  }
  
  .page-btn {
    padding: 5px 10px;
    font-size: 12px;
    min-width: 32px;
    height: 32px;
  }
}
</style>