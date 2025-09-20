// Attendance System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Password visibility toggle
    const togglePassword = (button) => {
        const input = button.previousElementSibling;
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };

    // Add password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', () => togglePassword(button));
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Password confirmation validation
            const passwordFields = this.querySelectorAll('input[type="password"]');
            if (passwordFields.length >= 2) {
                const password = passwordFields[0];
                const confirmPassword = passwordFields[1];

                if (password.value !== confirmPassword.value) {
                    valid = false;
                    confirmPassword.classList.add('is-invalid');
                    confirmPassword.nextElementSibling?.textContent = 'Passwords do not match';
                }
            }

            if (!valid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = this.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });

    // Auto-close modals on successful form submission
    document.addEventListener('ajaxComplete', function() {
        document.querySelectorAll('.modal').forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    });

    // Table sorting functionality
    document.querySelectorAll('table th[data-sort]').forEach(header => {
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const column = this.cellIndex;
            const isNumeric = this.dataset.sort === 'numeric';
            const isAsc = this.classList.contains('sort-asc');

            // Remove existing sort classes
            table.querySelectorAll('th').forEach(th => {
                th.classList.remove('sort-asc', 'sort-desc');
            });

            // Toggle sort direction
            this.classList.toggle('sort-asc', !isAsc);
            this.classList.toggle('sort-desc', isAsc);

            // Sort table
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                let aValue = a.cells[column].textContent.trim();
                let bValue = b.cells[column].textContent.trim();

                if (isNumeric) {
                    aValue = parseFloat(aValue) || 0;
                    bValue = parseFloat(bValue) || 0;
                }

                if (isAsc) {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });

            // Remove existing rows and append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // Auto-refresh dashboard every 5 minutes
    if (document.querySelector('.dashboard-refresh')) {
        setInterval(() => {
            window.location.reload();
        }, 300000); // 5 minutes
    }

    // Print functionality
    document.querySelectorAll('.btn-print').forEach(button => {
        button.addEventListener('click', () => {
            window.print();
        });
    });

    // Export functionality
    document.querySelectorAll('.btn-export').forEach(button => {
        button.addEventListener('click', function() {
            const format = this.dataset.format || 'csv';
            const tableId = this.dataset.table;
            const table = document.getElementById(tableId);

            if (table) {
                exportTable(table, format);
            }
        });
    });

    // Responsive menu toggle for mobile
    const navbarToggler = document.querySelector('.navbar-toggler');
    if (navbarToggler) {
        navbarToggler.addEventListener('click', () => {
            document.body.classList.toggle('menu-open');
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Real-time clock
    function updateClock() {
        const clockElements = document.querySelectorAll('.real-time-clock');
        if (clockElements.length > 0) {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            clockElements.forEach(el => {
                el.textContent = timeString;
            });
        }
    }

    setInterval(updateClock, 1000);
    updateClock();
});

// Table export function
function exportTable(table, format) {
    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');

        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }

        csv.push(row.join(','));
    }

    const csvString = csv.join('\n');
    const filename = 'export_' + new Date().toISOString().slice(0, 10) + '.csv';

    const link = document.createElement('a');
    link.style.display = 'none';
    link.setAttribute('target', '_blank');
    link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvString));
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Date range validation
function validateDateRange(startDateId, endDateId) {
    const startDate = new Date(document.getElementById(startDateId).value);
    const endDate = new Date(document.getElementById(endDateId).value);

    if (startDate > endDate) {
        alert('End date must be after start date');
        return false;
    }
    return true;
}

// Character counter for textareas
function setupCharacterCounters() {
    document.querySelectorAll('textarea[data-maxlength]').forEach(textarea => {
        const maxLength = textarea.dataset.maxlength;
        const counter = document.createElement('div');
        counter.className = 'form-text text-end character-counter';
        textarea.parentNode.appendChild(counter);

        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.style.color = remaining < 20 ? '#dc3545' : '#6c757d';
        }

        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
}

// Initialize character counters when DOM is loaded
document.addEventListener('DOMContentLoaded', setupCharacterCounters);
