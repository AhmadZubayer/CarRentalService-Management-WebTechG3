<?php
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Page</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; }
        .header { background: #111827; color: white; padding: 16px 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; }
        .header .brand { display: flex; flex-direction: column; }
        .header a { color: #93c5fd; text-decoration: none; margin-left: 12px; }
        .container { width: min(1100px, 92%); margin: 28px auto; }
        .post-box, .blog-list { background: white; border-radius: 20px; box-shadow: 0 10px 24px rgba(15,23,42,.08); padding: 24px; margin-bottom: 24px; }
        .post-box h3 { margin-top: 0; }
        .field { margin-top: 16px; }
        input, textarea { width: 100%; padding: 14px 16px; border: 1px solid #d1d5db; border-radius: 12px; font-size: 14px; }
        textarea { min-height: 140px; resize: vertical; }
        .button-group { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-top: 18px; }
        .btn { border: none; border-radius: 12px; padding: 12px 18px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .alert { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 14px; padding: 16px; margin-bottom: 24px; }
        .blog-card { border-left: 4px solid #2563eb; padding: 18px 20px; margin-bottom: 18px; border-radius: 14px; background: #ffffff; box-shadow: 0 6px 18px rgba(15,23,42,.06); }
        .blog-title { font-size: 20px; font-weight: 700; color: #111827; margin: 0; }
        .blog-meta { margin: 10px 0 14px; color: #6b7280; font-size: 13px; }
        .blog-content { color: #334155; line-height: 1.8; white-space: pre-wrap; }
        .delete-area { text-align: right; margin-top: 14px; }
        .error, .success { padding: 12px 16px; border-radius: 12px; margin-top: 12px; }
        .error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .success { background: #ecfdf5; color: #166534; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
<div class="header">
    <div class="brand">
        <div style="font-size: 20px; font-weight: 700;">Blog Experience</div>
        <div style="font-size: 13px; color: #cbd5e1;">Member and admin blog sharing</div>
    </div>
</div>

<div class="container">
    <?php if (!$user): ?>
        <div class="alert">Please <a href="../login.php">login</a> to post a new blog. All posts are visible to everyone.</div>
    <?php endif; ?>

    <?php if ($user): ?>
        <div class="post-box">
            <h3 id="formHeading">Post a Blog</h3>
            <form id="blogForm">
                <input type="hidden" id="blogId" name="blogId" value="">
                <div class="field">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Blog title" required>
                </div>
                <div class="field">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" placeholder="Write your experience..." required></textarea>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Post Blog</button>
                    <button type="button" class="btn" id="cancelEditBtn" style="display:none; background:#6b7280; color:#fff;">Cancel</button>
                    <div id="formMessage" role="status"></div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="blog-list" id="blogListContainer">
        <h3>All Blog Posts</h3>
        <div id="blogList">Loading blogs...</div>
    </div>
</div>

<script>
const currentUserId = <?= $user ? (int)$user['id'] : 'null' ?>;
const currentUserRole = <?= $user ? json_encode($user['role']) : 'null' ?>;
const blogList = document.getElementById('blogList');
const blogForm = document.getElementById('blogForm');
const formMessage = document.getElementById('formMessage');
const formHeading = document.getElementById('formHeading');
const blogIdInput = document.getElementById('blogId');
const submitBtn = document.getElementById('submitBtn');
const cancelEditBtn = document.getElementById('cancelEditBtn');
let activeEditId = null;

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderMessage(element, message, isError = false) {
    if (!element) return;
    element.textContent = message;
    element.className = isError ? 'error' : 'success';
}

async function fetchBlogs() {
    try {
        const response = await fetch('../Controller/BlogsController.php');
        const data = await response.json();

        if (!data.success) {
            blogList.innerHTML = '<div class="error">Unable to load blogs.</div>';
            return;
        }

        if (!Array.isArray(data.blogs) || data.blogs.length === 0) {
            window.currentBlogs = [];
            blogList.innerHTML = '<p>No blog posts yet. Be the first to share your experience.</p>';
            return;
        }

        window.currentBlogs = data.blogs;
        blogList.innerHTML = data.blogs.map(blog => {
            const canEdit = currentUserId === Number(blog.user_id);
            const canDelete = currentUserRole === 'admin' || currentUserId === Number(blog.user_id);
            return `
                <article class="blog-card">
                    <h4 class="blog-title">${escapeHtml(blog.title)}</h4>
                    <div class="blog-meta">By ${escapeHtml(blog.author_name)} | ${escapeHtml(blog.created_at)}</div>
                    <div class="blog-content">${escapeHtml(blog.content)}</div>
                    ${canEdit || canDelete ? `<div class="delete-area">${canEdit ? `<button class="btn" style="background:#2563eb; color:#fff; margin-right:10px;" onclick="startEdit(${blog.id})">Edit</button>` : ''}${canDelete ? `<button class="btn btn-danger" onclick="deleteBlog(${blog.id})">Delete</button>` : ''}</div>` : ''}
                </article>
            `;
        }).join('');
    } catch (error) {
        blogList.innerHTML = '<div class="error">Network error while loading blogs.</div>';
    }
}

function resetFormState() {
    activeEditId = null;
    blogIdInput.value = '';
    blogForm.reset();
    formHeading.textContent = 'Post a Blog';
    submitBtn.textContent = 'Post Blog';
    cancelEditBtn.style.display = 'none';
    formMessage.textContent = '';
    formMessage.className = '';
}

function startEdit(blogId) {
    const blog = (window.currentBlogs || []).find(item => Number(item.id) === Number(blogId));
    if (!blog) {
        alert('Blog post not found.');
        return;
    }

    activeEditId = blogId;
    blogIdInput.value = blogId;
    document.getElementById('title').value = blog.title;
    document.getElementById('content').value = blog.content;
    formHeading.textContent = 'Edit Blog Post';
    submitBtn.textContent = 'Save Changes';
    cancelEditBtn.style.display = 'inline-flex';
    formMessage.textContent = '';
    formMessage.className = '';
}

function cancelEdit() {
    resetFormState();
}

async function deleteBlog(blogId) {
    if (!confirm('Delete this blog post?')) {
        return;
    }

    try {
        const response = await fetch(`../Controller/BlogsController.php?id=${encodeURIComponent(blogId)}`, {
            method: 'DELETE',
        });
        const data = await response.json();

        if (!data.success) {
            alert(data.message || 'Unable to delete post.');
            return;
        }

        fetchBlogs();
    } catch (error) {
        alert('Network error while deleting the blog post.');
    }
}

if (blogForm) {
    blogForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        formMessage.textContent = '';
        formMessage.className = '';

        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();
        const blogIdValue = blogIdInput.value.trim();

        if (!title || !content) {
            renderMessage(formMessage, 'Title and content are required.', true);
            return;
        }

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);

            if (blogIdValue) {
                formData.append('blog_id', blogIdValue);
            }

            const response = await fetch('../Controller/BlogsController.php', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();
            if (!data.success) {
                renderMessage(formMessage, data.message || 'Unable to save post.', true);
                return;
            }

            renderMessage(formMessage, data.blog_id && blogIdValue ? 'Blog updated successfully.' : 'Blog posted successfully.');
            resetFormState();
            fetchBlogs();
        } catch (error) {
            renderMessage(formMessage, 'Network error while saving the blog.', true);
        }
    });
    cancelEditBtn.addEventListener('click', cancelEdit);
}

fetchBlogs();
</script>
</body>
</html>
