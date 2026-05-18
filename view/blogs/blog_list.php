<?php
$currentPage = 'blog';
$pageTitle = 'Blog Experience';
include __DIR__ . '/../member/public_header.php';
?>

<style>
    .blog-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .post-box {
        margin-bottom: 30px;
    }
    .blog-card {
        border-left: 4px solid #3949ab;
        padding: 20px;
        margin-bottom: 20px;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .blog-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px 0;
    }
    .blog-meta {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 12px;
    }
    .blog-content {
        font-size: 14px;
        color: #374151;
        line-height: 1.6;
        white-space: pre-wrap;
    }
    .blog-actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #374151;
        text-transform: uppercase;
    }
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 14px;
    }
    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }
    .alert {
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .alert-info {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    .alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .alert-success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .btn-delete {
        background: #ef4444;
        color: white;
        border: none;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        text-transform: uppercase;
    }
    .btn-edit {
        background: #3949ab;
        color: white;
        border: none;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        text-transform: uppercase;
    }
</style>

<div class="blog-container">
    <h2 class="section-title">Community Experiences</h2>

    <?php if (!$isLoggedIn): ?>
        <div class="alert alert-info">
            Please <a href="../registration/sign-in.php" style="font-weight:700; text-decoration:underline;">sign in</a> to share your experience.
        </div>
    <?php else: ?>
        <div class="container-card post-box">
            <h3 class="card-heading" id="formHeading">Post Your Experience</h3>
            <form id="blogForm">
                <input type="hidden" id="blogId" name="blogId" value="">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Give your post a title" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Experience</label>
                    <textarea id="content" name="content" placeholder="Tell us about your rental experience..." required></textarea>
                </div>
                
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button type="submit" class="btn-1" id="submitBtn">Post Blog</button>
                    <button type="button" class="btn-1" id="cancelEditBtn" style="display:none; background:#6b7280;">Cancel</button>
                    <div id="formStatus"></div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div id="blogListContainer">
        <h3 class="card-heading">All Posts</h3>
        <div id="blogList">Loading experiences...</div>
    </div>
</div>

<script>
const currentUserId = <?= $isLoggedIn ? (int)$_SESSION['user_id'] : 'null' ?>;
const currentUserRole = <?= $isLoggedIn ? json_encode($_SESSION['role']) : 'null' ?>;
const blogList = document.getElementById('blogList');
const blogForm = document.getElementById('blogForm');
const formStatus = document.getElementById('formStatus');
const formHeading = document.getElementById('formHeading');
const blogIdInput = document.getElementById('blogId');
const submitBtn = document.getElementById('submitBtn');
const cancelEditBtn = document.getElementById('cancelEditBtn');
let activeEditId = null;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function fetchBlogs() {
    try {
        const response = await fetch('controller/BlogsController.php');
        const data = await response.json();

        if (!data.success) {
            blogList.innerHTML = '<div class="alert alert-error">Unable to load blogs.</div>';
            return;
        }

        if (!data.blogs || data.blogs.length === 0) {
            window.currentBlogs = [];
            blogList.innerHTML = '<div class="container-card" style="text-align:center; color:#6b7280;">No experiences shared yet.</div>';
            return;
        }

        window.currentBlogs = data.blogs;
        blogList.innerHTML = data.blogs.map(blog => {
            const isOwner = currentUserId === Number(blog.user_id);
            const isAdmin = currentUserRole === 'admin';
            const canEdit = isOwner;
            const canDelete = isAdmin || isOwner;
            
            return `
                <div class="blog-card">
                    <h4 class="blog-title">${escapeHtml(blog.title)}</h4>
                    <div class="blog-meta">By <strong>${escapeHtml(blog.author_name)}</strong> | ${blog.created_at}</div>
                    <div class="blog-content">${escapeHtml(blog.content)}</div>
                    ${canEdit || canDelete ? `
                        <div class="blog-actions">
                            ${canEdit ? `<button class="btn-edit" onclick="startEdit(${blog.id})">Edit</button>` : ''}
                            ${canDelete ? `<button class="btn-delete" onclick="deleteBlog(${blog.id})">Delete</button>` : ''}
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');
    } catch (error) {
        blogList.innerHTML = '<div class="alert alert-error">Network error while loading blogs.</div>';
    }
}

function resetForm() {
    if (!blogForm) return;
    activeEditId = null;
    blogIdInput.value = '';
    blogForm.reset();
    formHeading.textContent = 'Post Your Experience';
    submitBtn.textContent = 'Post Blog';
    cancelEditBtn.style.display = 'none';
    formStatus.innerHTML = '';
}

function startEdit(blogId) {
    const blog = (window.currentBlogs || []).find(item => Number(item.id) === Number(blogId));
    if (!blog) return;

    activeEditId = blogId;
    blogIdInput.value = blogId;
    document.getElementById('title').value = blog.title;
    document.getElementById('content').value = blog.content;
    formHeading.textContent = 'Edit Your Experience';
    submitBtn.textContent = 'Save Changes';
    cancelEditBtn.style.display = 'inline-block';
    formStatus.innerHTML = '';
    window.scrollTo({ top: blogForm.offsetTop - 100, behavior: 'smooth' });
}

cancelEditBtn && cancelEditBtn.addEventListener('click', resetForm);

async function deleteBlog(blogId) {
    if (!confirm('Are you sure you want to delete this post?')) return;

    try {
        const response = await fetch(`controller/BlogsController.php?id=${blogId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            }
        });
        const data = await response.json();

        if (data.success) {
            fetchBlogs();
        } else {
            alert(data.message || 'Failed to delete post.');
        }
    } catch (error) {
        alert('Network error.');
    }
}

if (blogForm) {
    blogForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(blogForm);
        
        formStatus.innerHTML = '<span style="font-size:12px; color:#6b7280;">Saving...</span>';
        
        try {
            const response = await fetch('controller/BlogsController.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                formStatus.innerHTML = `<span style="font-size:12px; color:#10b981;">${data.message}</span>`;
                setTimeout(resetForm, 1500);
                fetchBlogs();
            } else {
                formStatus.innerHTML = `<span style="font-size:12px; color:#ef4444;">${data.message}</span>`;
            }
        } catch (error) {
            formStatus.innerHTML = '<span style="font-size:12px; color:#ef4444;">Network error.</span>';
        }
    });
}

fetchBlogs();
</script>

        </main>
    </div>
</div>
</body>
</html>
