<template>
  <div class="form-inner">
    <div class="form-header">
      <div class="system-tag">Secure Portal</div>
      <h2>Sign in to DTS</h2>
      <p>Enter your credentials to access the Document Tracking System. Authorized DENR personnel only.</p>
    </div>

    <!-- General Error Alert -->
    <transition name="error-slide">
      <div v-if="generalError" class="error-msg" role="alert">
        <div class="error-icon-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none"/>
          </svg>
        </div>
        <div class="error-content">
          <span class="error-title">Authentication Error</span>
          <span class="error-message">{{ generalError }}</span>
        </div>
        <button @click="generalError = ''" class="error-dismiss" aria-label="Dismiss error">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </transition>

    <form @submit.prevent="handleLogin" novalidate>
      <!-- Email Field -->
      <div class="field" :class="{ 'has-error': errors.email, 'is-valid': form.email && !errors.email }">
        <label for="email">Email Address</label>
        <div class="input-wrap">
          <span class="input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
          </span>
          <input 
            type="email" 
            id="email" 
            v-model="form.email"
            placeholder="you@denr.gov.ph" 
            autocomplete="email"
            :disabled="loading"
            @input="clearFieldError('email')"
          />
          <span v-if="form.email && !errors.email" class="validation-icon valid-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </span>
          <span v-if="errors.email" class="validation-icon error-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="15" y1="9" x2="9" y2="15"/>
              <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </span>
        </div>
        <transition name="error-fade">
          <span v-if="errors.email" class="field-error">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <circle cx="12" cy="16" r="0.5" fill="currentColor"/>
            </svg>
            {{ errors.email[0] }}
          </span>
        </transition>
      </div>

      <!-- Password Field -->
      <div class="field" :class="{ 'has-error': errors.password, 'is-valid': form.password && !errors.password }">
        <label for="password">Password</label>
        <div class="input-wrap">
          <span class="input-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0110 0v4"/>
            </svg>
          </span>
          <input 
            :type="showPassword ? 'text' : 'password'" 
            id="password" 
            v-model="form.password"
            placeholder="Enter your password" 
            autocomplete="current-password"
            :disabled="loading"
            @input="clearFieldError('password')"
          />
          <button 
            type="button" 
            class="toggle-pw" 
            @click="togglePasswordVisibility"
            :disabled="loading"
            aria-label="Toggle password visibility"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path v-if="!showPassword" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <path v-if="!showPassword" d="M12 9a3 3 0 100 6 3 3 0 000-6z"/>
              <path v-if="showPassword" d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
              <line x1="1" y1="1" x2="23" y2="23" v-if="showPassword"/>
            </svg>
          </button>
        </div>
        <transition name="error-fade">
          <span v-if="errors.password" class="field-error">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <circle cx="12" cy="16" r="0.5" fill="currentColor"/>
            </svg>
            {{ errors.password[0] }}
          </span>
        </transition>
      </div>

      <!-- Form Utils -->
      <div class="form-utils">
        <label class="remember">
          <input type="checkbox" v-model="form.remember" :disabled="loading" />
          Remember me
        </label>
        <a href="#" class="forgot">Forgot password?</a>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn-login" :disabled="loading" :class="{ 'btn-loading': loading }">
        <div v-if="loading" class="btn-loader">
          <div class="spinner"></div>
        </div>
        <span id="btnText">{{ loading ? 'Signing in...' : 'Sign In' }}</span>
        <svg v-if="!loading" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="btn-arrow">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </button>
    </form>

    <div class="account-notice">
      No account yet? <a href="#">Request access from IT Admin</a>
    </div>

    <div class="security-badge">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
      <div class="security-badge-text">
        This portal is for authorized DENR Region XI personnel only. All access is logged and monitored.
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'LoginForm',
  data() {
    return {
      form: {
        email: '',
        password: '',
        remember: false
      },
      errors: {},      // Field validation errors
      generalError: '', // General error message (e.g., invalid credentials)
      loading: false,
      showPassword: false
    }
  },
  mounted() {
    // Set up CSRF token for Laravel if using session-based auth
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = token
    }
  },
  methods: {
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword
    },
    
    clearFieldError(field) {
      // Clear specific field error when user starts typing
      if (this.errors[field]) {
        this.$delete(this.errors, field)
      }
    },
    
    async handleLogin() {
      // Reset previous errors
      this.errors = {}
      this.generalError = ''
      
      // Basic client-side validation before sending (optional but improves UX)
      if (!this.form.email) {
        this.errors.email = ['Please enter your email address.']
        return
      }
      if (!this.form.password) {
        this.errors.password = ['Password is required.']
        return
      }
      if (this.form.password.length < 8) {
        this.errors.password = ['Password must be at least 8 characters.']
        return
      }
      
      this.loading = true
      
      // Simulate login delay with setTimeout (remove in production)
      await new Promise(resolve => setTimeout(resolve, 1500))
      
      try {
        // Adjust the URL to match your Laravel route
        const response = await axios.post('/dts_denr/api/login', this.form)
        
        if (response.data.success) {
          // Successful login - redirect as per backend response
          window.location.href = response.data.redirect
        } else {
          // Should not happen for 200 status, but handle just in case
          this.generalError = response.data.message || 'Login failed. Please try again.'
        }
      } catch (error) {
        if (error.response) {
          const { status, data } = error.response
          
          if (status === 422 && data.errors) {
            // Validation errors from the backend
            this.errors = data.errors
            // Optionally show first error in general alert
            const firstError = Object.values(data.errors)[0]?.[0]
            if (firstError) {
              this.generalError = firstError
            }
          } else if (status === 401 && data.message) {
            // Invalid credentials
            this.generalError = data.message
          } else {
            // Other server errors
            this.generalError = 'An unexpected error occurred. Please try again.'
          }
        } else if (error.request) {
          // Network error
          this.generalError = 'Network error. Please check your connection.'
        } else {
          this.generalError = 'An error occurred. Please try again.'
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
/* ========== ERROR HANDLING STYLES ========== */

/* General Error Alert */
.error-msg {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  margin-bottom: 20px;
  background: #FEF2F2;
  border: 1px solid #FECACA;
  border-radius: 10px;
  color: #991B1B;
  font-size: 13px;
  line-height: 1.5;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}

.error-msg::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: #EF4444;
  border-radius: 3px 0 0 3px;
}

.error-icon-wrap {
  flex-shrink: 0;
  margin-top: 1px;
  color: #EF4444;
}

.error-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.error-title {
  font-weight: 600;
  font-size: 14px;
  color: #991B1B;
}

.error-message {
  color: #B91C1C;
}

.error-dismiss {
  flex-shrink: 0;
  background: none;
  border: none;
  padding: 4px;
  cursor: pointer;
  color: #991B1B;
  opacity: 0.5;
  transition: opacity 0.2s;
  margin-top: -2px;
}

.error-dismiss:hover {
  opacity: 1;
}

/* Error Slide Animation */
.error-slide-enter-active {
  animation: slideIn 0.3s ease-out;
}

.error-slide-leave-active {
  animation: slideOut 0.2s ease-in;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideOut {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-10px);
  }
}

/* Field Error State */
.field.has-error .input-wrap {
  border-color: #EF4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
  animation: shake 0.4s ease-in-out;
}

.field.has-error label {
  color: #DC2626;
}

.field.has-error .input-icon {
  color: #EF4444;
}

/* Field Valid State */
.field.is-valid .input-wrap {
  border-color: #10B981;
}

.field.is-valid .input-icon {
  color: #10B981;
}

/* Validation Icons */
.validation-icon {
  position: absolute;
  right: 40px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  pointer-events: none;
  animation: iconPop 0.3s ease-out;
}

.toggle-pw ~ .validation-icon {
  right: 70px;
}

@keyframes iconPop {
  from {
    opacity: 0;
    transform: translateY(-50%) scale(0.5);
  }
  to {
    opacity: 1;
    transform: translateY(-50%) scale(1);
  }
}

/* Field Error Message */
.field-error {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
  color: #DC2626;
  font-size: 12px;
  font-weight: 500;
  line-height: 1.4;
}

.field-error svg {
  flex-shrink: 0;
}

/* Error Message Animation */
.error-fade-enter-active {
  animation: fadeInSlide 0.3s ease-out;
}

.error-fade-leave-active {
  animation: fadeOutSlide 0.2s ease-in;
}

@keyframes fadeInSlide {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeOutSlide {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(-5px);
  }
}

/* Shake Animation for Invalid Fields */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  50% { transform: translateX(5px); }
  75% { transform: translateX(-5px); }
}

/* ========== ENHANCED LOADING STYLES ========== */

.btn-login {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px 24px;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  overflow: hidden;
}

.btn-login:hover:not(:disabled) {
 
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
}

.btn-login:active:not(:disabled) {
  transform: translateY(0);
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-login.btn-loading {
  background: linear-gradient(135deg, #374151 0%, #4B5563 100%);
}

.btn-loader {
  display: flex;
  align-items: center;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

.btn-arrow {
  transition: transform 0.3s ease;
}

.btn-login:hover:not(:disabled) .btn-arrow {
  transform: translateX(3px);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ========== INPUT STYLES (Keep existing styles, enhanced for errors) ========== */

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  transition: all 0.3s ease;
  background: white;
}

.input-wrap:focus-within {
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.field.has-error .input-wrap:focus-within {
  border-color: #EF4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.field.is-valid .input-wrap:focus-within {
  border-color: #10B981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.field input {
  flex: 1;
  padding: 12px 16px;
  padding-left: 42px;
  border: none;
  outline: none;
  font-size: 14px;
  background: transparent;
  color: #1F2937;
}

.field input::placeholder {
  color: #9CA3AF;
}

.field input:disabled {
  background: #F9FAFB;
  color: #6B7280;
}

.input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #6B7280;
  transition: color 0.3s ease;
  pointer-events: none;
}

.toggle-pw {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 4px;
  color: #6B7280;
  cursor: pointer;
  transition: color 0.2s;
  z-index: 1;
}

.toggle-pw:hover:not(:disabled) {
  color: #374151;
}

/* Adjust input padding when validation icon is visible */
.field.has-error input,
.field.is-valid input {
  padding-right: 70px;
}

/* ========== RESPONSIVE STYLES ========== */

@media (max-width: 640px) {
  .error-msg {
    padding: 12px;
    font-size: 12px;
  }
  
  .field-error {
    font-size: 11px;
  }
  
  .btn-login {
    padding: 12px 20px;
    font-size: 14px;
  }
}
</style>