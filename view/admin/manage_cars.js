



function showToast(message, type) {
    var container = document.getElementById('toast-container');
    var toast = document.createElement('div');
    toast.className = 'toast ' + (type || 'success');
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(function() {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 3500);
}

function loadCars() {
    var tbody = document.getElementById('carsTableBody');
    tbody.innerHTML = '<tr><td colspan="7" class="loading-td">Loading...</td></tr>';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../ajax_handler.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            var data = JSON.parse(xhr.responseText);
            renderCarsTable(data);
        }
    };
    xhr.send('module=car&action=get&csrf_token=' + encodeURIComponent(window.csrfToken));
}

function renderCarsTable(data) {
    var tbody = document.getElementById('carsTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading-td">No cars found. Go to Add Car to add your first car.</td></tr>';
        return;
    }

    var html = '';
    data.forEach(function(car) {
        var imgHtml = car.image_path
            ? '<img src="../../' + car.image_path + '" class="car-thumb" alt="' + car.name + '">'
            : '<span style="color:#9ca3af;font-size:12px">-</span>';

        var statusBadge = car.availability_status === 'Available'
            ? '<span class="badge badge-available">Available</span>'
            : '<span class="badge badge-unavailable">Not Available</span>';

        html += '<tr>';
        html += '<td>' + imgHtml + '</td>';
        html += '<td><strong style="font-size:13.5px">' + car.name + '</strong><br><span style="font-size:11px;color:#9ca3af">' + car.model + '</span></td>';
        html += '<td>' + car.type + '</td>';
        html += '<td><strong>Tk ' + parseFloat(car.price_per_day).toFixed(2) + '</strong></td>';
        html += '<td>' + statusBadge + '</td>';
        html += '<td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#6b7280;font-size:12px">' + (car.description || '-') + '</td>';
        html += '<td>';
        html += '<button class="tbl-btn tbl-btn-edit" onclick="window.location.href=\'add_car.php?edit_id=' + car.id + '\'">Edit</button>';
        html += '<button class="tbl-btn tbl-btn-delete" onclick="confirmDeleteCar(' + car.id + ', \'' + car.name.replace(/'/g, "\\'") + '\')">Delete</button>';
        html += '</td>';
        html += '</tr>';
    });
    document.getElementById('carsTableBody').innerHTML = html;
}

var deleteCarId = null;

function confirmDeleteCar(id, name) {
    deleteCarId = id;
    document.getElementById('deleteCarName').textContent = name;
    document.getElementById('deleteModal').classList.add('open');
}

document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
    document.getElementById('deleteModal').classList.remove('open');
    deleteCarId = null;
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (!deleteCarId) return;
    var btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../ajax_handler.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        btn.disabled = false;
        btn.textContent = 'Delete';
        document.getElementById('deleteModal').classList.remove('open');

        if (xhr.status >= 200 && xhr.status < 300) {
            var data = JSON.parse(xhr.responseText);
            if (data.status === 'success') {
                showToast(data.message, 'success');
                loadCars();
            } else {
                showToast(data.message, 'error');
            }
        }
        deleteCarId = null;
    };
    xhr.send('module=car&action=delete&id=' + deleteCarId + '&csrf_token=' + encodeURIComponent(window.csrfToken));
});

loadCars();
