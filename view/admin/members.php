<?php
$pageTitle = 'Members';
include 'admin_header.php';
?>


<div class="container-card">
    <div class="card-heading">All Members</div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="membersTableBody">
                <tr><td colspan="6" class="loading-td">Loading members...</td></tr>
            </tbody>
        </table>
    </div>
</div>


<div class="modal-overlay" id="deleteMemberModal">
    <div class="modal-box">
        <h3>Delete Member</h3>
        <p>Are you sure you want to delete <strong id="deleteMemberName"></strong>?<br>
           All their orders and blog posts will also be removed.</p>
        <div class="modal-actions">
            <button class="btn-cancel" id="cancelMemberDeleteBtn">Cancel</button>
            <button class="btn-danger" id="confirmMemberDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<div id="toast-container"></div>

        </main>
    </div>
</div>

<script src="members.js"></script>
</body>
</html>
