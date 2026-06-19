<aside class="sidebar" id="sidebar">

  <div class="logo">
    <div class="logo-emblem">
      <img src="{{ asset('storage/app/public/logo/denr1.png') }}" alt="" height="50">
    </div>
    <div class="logo-text">
      <div class="l1">DENR XI</div>
      <div class="l2">Document Tracking System</div>
    </div>
  </div>
   @if(Auth::user()->role==='Super Admin')      
    <nav>
      <div class="nav-section-label">Main</div>

      <a class="nav-item active" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>

      <div class="nav-group">
        <div class="nav-group-trigger" onclick="toggleDropdown(this)">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Documents
          <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="nav-submenu">
          <a class="nav-sub-item active" href="#">Incoming Documents</a>
          <a class="nav-sub-item" href="#">Internal Routing</a>
          <a class="nav-sub-item" href="#">Archived</a>
        </div>
      </div>

      <div class="nav-section-label" style="margin-top:10px">Administration</div>

      <div class="nav-group">
        <div class="nav-group-trigger" onclick="toggleDropdown(this)">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="9" y1="6" x2="15" y2="6"/><line x1="9" y1="10" x2="15" y2="10"/><line x1="9" y1="14" x2="15" y2="14"/><line x1="9" y1="18" x2="13" y2="18"/></svg>
          Office
          <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="nav-submenu">
          <a class="nav-sub-item" href="{{ route('main.office') }}">Main Office</a>
          <a class="nav-sub-item" href="{{ route('sub.office') }}">Sub Office</a>
        </div>
      </div>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        User Management
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Reports and Analytics
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Chats
      </a>
    </nav>
@endif
@if(Auth::user()->role==='Admin')      
    <nav>
      <div class="nav-section-label">Main</div>

      <a class="nav-item active" href="{{route('admin.dashboard')}}">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>

      <div class="nav-group">
        <div class="nav-group-trigger" onclick="toggleDropdown(this)">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Documents
          <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="nav-submenu">
          <a class="nav-sub-item active" href="{{ route('admin.incoming.documents') }}">Incoming Documents</a>
          <a class="nav-sub-item" href="#">Internal Routing</a>
          <a class="nav-sub-item" href="#">Archived</a>
        </div>
      </div>
  <a class="nav-item" href="">
    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
    </svg>
    My Document
</a>

      <div class="nav-section-label" style="margin-top:10px">Administration</div>

       <a class="nav-item" href="{{ route('admin.document.type') }}">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        Document Type
      </a>

   

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Reports and Analytics
      </a>

      <a class="nav-item" href="#">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Chats
      </a>
    </nav>
@endif

@if(Auth::user()->role==='Secretariat')   
<nav>
    <div class="nav-section-label">Main</div>

    <a class="nav-item active" href="">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Dashboard
    </a>

    <a class="nav-item" href="{{route('incoming.secretariat')}}">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
        </svg>
        Incoming Documents
    </a>

    <a class="nav-item" href="">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
        </svg>
        My Documents
    </a>

    <a class="nav-item" href="">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        Chats
    </a>

    <div class="nav-section-label" style="margin-top:10px">Administration</div>

    <a class="nav-item" href="">
        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        Reports
    </a>
</nav>
@endif
 
  <div class="sidebar-footer" style="text-align: center; padding: 16px 10px; font-size: 10px; font-family: var(--font-mono); color: var(--sb-muted); letter-spacing: 0.02em; line-height: 1.5;">
    Developed By:<br>
    <strong style="color: var(--green); font-size: 11px;">Planning and Management Division</strong><br>
    Regional ICT Unit XI
  </div>
</aside>