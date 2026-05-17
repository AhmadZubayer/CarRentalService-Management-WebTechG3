<?php
require_once __DIR__ . '/../model/BlogModel.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$blogId = getRequestedBlogId();

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'DELETE':
        handleDelete($blogId);
        break;
    default:
        respond(['success' => false, 'message' => 'Method not allowed.'], 405);
}

function getRequestedBlogId(): ?int
{
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    $blogId = null;

    if ($pathInfo) {
        $segments = explode('/', trim($pathInfo, '/'));
        if (!empty($segments[0]) && is_numeric($segments[0])) {
            $blogId = (int)$segments[0];
        }
    }

    if (!$blogId && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $blogId = (int)$_GET['id'];
    }

    return $blogId;
}

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function requireLogin()
{
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
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

function handleGet(): void
{
    $blogs = getAllBlogs();
    respond(['success' => true, 'blogs' => $blogs]);
}

function handlePost(): void
{
    requireLogin();

    [$title, $content] = validateBlogInput();
    $user = currentUser();
    $blogId = isset($_POST['blog_id']) && is_numeric($_POST['blog_id']) ? (int)$_POST['blog_id'] : null;

    if ($blogId) {
        $blog = getBlogById($blogId);

        if (!$blog) {
            respond(['success' => false, 'message' => 'Blog post not found.'], 404);
        }

        if ($blog['user_id'] !== (int)$user['id']) {
            respond(['success' => false, 'message' => 'Not authorized to edit this post.'], 403);
        }

        if (updateBlog($blogId, $title, $content)) {
            respond(['success' => true, 'message' => 'Blog post updated successfully.', 'blog_id' => $blogId]);
        }

        respond(['success' => false, 'message' => 'Unable to update blog post.'], 500);
    }

    $newBlogId = createBlog((int)$user['id'], $title, $content);

    if ($newBlogId) {
        respond(['success' => true, 'message' => 'Blog post created successfully.', 'blog_id' => $newBlogId], 201);
    }

    respond(['success' => false, 'message' => 'Unable to save blog post.'], 500);
}

function handleDelete(?int $blogId): void
{
    requireLogin();

    if (!$blogId) {
        respond(['success' => false, 'message' => 'Blog id is required.'], 400);
    }

    $blog = getBlogById($blogId);

    if (!$blog) {
        respond(['success' => false, 'message' => 'Blog post not found.'], 404);
    }

    $user = currentUser();
    $ownsPost = $blog['user_id'] === (int)$user['id'];

    if (!isAdmin() && !$ownsPost) {
        respond(['success' => false, 'message' => 'Not authorized to delete this post.'], 403);
    }

    if (deleteBlog($blogId)) {
        respond(['success' => true, 'message' => 'Blog post deleted successfully.']);
    }

    respond(['success' => false, 'message' => 'Unable to delete blog post.'], 500);
}

function validateBlogInput(): array
{
    $title = sanitizeInput($_POST['title'] ?? '');
    $content = sanitizeInput($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        respond(['success' => false, 'message' => 'Title and content are required.'], 400);
    }

    return [$title, $content];
}
