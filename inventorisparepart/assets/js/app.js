document.addEventListener('DOMContentLoaded', () => {
    /* ===== THEME TOGGLE ===== */
    const themeBtn = document.getElementById('theme-toggle');
    const html = document.documentElement;
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    html.setAttribute('data-theme', saved || (prefersDark ? 'dark' : 'light'));
    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });
    }

    /* ===== SIDEBAR TOGGLE ===== */
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const hamburger = document.getElementById('hamburgerBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');

    function toggleSidebar() {
        const isOpen = sidebar?.classList.contains('open');
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (hamburger) {
        hamburger.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Close sidebar on desktop resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) closeSidebar();
    });

    /* ===== MODAL HELPERS ===== */
    window.openModal = function(id) {
        document.getElementById(id)?.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    window.closeModal = function(id) {
        document.getElementById(id)?.classList.remove('active');
        document.body.style.overflow = '';
    };

    // Close modal on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) {
                backdrop.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    /* ===== AUTO-DISMISS ALERTS ===== */
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3500);
    });

    /* ===== ANIMATED COUNTER ===== */
    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseInt(el.getAttribute('data-count'), 10);
        if (isNaN(target)) return;
        let current = 0;
        const step = Math.ceil(target / 40);
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString('id-ID');
            if (current >= target) clearInterval(timer);
        }, 30);
    });

    /* ===== ANIMATED BARS ===== */
    document.querySelectorAll('.bar-fill[data-height]').forEach(bar => {
        const h = bar.getAttribute('data-height');
        setTimeout(() => { bar.style.height = h; }, 100);
    });

    /* ===== SEARCH FILTER TABLE ===== */
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    /* ===== CONFIRM DELETE ===== */
    window.confirmDelete = function(formId) {
        if (confirm('Yakin ingin menghapus data ini?')) {
            document.getElementById(formId)?.submit();
        }
    };

    /* ===== PRINT REPORT ===== */
    window.printReport = function() { window.print(); };

    /* ===== EDIT MODAL FILL ===== */
    window.fillEditModal = function(fields) {
        Object.entries(fields).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        });
    };
});
