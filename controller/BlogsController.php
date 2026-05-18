<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db-config.php';
require_once __DIR__ . '/../model/BlogModel.php';
require_once __DIR__ . '/../config/security.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$blogId = getRequestedBlogId();

switch ($method) {
    case 'GET':
        handleGet($conn);
        break;
    case 'POST':
        handlePost($conn);
        break;
    case 'DELETE':
        handleDelete($conn, $blogId);
        break;
    default:
        respond(['success' => false, 'message' => 'Method not allowed.'], 405);
}

function getRequestedBlogId(): ?int
{
    $blogId = null;
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $blogId = (int)$_GET['id'];
    } elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $blogId = (int)$_POST['id'];
    }
    return $blogId;
}

function currentUser()
{
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['role'],
            'name' => $_SESSION['name']
        ];
    }
    return null;
}

function isAdmin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function handleGet($conn): void
{
    $blogs = getAllBlogs($conn);
    respond(['success' => true, 'blogs' => $blogs]);
}

function handlePost($conn): void
{
    $user = currentUser();
    if (!$user) {
        respond(['success' => false, 'message' => 'Please login to post.'], 401);
    }

    // CSRF Validation
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verify_csrf_token($csrfToken)) {
        respond(['success' => false, 'message' => 'CSRF token validation failed.'], 403);
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $blogId = isset($_POST['blog_id']) && is_numeric($_POST['blog_id']) ? (int)$_POST['blog_id'] : null;

    if ($title === '' || $content === '') {
        respond(['success' => false, 'message' => 'Title and content are required.'], 400);
    }

    if ($blogId) {
        $blog = getBlogById($conn, $blogId);
        if (!$blog) {
            respond(['success' => false, 'message' => 'Blog post not found.'], 404);
        }
        if ($blog['user_id'] !== (int)$user['id']) {
            respond(['success' => false, 'message' => 'Not authorized to edit this post.'], 403);
        }
        if (updateBlog($conn, $blogId, $title, $content)) {
            respond(['success' => true, 'message' => 'Blog post updated successfully.', 'blog_id' => $blogId]);
        }
        respond(['success' => false, 'message' => 'Unable to update blog post.'], 500);
    } else {
        $newBlogId = createBlog($conn, (int)$user['id'], $title, $content);
        if ($newBlogId) {
            respond(['success' => true, 'message' => 'Blog post created successfully.', 'blog_id' => $newBlogId], 201);
        }
        respond(['success' => false, 'message' => 'Unable to save blog post.'], 500);
    }
}

function handleDelete($conn, ?int $blogId): void
{
    $user = currentUser();
    if (!$user) {
        respond(['success' => false, 'message' => 'Please login to delete.'], 401);
    }

    // For DELETE method, CSRF might be in header
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verify_csrf_token($csrfToken)) {
        respond(['success' => false, 'message' => 'CSRF token validation failed.'], 403);
    }

    if (!$blogId) {
        respond(['success' => false, 'message' => 'Blog id is required.'], 400);
    }

    $blog = getBlogById($conn, $blogId);
    if (!$blog) {
        respond(['success' => false, 'message' => 'Blog post not found.'], 404);
    }

    $ownsPost = (int)$blog['user_id'] === (int)$user['id'];
    if (!isAdmin() && !$ownsPost) {
        respond(['success' => false, 'message' => 'Not authorized to delete this post.'], 403);
    }

    if (deleteBlog($conn, $blogId)) {
        respond(['success' => true, 'message' => 'Blog post deleted successfully.']);
    }
    respond(['success' => false, 'message' => 'Unable to delete blog post.'], 500);
}
