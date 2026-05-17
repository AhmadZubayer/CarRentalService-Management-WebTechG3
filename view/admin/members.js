



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

function loadMembers() {
    var tbody = document.getElementById('membersTableBody');
    tbody.innerHTML = '<tr><td colspan="7" class="loading-td">Loading...</td></tr>';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../ajax_handler.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            var data = JSON.parse(xhr.responseText);
            renderMembersTable(data);
        }
    };
    xhr.send('module=user&action=getMembers&csrf_token=' + encodeURIComponent(window.csrfToken));
}

function renderMembersTable(data) {
    var tbody = document.getElementById('membersTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="loading-td">No members found.</td></tr>';
        return;
    }

    var html = '';
    data.forEach(function(member) {
        var joinDate = new Date(member.created_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' });
        html += '<tr>';
        html += '<td style="color:#9ca3af">' + member.id + '</td>';
        html += '<td><strong>' + member.name + '</strong></td>';
        html += '<td>' + member.email + '</td>';
        html += '<td>' + (member.phone || '-') + '</td>';
        html += '<td>' + (member.address || '-') + '</td>';
        html += '<td style="font-size:12px;color:#6b7280">' + joinDate + '</td>';
        html += '<td><button class="tbl-btn tbl-btn-delete" onclick="confirmDeleteMember(' + member.id + ', \'' + member.name.replace(/'/g, "\\'") + '\')">Delete</button></td>';
        html += '</tr>';
    });
    document.getElementById('membersTableBody').innerHTML = html;
}

var deleteMemberId = null;

function confirmDeleteMember(id, name) {
    deleteMemberId = id;
    document.getElementById('deleteMemberName').textContent = name;
    document.getElementById('deleteMemberModal').classList.add('open');
}

document.getElementById('cancelMemberDeleteBtn').addEventListener('click', function() {
    document.getElementById('deleteMemberModal').classList.remove('open');
    deleteMemberId = null;
});

document.getElementById('confirmMemberDeleteBtn').addEventListener('click', function() {
    if (!deleteMemberId) return;

    var btn = document.getElementById('confirmMemberDeleteBtn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../ajax_handler.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        btn.disabled = false;
        btn.textContent = 'Delete';
        document.getElementById('deleteMemberModal').classList.remove('open');

        if (xhr.status >= 200 && xhr.status < 300) {
            var data = JSON.parse(xhr.responseText);
            if (data.status === 'success') {
                showToast(data.message, 'success');
                loadMembers();
            } else {
                showToast(data.message, 'error');
            }
        }
        deleteMemberId = null;
    };
    xhr.send('module=user&action=deleteMember&id=' + deleteMemberId + '&csrf_token=' + encodeURIComponent(window.csrfToken));
});

loadMembers();
