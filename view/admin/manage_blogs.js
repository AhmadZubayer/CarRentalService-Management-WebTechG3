document.addEventListener('DOMContentLoaded', function() {
    const blogsTableBody = document.getElementById('blogsTableBody');
    const blogModal = document.getElementById('blogModal');
    const deleteModal = document.getElementById('deleteModal');
    const adminBlogForm = document.getElementById('adminBlogForm');
    const writeBlogBtn = document.getElementById('writeBlogBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const toastContainer = document.getElementById('toast-container');
    
    let blogs = [];
    let blogToDeleteId = null;

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    async function fetchBlogs() {
        try {
            const response = await fetch('../../controller/BlogsController.php');
            const data = await response.json();
            if (data.success) {
                blogs = data.blogs;
                renderBlogs();
            } else {
                blogsTableBody.innerHTML = `<tr><td colspan="5" class="loading-td" style="color: #ef4444;">${data.message}</td></tr>`;
            }
        } catch (error) {
            blogsTableBody.innerHTML = `<tr><td colspan="5" class="loading-td" style="color: #ef4444;">Error loading blogs.</td></tr>`;
        }
    }

    function renderBlogs() {
        if (blogs.length === 0) {
            blogsTableBody.innerHTML = `<tr><td colspan="5" class="loading-td">No blog posts found.</td></tr>`;
            return;
        }

        blogsTableBody.innerHTML = blogs.map(blog => {
            const isOwner = Number(window.currentUserId) === Number(blog.user_id);
            return `
                <tr>
                    <td><strong>${escapeHtml(blog.title)}</strong></td>
                    <td>${escapeHtml(blog.author_name)}</td>
                    <td>${blog.created_at}</td>
                    <td><div style="max-height: 60px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${escapeHtml(blog.content)}</div></td>
                    <td>
                        ${isOwner ? `<button class="tbl-btn tbl-btn-edit" onclick="editBlog(${blog.id})">Edit</button>` : ''}
                        <button class="tbl-btn tbl-btn-delete" onclick="openDeleteModal(${blog.id})">Delete</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.editBlog = function(id) {
        const blog = blogs.find(b => Number(b.id) === Number(id));
        if (!blog) return;

        document.getElementById('modalHeading').textContent = 'Edit Blog';
        document.getElementById('blogId').value = blog.id;
        document.getElementById('title').value = blog.title;
        document.getElementById('content').value = blog.content;
        document.getElementById('submitBlogBtn').textContent = 'Update Blog';
        blogModal.classList.add('open');
    };

    window.openDeleteModal = function(id) {
        blogToDeleteId = id;
        deleteModal.classList.add('open');
    };

    writeBlogBtn.addEventListener('click', () => {
        document.getElementById('modalHeading').textContent = 'Write a Blog';
        document.getElementById('blogId').value = '';
        adminBlogForm.reset();
        document.getElementById('submitBlogBtn').textContent = 'Post Blog';
        blogModal.classList.add('open');
    });

    closeModalBtn.addEventListener('click', () => {
        blogModal.classList.remove('open');
    });

    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.classList.remove('open');
        blogToDeleteId = null;
    });

    confirmDeleteBtn.addEventListener('click', async () => {
        if (!blogToDeleteId) return;

        try {
            const response = await fetch(`../../controller/BlogsController.php?id=${blogToDeleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            const data = await response.json();
            if (data.success) {
                showToast(data.message);
                fetchBlogs();
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            showToast('Network error while deleting.', 'error');
        } finally {
            deleteModal.classList.remove('open');
            blogToDeleteId = null;
        }
    });

    adminBlogForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // JS Validation
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();
        let hasError = false;

        document.getElementById('titleErr').textContent = '';
        document.getElementById('contentErr').textContent = '';

        if (title === '') {
            document.getElementById('titleErr').textContent = 'Title is required.';
            hasError = true;
        }
        if (content === '') {
            document.getElementById('contentErr').textContent = 'Content is required.';
            hasError = true;
        }

        if (hasError) return;

        const formData = new FormData(adminBlogForm);
        try {
            const response = await fetch('../../controller/BlogsController.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                showToast(data.message);
                blogModal.classList.remove('open');
                fetchBlogs();
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            showToast('Network error while saving.', 'error');
        }
    });

    fetchBlogs();
});
