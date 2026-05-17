<?php
$currentPage = 'profile';
$pageTitle = 'My Profile';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../registration/sign-in.php");
    exit();
}

include 'admin_header.php';
?>

<style>
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px 20px;
    }
    .form-grid .span-2 { grid-column: 1 / -1; }
    .form-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
    .form-field label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .form-field input, .form-field select, .form-field textarea {
        font-family: 'Inter', sans-serif;
        font-size: 13.5px;
        padding: 9px 11px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #111827;
        outline: none;
        transition: border-color 0.15s;
        width: 100%;
    }
    .form-field input:focus { border-color: #3949ab; }

    .profile-max-width {
        max-width: 800px;
        margin: 0 auto;
    }

    .view-row { margin-bottom: 20px; }
    .view-label { font-size: 11px; font-weight: 600; color: #6b7280; }
    .view-value { font-size: 15px; color: #111827; }

    .profile-card-header {
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 30px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e5e7eb;
    }
    .profile-avatar-large {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        object-fit: cover;
        background: #f3f4f6;
        border: 3px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .profile-main-info h2 { font-size: 20px; font-weight: 700; color: #111827; }
    .profile-main-info p { font-size: 13px; color: #6b7280; }
    
    .btn-row { display: flex; gap: 12px; margin-top: 10px; }
    .btn-cancel {
        background: #e5e7eb;
        color: #374151;
        font-family: 'Inter', sans-serif;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        border: none;
        padding: 9px 20px;
        cursor: pointer;
        letter-spacing: 0.04em;
    }
    .btn-cancel:hover { background: #d1d5db; }

    .section-divider {
        grid-column: 1 / -1;
        font-size: 11px;
        font-weight: 700;
        color: #3949ab;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin: 20px 0 10px 0;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }
</style>

<div class="profile-max-width">
    <div class="container-card">
        <div class="card-heading">Admin Profile Management</div>
        
        <div id="profileView">
            <div class="profile-card-header">
                <img id="viewAvatar" src="../../public/logo.png" alt="Avatar" class="profile-avatar-large">
                <div class="profile-main-info">
                    <h2 id="viewDisplayName">Loading...</h2>
                    <p id="viewDisplayRole">Administrator</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="view-row">
                    <div class="view-label">Full Name</div>
                    <div class="view-value" id="viewFullName">---</div>
                </div>
                <div class="view-row">
                    <div class="view-label">Email Address</div>
                    <div class="view-value" id="viewEmail">---</div>
                </div>
                <div class="view-row">
                    <div class="view-label">Phone Number</div>
                    <div class="view-value" id="viewPhone">---</div>
                </div>
                <div class="view-row">
                    <div class="view-label">Home Address</div>
                    <div class="view-value" id="viewAddress">---</div>
                </div>
            </div>

            <div class="btn-row" style="margin-top: 20px; border-top: 1px solid #f3f4f6; padding-top: 20px;">
                <button class="btn-1" onclick="toggleEdit(true)">Edit Profile</button>
            </div>
        </div>

        <div id="profileEdit" style="display: none;">
            <form id="profileForm" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="section-divider">Personal Information</div>
                    
                    <div class="form-field span-2" style="background: #f9fafb; padding: 15px; border: 1px dashed #d1d5db;">
                        <label for="profilePicture">Update Profile Picture</label>
                        <input type="file" id="profilePicture" name="profile_picture" accept="image/jpeg, image/png" style="border: none; padding: 10px 0;">
                        <p style="font-size: 11px; color: #6b7280; margin-top: 5px;">Max size 2MB. Format: JPG, PNG.</p>
                    </div>
                    
                    <div class="form-field">
                        <label for="profName">Full Name *</label>
                        <input type="text" id="profName" name="name" required placeholder="Enter Full Name Here">
                    </div>
                    <div class="form-field">
                        <label for="profEmail">Email Address *</label>
                        <input type="email" id="profEmail" name="email" required placeholder="john@example.com">
                    </div>
                    <div class="form-field">
                        <label for="profPhone">Phone Number</label>
                        <input type="text" id="profPhone" name="phone" placeholder="+880123456789">
                    </div>
                    <div class="form-field">
                        <label for="profAddress">Address</label>
                        <input type="text" id="profAddress" name="address" placeholder="123 Street, City">
                    </div>

                    <div class="section-divider">Security (Optional)</div>
                    <p class="span-2" style="font-size: 12px; color: #6b7280; margin-bottom: 10px;">Leave password fields blank if you don't want to change it.</p>
                    
                    <div class="form-field">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" id="currentPassword" name="current_password" placeholder="••••••••">
                    </div>
                    <div class="form-field">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="new_password" minlength="8" placeholder="Min 8 characters">
                    </div>
                </div>
                
                <div class="btn-row" style="margin-top: 24px; border-top: 1px solid #f3f4f6; padding-top: 24px;">
                    <button type="submit" class="btn-1" id="saveProfBtn">Save All Changes</button>
                    <button type="button" class="btn-cancel" onclick="toggleEdit(false)">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="toast-container"></div>

        </main>
    </div>
</div>

<script>
function showToast(message, type) {
    var container = document.getElementById('toast-container');
    if (!container) return;
    var toast = document.createElement('div');
    toast.className = 'toast ' + (type || 'success');
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(function() {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 3500);
}

const ajaxPath = '../../ajax_handler.php';

function toggleEdit(isEdit) {
    document.getElementById('profileView').style.display = isEdit ? 'none' : 'block';
    document.getElementById('profileEdit').style.display = isEdit ? 'block' : 'none';
    if (!isEdit) {
        document.getElementById('profileForm').reset();
        loadProfile();
    }
}

function loadProfile() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxPath, true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            var res = JSON.parse(xhr.responseText);
            if (res.status === 'success') {
                var data = res.data;
                document.getElementById('viewDisplayName').textContent = data.name;
                document.getElementById('viewFullName').textContent = data.name;
                document.getElementById('viewEmail').textContent = data.email;
                document.getElementById('viewPhone').textContent = data.phone || 'Not provided';
                document.getElementById('viewAddress').textContent = data.address || 'Not provided';
                if (data.profile_picture) {
                    document.getElementById('viewAvatar').src = '../../' + data.profile_picture;
                }
                document.getElementById('profName').value = data.name;
                document.getElementById('profEmail').value = data.email;
                document.getElementById('profPhone').value = data.phone || '';
                document.getElementById('profAddress').value = data.address || '';
            }
        }
    };
    xhr.send('module=user&action=getProfile');
}

document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var curPass = document.getElementById('currentPassword').value.trim();
    var newPass = document.getElementById('newPassword').value.trim();
    
    if (newPass !== '' && curPass === '') {
        showToast('Please provide your current password to set a new one.', 'error');
        return;
    }

    var btn = document.getElementById('saveProfBtn');
    btn.textContent = 'Saving...';
    btn.disabled = true;

    var formData = new FormData(this);
    formData.append('module', 'user');
    formData.append('action', 'updateProfile');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', ajaxPath, true);
    xhr.onload = function() {
        btn.textContent = 'Save All Changes';
        btn.disabled = false;
        var res = JSON.parse(xhr.responseText);
        if (res.status === 'success') {
            showToast(res.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(res.message, 'error');
        }
    };
    xhr.send(formData);
});

loadProfile();
</script>
</body>
</html>
