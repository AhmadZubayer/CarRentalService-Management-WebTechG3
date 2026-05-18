<?php
$pageTitle = 'Blog Management';
include 'admin_header.php';
?>

<link rel="stylesheet" href="blog.css">

<div class="container-card" id="blogFormCard">
    <div class="card-heading">
        <span id="formHeading">Post a Blog</span>
    </div>

    <p id="formMessage"></p>

    <form id="blogForm" novalidate>
        <input type="hidden" id="blogId" name="blog_id">

        <div class="form-grid">
            <div class="form-field span-2">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" placeholder="Write a short headline" maxlength="120">
            </div>

            <div class="form-field span-2">
                <label for="content">Content *</label>
                <textarea id="content" name="content" rows="7" placeholder="Share your update, experience, or announcement"></textarea>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn-1" id="submitBtn">Post Blog</button>
            <button type="button" class="btn-cancel" id="cancelEditBtn" style="display:none">Cancel Edit</button>
        </div>
    </form>
</div>

<div class="container-card" id="blogListCard">
    <div class="card-heading">
        <span>Published Blogs</span>
    </div>
    <div id="blogList">
        <p>Loading blog posts...</p>
    </div>
</div>

<div id="toast-container"></div>

<script>
    window.currentUserId = <?= (int) ($_SESSION['user_id'] ?? 0) ?>;
    window.currentUserRole = <?= json_encode($_SESSION['role'] ?? '') ?>;
</script>

        </main>
    </div>
</div>

<script src="blog.js"></script>
</body>
</html>
