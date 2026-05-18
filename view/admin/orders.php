<?php
$pageTitle = 'Rent Order History';
include 'admin_header.php';
?>


<div class="container-card" style="padding: 20px 30px;">
    <div class="filter-bar">
        <div class="form-field">
            <label for="filterStatus">Filter by Status</label>
            <select id="filterStatus" class="status-sel" style="padding:9px 11px;font-size:13.5px;border:1px solid #d1d5db;min-width:160px">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="form-field">
            <label for="filterDate">Filter by Order Date</label>
            <input type="date" id="filterDate" style="font-family:'Inter',sans-serif;font-size:13.5px;padding:9px 11px;border:1px solid #d1d5db;background:#fff;color:#111827;outline:none">
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end;">
            <button class="btn-1" id="applyFilterBtn">Apply Filter</button>
            <button class="btn-cancel" id="clearFilterBtn">Clear</button>
        </div>
    </div>
</div>


<div class="container-card">
    <div class="card-heading">All Rent Orders</div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Car</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Order Date</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <tr><td colspan="9" class="loading-td">Loading orders...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="toast-container"></div>

        </main>
    </div>
</div>

<script src="orders.js"></script>
</body>
</html>
