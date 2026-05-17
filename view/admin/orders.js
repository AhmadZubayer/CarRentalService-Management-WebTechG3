



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

function loadOrders() {
    var status = document.getElementById('filterStatus').value;
    var date   = document.getElementById('filterDate').value;
    var tbody  = document.getElementById('ordersTableBody');
    tbody.innerHTML = '<tr><td colspan="10" class="loading-td">Loading...</td></tr>';

    var params = 'module=order&action=getOrders&status=' + encodeURIComponent(status) + '&date=' + encodeURIComponent(date) + '&csrf_token=' + encodeURIComponent(window.csrfToken);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../ajax_handler.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            var data = JSON.parse(xhr.responseText);
            renderOrdersTable(data);
        }
    };
    xhr.send(params);
}

function renderOrdersTable(data) {
    var tbody = document.getElementById('ordersTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="loading-td">No orders found for the selected filters.</td></tr>';
        return;
    }

    var html = '';
    data.forEach(function(order) {
        var statusBadge = '<span class="badge badge-' + order.status + '">' + order.status + '</span>';
        var orderDate   = new Date(order.order_date).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });

        html += '<tr>';
        html += '<td style="color:#9ca3af;font-weight:600">#' + order.id + '</td>';
        html += '<td><strong>' + order.member_name + '</strong><br><span style="font-size:11px;color:#9ca3af">' + order.member_email + '</span></td>';
        html += '<td>' + order.car_name + '<br><span style="font-size:11px;color:#9ca3af">' + order.car_model + ' - ' + order.car_type + '</span></td>';
        html += '<td style="font-size:12.5px">' + order.start_date + '</td>';
        html += '<td style="font-size:12.5px">' + order.end_date + '</td>';
        html += '<td><strong>Tk ' + parseFloat(order.total_cost).toFixed(2) + '</strong></td>';
        html += '<td>' + statusBadge + '</td>';
        html += '<td style="font-size:12.5px">' + (order.payment_method || '-') + '</td>';
        html += '<td style="font-size:12px;color:#6b7280">' + orderDate + '</td>';
        html += '<td>';
        html += '<select class="status-sel" onchange="updateOrderStatus(' + order.id + ', this.value)">';
        html += '<option value="pending"   ' + (order.status === 'pending'   ? 'selected' : '') + '>Pending</option>';
        html += '<option value="confirmed" ' + (order.status === 'confirmed' ? 'selected' : '') + '>Confirmed</option>';
        html += '<option value="cancelled" ' + (order.status === 'cancelled' ? 'selected' : '') + '>Cancelled</option>';
        html += '</select>';
        html += '</td>';
        html += '</tr>';
    });

    document.getElementById('ordersTableBody').innerHTML = html;
}

function updateOrderStatus(id, status) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../ajax_handler.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            var data = JSON.parse(xhr.responseText);
            if (data.status === 'success') {
                showToast('Order #' + id + ' updated to ' + status, 'success');
                loadOrders();
            } else {
                showToast(data.message, 'error');
            }
        }
    };
    xhr.send('module=order&action=updateStatus&id=' + id + '&status=' + encodeURIComponent(status) + '&csrf_token=' + encodeURIComponent(window.csrfToken));
}

document.getElementById('applyFilterBtn').addEventListener('click', function() {
    loadOrders();
});

document.getElementById('clearFilterBtn').addEventListener('click', function() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterDate').value   = '';
    loadOrders();
});

loadOrders();
