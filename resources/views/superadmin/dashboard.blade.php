@extends('layout.template')
@section('content')

 <div class="page active" id="page-overview">
                <div>
                    <div class="section-header">
                        <span class="section-title">Key Metrics — 2025</span>
                        <a class="section-link" href="#">May 2025 ▾</a>
                    </div>
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22V12"/><path d="M12 12C12 7 7 3 3 3c0 4 2 8 9 9"/><path d="M12 12c0-5 5-9 9-9c0 4-2 8-9 9"/></svg></div>
                                <div class="stat-label">Forest Cover (ha)</div>
                                <div class="stat-value">6.84M</div>
                                <div class="stat-footer"><span class="stat-delta delta-up">↑ 2.1%</span> vs last year</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                                <div class="stat-label">Permits Issued</div>
                                <div class="stat-value">1,482</div>
                                <div class="stat-footer"><span class="stat-delta delta-up">↑ 9.1%</span> this month</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                                <div class="stat-label">Violations Filed</div>
                                <div class="stat-value">247</div>
                                <div class="stat-footer"><span class="stat-delta delta-down">↑ 4.3%</span> this month</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stat-card">
                                <div class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
                                <div class="stat-label">Active Field Officers</div>
                                <div class="stat-value">3,210</div>
                                <div class="stat-footer"><span class="stat-delta delta-up">↑ 1.8%</span> deployed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold" style="font-size:13.5px;color:var(--text)">Permit Applications Over Time</span>
                                <span style="font-family:var(--font-mono);font-size:10px;padding:3px 9px;border-radius:20px;border:1px solid var(--border);color:var(--muted);background:var(--input-bg)">Monthly</span>
                            </div>
                            <div class="chart-placeholder">
                                <div class="chart-legend">
                                    <div class="legend-item"><div class="legend-dot" style="background:var(--green)"></div>Approved</div>
                                    <div class="legend-item"><div class="legend-dot" style="background:var(--blue)"></div>Pending</div>
                                </div>
                                <div class="chart-area">
                                    <svg class="chart-svg" viewBox="0 0 560 185" preserveAspectRatio="none">
                                        <line class="chart-grid" x1="0" y1="37" x2="560" y2="37" />
                                        <line class="chart-grid" x1="0" y1="74" x2="560" y2="74" />
                                        <line class="chart-grid" x1="0" y1="111" x2="560" y2="111" />
                                        <line class="chart-grid" x1="0" y1="148" x2="560" y2="148" />
                                        <polygon fill="rgba(45,158,74,.1)" points="0,132 70,112 140,122 210,80 280,88 350,54 420,63 490,37 560,51 560,185 0,185" />
                                        <polygon fill="rgba(26,86,219,.08)" points="0,152 70,147 140,142 210,138 280,132 350,122 420,128 490,112 560,118 560,185 0,185" />
                                        <polyline fill="none" stroke="var(--green)" stroke-width="2.2" stroke-linecap="round" points="0,132 70,112 140,122 210,80 280,88 350,54 420,63 490,37 560,51" />
                                        <polyline fill="none" stroke="var(--blue)" stroke-width="2.2" stroke-linecap="round" points="0,152 70,147 140,142 210,138 280,132 350,122 420,128 490,112 560,118" />
                                    </svg>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-top:7px;font-family:var(--font-mono);font-size:10px;color:var(--muted)">
                                    <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span><span>Jul</span><span>Aug</span><span>Sep</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold" style="font-size:13.5px;color:var(--text)">Land Classification</span>
                                <span style="font-family:var(--font-mono);font-size:10px;padding:3px 9px;border-radius:20px;border:1px solid var(--border);color:var(--muted);background:var(--input-bg)">2025</span>
                            </div>
                            <div class="donut-wrap">
                                <svg class="donut-svg" viewBox="0 0 130 130">
                                    <circle cx="65" cy="65" r="50" fill="none" stroke="var(--border)" stroke-width="20" />
                                    <circle cx="65" cy="65" r="50" fill="none" stroke="#2d9e4a" stroke-width="20" stroke-dasharray="120 194" stroke-dashoffset="0" transform="rotate(-90 65 65)" />
                                    <circle cx="65" cy="65" r="50" fill="none" stroke="#1a56db" stroke-width="20" stroke-dasharray="78 236" stroke-dashoffset="-120" transform="rotate(-90 65 65)" />
                                    <circle cx="65" cy="65" r="50" fill="none" stroke="#3dbf5e" stroke-width="20" stroke-dasharray="63 251" stroke-dashoffset="-198" transform="rotate(-90 65 65)" />
                                    <circle cx="65" cy="65" r="50" fill="none" stroke="#3b82f6" stroke-width="20" stroke-dasharray="53 261" stroke-dashoffset="-261" transform="rotate(-90 65 65)" />
                                    <text x="65" y="61" text-anchor="middle" font-family="Plus Jakarta Sans" font-size="17" font-weight="800" fill="var(--text)">68%</text>
                                    <text x="65" y="75" text-anchor="middle" font-family="JetBrains Mono" font-size="9" fill="var(--muted)">forest land</text>
                                </svg>
                                <div class="donut-stats">
                                    <div class="donut-row"><div class="donut-swatch" style="background:#2d9e4a"></div><span class="donut-label">Timberland</span><span class="donut-pct">38%</span></div>
                                    <div class="donut-row"><div class="donut-swatch" style="background:#1a56db"></div><span class="donut-label">Protected</span><span class="donut-pct">25%</span></div>
                                    <div class="donut-row"><div class="donut-swatch" style="background:#3dbf5e"></div><span class="donut-label">Alienable</span><span class="donut-pct">20%</span></div>
                                    <div class="donut-row"><div class="donut-swatch" style="background:#3b82f6"></div><span class="donut-label">Mineral</span><span class="donut-pct">17%</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold" style="font-size:13.5px;color:var(--text)">Violations by Category</span>
                                <span style="font-family:var(--font-mono);font-size:10px;padding:3px 9px;border-radius:20px;border:1px solid var(--border);color:var(--muted);background:var(--input-bg)">This Month</span>
                            </div>
                            <div class="chart-placeholder">
                                <svg viewBox="0 0 480 160" width="100%" style="display:block;">
                                    <line x1="40" y1="10" x2="480" y2="10" stroke="var(--border)" stroke-width="1" />
                                    <line x1="40" y1="50" x2="480" y2="50" stroke="var(--border)" stroke-width="1" />
                                    <line x1="40" y1="90" x2="480" y2="90" stroke="var(--border)" stroke-width="1" />
                                    <line x1="40" y1="130" x2="480" y2="130" stroke="var(--border)" stroke-width="1" />
                                    <rect x="55" y="30" width="40" height="100" rx="5" fill="#2d9e4a" opacity=".85" />
                                    <rect x="115" y="55" width="40" height="75" rx="5" fill="#1a56db" opacity=".85" />
                                    <rect x="175" y="15" width="40" height="115" rx="5" fill="#3dbf5e" opacity=".85" />
                                    <rect x="235" y="75" width="40" height="55" rx="5" fill="#3b82f6" opacity=".85" />
                                    <rect x="295" y="42" width="40" height="88" rx="5" fill="#1a56db" opacity=".85" />
                                    <rect x="355" y="62" width="40" height="68" rx="5" fill="#2d9e4a" opacity=".85" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold" style="font-size:13.5px;color:var(--text)">Recent Activity</span>
                                <a class="section-link" href="#">View all</a>
                            </div>
                            <div class="activity-feed">
                                <div class="activity-item">
                                    <div class="activity-icon act-green"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
                                    <div class="activity-body"><div class="activity-text"><strong>Permit #FP-2210</strong> approved — Timber License issued.</div><div class="activity-time">5 min ago</div></div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon act-blue"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
                                    <div class="activity-body"><div class="activity-text">Field survey submitted for <strong>Davao Region</strong> biodiversity index.</div><div class="activity-time">22 min ago</div></div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon act-red"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                                    <div class="activity-body"><div class="activity-text">Illegal logging alert raised in <strong>Mt. Apo Protected Area</strong>.</div><div class="activity-time">1 hr ago</div></div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon act-green"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22V12"/><path d="M12 12C12 7 7 3 3 3c0 4 2 8 9 9"/><path d="M12 12c0-5 5-9 9-9c0 4-2 8-9 9"/></svg></div>
                                    <div class="activity-body"><div class="activity-text">Reforestation of <strong>420 ha</strong> completed in Bukidnon.</div><div class="activity-time">4 hr ago</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold" style="font-size:13.5px;color:var(--text)">Recent Permit Applications</span>
                        <div class="d-flex align-items-center" style="gap:8px">
                            <span style="font-family:var(--font-mono);font-size:10px;padding:3px 9px;border-radius:20px;border:1px solid var(--border);color:var(--muted);background:var(--input-bg)">Showing 8 of 1,482</span>
                            <a class="section-link" href="#">View all →</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead><tr><th>Reference</th><th>Applicant</th><th>Type</th><th>Area (ha)</th><th>Status</th><th>Progress</th><th>Filed</th></tr></thead>
                            <tbody>
                                <tr><td><span class="order-id">#FP-2210</span></td><td><div class="customer"><div class="customer-avatar" style="background:linear-gradient(135deg,#2d9e4a,#1a56db)">JL</div><div><div class="customer-name">Juan Lim</div><div class="customer-email">j.lim@corp.ph</div></div></div></td><td>Timber License</td><td><span class="amount">420</span></td><td><span class="badge badge-success">Approved</span></td><td><div class="progress-sm"><div class="progress-sm-fill" style="width:100%;background:#2d9e4a"></div></div></td><td style="font-family:var(--font-mono);font-size:11px;color:var(--muted)">May 23, 2025</td></tr>
                                <tr><td><span class="order-id">#MP-1834</span></td><td><div class="customer"><div class="customer-avatar" style="background:linear-gradient(135deg,#1a56db,#3b82f6)">MS</div><div><div class="customer-name">Maria Santos</div><div class="customer-email">m.santos@mine.ph</div></div></div></td><td>Mineral Exploration</td><td><span class="amount">80</span></td><td><span class="badge badge-info">Under Review</span></td><td><div class="progress-sm"><div class="progress-sm-fill" style="width:65%;background:#1a56db"></div></div></td><td style="font-family:var(--font-mono);font-size:11px;color:var(--muted)">May 23, 2025</td></tr>
                                <tr><td><span class="order-id">#VF-0441</span></td><td><div class="customer"><div class="customer-avatar" style="background:linear-gradient(135deg,#ef4444,#1a56db)">BM</div><div><div class="customer-name">Bong Malabanan</div><div class="customer-email">bong.m@ranch.ph</div></div></div></td><td>Violation — Logging</td><td><span class="amount">—</span></td><td><span class="badge badge-danger">Filed</span></td><td><div class="progress-sm"><div class="progress-sm-fill" style="width:20%;background:#ef4444"></div></div></td><td style="font-family:var(--font-mono);font-size:11px;color:var(--muted)">May 21, 2025</td></tr>
                                <tr><td><span class="order-id">#CP-0310</span></td><td><div class="customer"><div class="customer-avatar" style="background:linear-gradient(135deg,#1a56db,#2d9e4a)">LM</div><div><div class="customer-name">Liza Medina</div><div class="customer-email">l.medina@city.ph</div></div></div></td><td>Carbon Credit</td><td><span class="amount">1,200</span></td><td><span class="badge badge-warning">Pending</span></td><td><div class="progress-sm"><div class="progress-sm-fill" style="width:55%;background:#1a56db"></div></div></td><td style="font-family:var(--font-mono);font-size:11px;color:var(--muted)">May 21, 2025</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
@endsection