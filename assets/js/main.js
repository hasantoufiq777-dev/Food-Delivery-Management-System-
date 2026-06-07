/**
 * FlameRoute — Food Delivery Management System
 * Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ─── Mobile Menu Toggle ──────────────────────────────────
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }

    // ─── Flash Message Auto-dismiss ──────────────────────────
    const flashMsg = document.getElementById('flashMessage');
    if (flashMsg) {
        setTimeout(function() {
            flashMsg.style.opacity = '0';
            flashMsg.style.transform = 'translateY(-10px)';
            setTimeout(function() { flashMsg.remove(); }, 300);
        }, 4000);
    }

    // ─── Delete Confirmation Modal ───────────────────────────
    document.querySelectorAll('[data-delete]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-delete');
            const name = this.getAttribute('data-name') || 'this item';
            openDeleteModal(url, name);
        });
    });

    // ─── Table Row Hover Effect ──────────────────────────────
    document.querySelectorAll('.data-table tbody tr').forEach(function(row) {
        row.style.cursor = row.querySelector('a') ? 'pointer' : 'default';
    });

    // ─── Smooth Number Counter Animation ─────────────────────
    document.querySelectorAll('.stat-value').forEach(function(el) {
        const value = el.textContent;
        // Only animate pure numbers
        if (/^\$?[\d,]+\.?\d*$/.test(value.trim())) {
            const isPrice = value.trim().startsWith('$');
            const num = parseFloat(value.replace(/[$,]/g, ''));
            const duration = 1000;
            const start = performance.now();
            const initial = 0;
            
            function easeOutQuart(t) {
                return 1 - Math.pow(1 - t, 4);
            }
            
            function animate(now) {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const current = initial + (num - initial) * easeOutQuart(progress);
                
                if (isPrice) {
                    el.textContent = '$' + current.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                } else {
                    el.textContent = Math.round(current).toLocaleString();
                }
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    el.textContent = value; // Ensure exact original value
                }
            }
            
            // Use IntersectionObserver for visible animation
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        requestAnimationFrame(animate);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.3 });
            
            observer.observe(el);
        }
    });

    // ─── Form Validation Visual Feedback ─────────────────────
    document.querySelectorAll('.form-input, .form-textarea').forEach(function(input) {
        input.addEventListener('invalid', function() {
            this.style.borderColor = '#FC5C65';
        });
        input.addEventListener('input', function() {
            this.style.borderColor = '';
        });
    });
});

// ─── Modal Functions ─────────────────────────────────────────
function openDeleteModal(url, name) {
    const modal = document.getElementById('deleteModal');
    const message = document.getElementById('deleteModalMessage');
    const confirmBtn = document.getElementById('deleteConfirmBtn');
    
    message.textContent = 'Are you sure you want to delete "' + name + '"? This action cannot be undone.';
    confirmBtn.href = url;
    modal.classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function openModal(id) {
    document.getElementById(id).classList.add('active');
}

// Close modal on background click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay') && e.target.classList.contains('active')) {
        e.target.classList.remove('active');
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
        });
    }
});

// ─── Cart Functions ──────────────────────────────────────────
function updateCartQuantity(itemId, action) {
    const baseUrl = window.BASE_URL || '/food delivery/';
    window.location.href = baseUrl + 'customer/cart.php?action=' + action + '&item_id=' + itemId;
}

function addToCart(itemId, restaurantId) {
    const baseUrl = window.BASE_URL || '/food delivery/';
    window.location.href = baseUrl + 'customer/browse.php?add_to_cart=' + itemId + '&restaurant_id=' + restaurantId;
}

// ─── Filter/Search Functions ─────────────────────────────────
function applyFilter(key, value) {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set(key, value);
    } else {
        url.searchParams.delete(key);
    }
    url.searchParams.delete('page'); // Reset to page 1
    window.location.href = url.toString();
}

// ─── CRUD Form Toggle (inline forms) ────────────────────────
function toggleForm(id) {
    const form = document.getElementById(id);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
}
