<?php
$currentPage = 'manage_blogs';
$pageTitle = 'Manage Blogs';
include 'admin_header.php';
?>

<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div class="page-heading" style="margin-bottom: 0;">Manage All Blogs</div>
    <button class="btn-1" id="writeBlogBtn">Write a Blog</button>
</div>

<div class="container-card">
    <div class="card-heading">All Blog Posts</div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Date</th>
                    <th style="width: 40%;">Content</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="blogsTableBody">
                <tr><td colspan="5" class="loading-td">Loading blogs...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Write/Edit Blog Modal -->
<div class="modal-overlay" id="blogModal">
    <div class="modal-box" style="max-width: 600px; text-align: left;">
        <h3 id="modalHeading">Write a Blog</h3>
        <form id="adminBlogForm" style="margin-top: 20px;">
            <input type="hidden" id="blogId" name="blog_id" value="">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div class="form-field" style="margin-bottom: 15px;">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required placeholder="Blog Title">
                <span class="field-err" id="titleErr"></span>
            </div>
            
            <div class="form-field" style="margin-bottom: 15px;">
                <label for="content">Content</label>
                <textarea id="content" name="content" required placeholder="Write your blog content here..." style="min-height: 150px;"></textarea>
                <span class="field-err" id="contentErr"></span>
            </div>
            
            <div class="modal-actions" style="justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn-cancel" id="closeModalBtn">Cancel</button>
                <button type="submit" class="btn-1" id="submitBlogBtn">Post Blog</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <h3>Delete Blog Post</h3>
        <p>Are you sure you want to delete this blog post?<br>
           This action cannot be undone.</p>
        <div class="modal-actions" style="margin-top: 20px;">
            <button class="btn-cancel" id="cancelDeleteBtn">Cancel</button>
            <button class="btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<div class="toast-container" id="toast-container"></div>

        </main>
    </div>
</div>

<script>
    window.currentUserId = <?= (int)$_SESSION['user_id'] ?>;
</script>
<script src="manage_blogs.js"></script>
</body>
</html>
