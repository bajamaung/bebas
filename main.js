// assets/js/main.js

// Auto logout jika idle selama 30 menit
let idleTimer = null;
let idleWait = 30 * 60 * 1000; // 30 menit

function resetIdleTimer() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(() => {
        logoutDueToInactivity();
    }, idleWait);
}

document.addEventListener('mousemove', resetIdleTimer);
document.addEventListener('keypress', resetIdleTimer);
document.addEventListener('click', resetIdleTimer);

function logoutDueToInactivity() {
    Swal.fire({
        title: 'Session Expired',
        text: 'Your session has expired due to inactivity. Please login again.',
        icon: 'warning',
        confirmButtonText: 'Login'
    }).then(() => {
        window.location.href = BASE_URL + 'logout.php';
    });
}

// Toast Notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 100);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Confirm Delete
function confirmDelete(itemName, callback) {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: 'Data ' + itemName + ' akan dihapus secara permanen',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof callback === 'function') {
                callback();
            }
        }
    });
}

// Format Currency
function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
}

// Format Date
function formatDate(date) {
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(date));
}

// Append BASE_URL to links
document.addEventListener('DOMContentLoaded', function() {
    // Initial idle timer
    resetIdleTimer();
});

// Export to Excel
function exportToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    const csv = [];
    
    // Get headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));
    
    // Get rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.trim() + '"');
        });
        csv.push(row.join(','));
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.setAttribute('href', URL.createObjectURL(blob));
    link.setAttribute('download', filename + '.csv');
    link.click();
}

// Print
function printContent(elementId) {
    const printWindow = window.open('', '', 'width=800,height=600');
    const element = document.getElementById(elementId);
    printWindow.document.write(element.innerHTML);
    printWindow.document.close();
    printWindow.print();
}

// Toast styles
const style = document.createElement('style');
style.innerHTML = `
    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #16a34a;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 3000;
    }

    .toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .toast-danger {
        background: #dc2626;
    }

    .toast-warning {
        background: #f59e0b;
    }

    .toast-info {
        background: #2563eb;
    }

    @media (max-width: 768px) {
        .toast {
            bottom: 20px;
            right: 20px;
            left: 20px;
            width: auto;
        }
    }
`;
document.head.appendChild(style);

// Default BASE_URL jika belum didefinisikan
if (typeof BASE_URL === 'undefined') {
    window.BASE_URL = '/bangjo/';
}
