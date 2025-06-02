// Hiển thị/ẩn loading spinner
function showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'spinner-overlay';
    spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    document.body.appendChild(spinner);
}

function hideLoading() {
    const spinner = document.querySelector('.spinner-overlay');
    if (spinner) {
        spinner.remove();
    }
}

// booking form
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        const totalPriceElement = document.getElementById('totalPrice');
        const roomIdInput = document.getElementById('room_id');

        function updateTotalPrice() {
            if (checkInInput.value && checkOutInput.value && roomIdInput.value) {
                const checkIn = new Date(checkInInput.value);
                const checkOut = new Date(checkOutInput.value);
                
                if (checkIn >= checkOut) {
                    alert('Ngày trả phòng phải sau ngày nhận phòng');
                    checkOutInput.value = '';
                    return;
                }

                //get calc api 
                fetch(`${BASE_URL}/api/calculate-price.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        room_id: roomIdInput.value,
                        check_in: checkInInput.value,
                        check_out: checkOutInput.value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        totalPriceElement.textContent = new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(data.total_price);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        checkInInput.addEventListener('change', updateTotalPrice);
        checkOutInput.addEventListener('change', updateTotalPrice);
    }

    // search form
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            window.location.href = `${BASE_URL}/rooms.php?${params.toString()}`;
        });
    }

    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Bạn có chắc chắn muốn xóa?')) {
                e.preventDefault();
            }
        });
    });

    const imageInput = document.querySelector('input[type="file"][accept*="image"]');
    if (imageInput) {
        const preview = document.getElementById('imagePreview');
        if (preview) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    const printButton = document.getElementById('printButton');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }

    const tableFilter = document.getElementById('tableFilter');
    if (tableFilter) {
        tableFilter.addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            const table = document.querySelector('table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell.textContent.toLowerCase().indexOf(value) > -1) {
                        found = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        });
    }
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            const index = Array.from(this.parentNode.children).indexOf(this);
            const direction = this.dataset.direction === 'asc' ? -1 : 1;

            rows.sort((a, b) => {
                const aValue = a.children[index].textContent.trim();
                const bValue = b.children[index].textContent.trim();
                return direction * aValue.localeCompare(bValue, undefined, {numeric: true});
            });

            this.dataset.direction = direction === 1 ? 'asc' : 'desc';
            rows.forEach(row => tbody.appendChild(row));
        });
    });
}); 