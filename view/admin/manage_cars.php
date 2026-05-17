<?php
$pageTitle = 'Manage All Cars';
include 'admin_header.php';
?>


<div class="container-card">
    <div class="card-heading">All Cars</div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name / Model</th>
                    <th>Type</th>
                    <th>Price / Day</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="carsTableBody">
                <tr><td colspan="7" class="loading-td">Loading cars...</td></tr>
            </tbody>
        </table>
    </div>
</div>


<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <h3>Delete Car</h3>
        <p>Are you sure you want to delete <strong id="deleteCarName"></strong>?<br>
           This cannot be undone. Cars with pending or confirmed orders cannot be deleted.</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="cancelDeleteBtn">Cancel</button>
            <button class="btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<div id="toast-container"></div>

        </main>
    </div>
</div>

<script src="manage_cars.js"></script>
</body>
</html>
