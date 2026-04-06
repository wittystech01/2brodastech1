// GadgetZone - Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // SIDEBAR TOGGLE (mobile)
    // ============================================================
    const sidebarToggle = document.querySelector('.admin-sidebar-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    const adminMain = document.querySelector('.admin-main');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            adminSidebar && adminSidebar.classList.toggle('collapsed');
            adminMain && adminMain.classList.toggle('expanded');
        });
    }

    // ============================================================
    // ACTIVE NAV LINK HIGHLIGHTING
    // ============================================================
    const currentPath = window.location.pathname;
    document.querySelectorAll('.admin-nav-link').forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });

    // ============================================================
    // ADMIN TABS
    // ============================================================
    document.querySelectorAll('.admin-tab[data-tab]').forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.dataset.tab;
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.admin-tab-panel').forEach(p => p.style.display = 'none');
            this.classList.add('active');
            const panel = document.querySelector(`[data-tab-panel="${target}"]`);
            if (panel) panel.style.display = 'block';
        });
    });

    // Activate first tab
    const firstTab = document.querySelector('.admin-tab[data-tab]');
    if (firstTab) firstTab.click();

    // ============================================================
    // IMAGE PREVIEW ON FILE INPUT
    // ============================================================
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function () {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            if (!preview || !this.files[0]) return;
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; };
            reader.readAsDataURL(this.files[0]);
        });
    });

    // ============================================================
    // CONFIRM DELETE
    // ============================================================
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // ============================================================
    // BULK SELECT / DESELECT
    // ============================================================
    const selectAll = document.querySelector('#select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkActionsBar();
        });

        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkActionsBar);
        });
    }

    function updateBulkActionsBar() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const bar = document.querySelector('.bulk-actions-bar');
        const countEl = document.querySelector('.bulk-selected-count');
        if (bar) bar.style.display = checked.length > 0 ? 'flex' : 'none';
        if (countEl) countEl.textContent = checked.length;
    }

    // ============================================================
    // BULK ACTION FORM SUBMIT
    // ============================================================
    const bulkForm = document.querySelector('#bulk-action-form');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            const action = document.querySelector('#bulk-action-select')?.value;
            const checked = document.querySelectorAll('.row-checkbox:checked');
            if (!action || checked.length === 0) {
                e.preventDefault();
                alert('Please select items and an action.');
                return;
            }
            if (action === 'delete' && !confirm(`Delete ${checked.length} item(s)? This cannot be undone.`)) {
                e.preventDefault();
            }
        });
    }

    // ============================================================
    // INLINE STATUS TOGGLE (AJAX)
    // ============================================================
    document.querySelectorAll('[data-toggle-status]').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const id = this.dataset.id;
            const type = this.dataset.type;
            const status = this.checked ? 'active' : 'inactive';

            fetch('ajax/toggle_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, type, status })
            })
                .then(r => r.json())
                .then(data => {
                    adminToast(data.success ? 'Status updated.' : 'Update failed.', data.success ? 'success' : 'error');
                })
                .catch(() => adminToast('Network error.', 'error'));
        });
    });

    // ============================================================
    // SEARCH TABLE (client-side filter)
    // ============================================================
    const tableSearch = document.querySelector('[data-table-search]');
    if (tableSearch) {
        const targetTable = document.querySelector(tableSearch.dataset.tableSearch || '.admin-table tbody');
        tableSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            if (!targetTable) return;
            targetTable.querySelectorAll('tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ============================================================
    // ADMIN TOAST NOTIFICATIONS
    // ============================================================
    window.adminToast = function (message, type = 'success') {
        const colors = { success: '#27ae60', error: '#e74c3c', info: '#4a90d9', warning: '#f0b429' };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position:fixed; bottom:24px; right:24px; padding:14px 24px;
            background:${colors[type] || colors.success}; color:#fff;
            border-radius:8px; font-weight:600; font-size:0.875rem;
            z-index:9999; box-shadow:0 4px 16px rgba(0,0,0,0.15);
            opacity:0; transform:translateY(8px); transition:all 0.3s ease;`;
        toast.textContent = message;
        document.body.appendChild(toast);
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });
        });
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(8px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // ============================================================
    // SALES CHART (Chart.js — loaded if canvas#salesChart exists)
    // ============================================================
    const salesCanvas = document.getElementById('salesChart');
    if (salesCanvas && typeof Chart !== 'undefined') {
        fetch('ajax/chart_data.php?type=sales')
            .then(r => r.json())
            .then(data => {
                new Chart(salesCanvas, {
                    type: 'line',
                    data: {
                        labels: data.labels || [],
                        datasets: [{
                            label: 'Revenue (₦)',
                            data: data.values || [],
                            borderColor: '#1e3a5f',
                            backgroundColor: 'rgba(30,58,95,0.08)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#1e3a5f'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            })
            .catch(() => {});
    }

    // ============================================================
    // DATE RANGE PICKER INIT (flatpickr — if loaded)
    // ============================================================
    if (typeof flatpickr !== 'undefined') {
        document.querySelectorAll('[data-datepicker]').forEach(el => {
            flatpickr(el, { dateFormat: 'Y-m-d' });
        });
    }

    // ============================================================
    // AUTO-DISMISS FLASH ALERTS
    // ============================================================
    document.querySelectorAll('.admin-alert[data-auto-dismiss]').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // ============================================================
    // RICH TEXT EDITOR INIT (if TinyMCE/Quill is loaded)
    // ============================================================
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.rich-editor',
            height: 300,
            menubar: false,
            plugins: ['lists', 'link', 'image', 'code'],
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
        });
    }

});
