<?php
include_once 'model/BlogModel.php';

function prepare_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function jsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
}

function handleBlogRequest($conn) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'get') {
        $blogs = getAllBlogs($conn);
        jsonResponse(['status' => 'success', 'data' => ['blogs' => $blogs]]);
        return;
    }

    if ($action === 'save') {
        if (!isLoggedIn()) {
            jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 403);
            return;
        }

        $title = prepare_input($_POST['title'] ?? '');
        $content = prepare_input($_POST['content'] ?? '');
        $blogId = isset($_POST['blog_id']) && is_numeric($_POST['blog_id']) ? (int) $_POST['blog_id'] : 0;

        if ($title === '' || $content === '') {
            jsonResponse(['status' => 'error', 'message' => 'Title and content are required.'], 400);
            return;
        }

        if ($blogId > 0) {
            $blog = getBlogById($conn, $blogId);
            if (!$blog) {
                jsonResponse(['status' => 'error', 'message' => 'Blog post not found.'], 404);
                return;
            }

            if ($blog['user_id'] !== $_SESSION['user_id']) {
                jsonResponse(['status' => 'error', 'message' => 'Not authorized to edit this post.'], 403);
                return;
            }

            if (updateBlog($conn, $blogId, $title, $content)) {
                jsonResponse(['status' => 'success', 'message' => 'Blog post updated successfully.', 'blog_id' => $blogId]);
            } else {
                jsonResponse(['status' => 'error', 'message' => 'Unable to update blog post.'], 500);
            }
            return;
        }

        $newBlogId = createBlog($conn, $_SESSION['user_id'], $title, $content);
        if ($newBlogId) {
            jsonResponse(['status' => 'success', 'message' => 'Blog post created successfully.', 'blog_id' => $newBlogId], 201);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Unable to save blog post.'], 500);
        }
        return;
    }

    if ($action === 'delete') {
        if (!isLoggedIn()) {
            jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 403);
            return;
        }

        $blogId = isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($blogId <= 0) {
            jsonResponse(['status' => 'error', 'message' => 'Invalid blog ID.'], 400);
            return;
        }

        $blog = getBlogById($conn, $blogId);
        if (!$blog) {
            jsonResponse(['status' => 'error', 'message' => 'Blog post not found.'], 404);
            return;
        }

        if (!isAdmin() && $blog['user_id'] !== $_SESSION['user_id']) {
            jsonResponse(['status' => 'error', 'message' => 'Not authorized to delete this post.'], 403);
            return;
        }

        if (deleteBlog($conn, $blogId)) {
            jsonResponse(['status' => 'success', 'message' => 'Blog post deleted successfully.']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Unable to delete blog post.'], 500);
        }
        return;
    }

    jsonResponse(['status' => 'error', 'message' => 'Unknown action.'], 400);
}
