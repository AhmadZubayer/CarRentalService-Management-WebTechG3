<?php
require_once __DIR__ . '/../config/dbConfig.php';

function getAllBlogs()
{
    global $conn;
    $sql = 'SELECT b.id, b.user_id, b.title, b.content, b.created_at, u.name AS author_name
            FROM blogs b
            JOIN users u ON b.user_id = u.id
            ORDER BY b.created_at DESC';
    $result = mysqli_query($conn, $sql);

    $blogs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $blogs[] = $row;
    }
    return $blogs;
}

function createBlog(int $userId, string $title, string $content)
{
    global $conn;
    $sql = 'INSERT INTO blogs (user_id, title, content) VALUES (?, ?, ?)';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'iss', $userId, $title, $content);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($conn);
}

function getBlogById(int $id)
{
    global $conn;
    $sql = 'SELECT id, user_id, title, content, created_at FROM blogs WHERE id = ? LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result) ?: null;
}

function deleteBlog(int $id)
{
    global $conn;
    $sql = 'DELETE FROM blogs WHERE id = ?';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_affected_rows($stmt) > 0;
}

function updateBlog(int $id, string $title, string $content)
{
    global $conn;
    $sql = 'UPDATE blogs SET title = ?, content = ? WHERE id = ?';
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssi', $title, $content, $id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_affected_rows($stmt) >= 0;
}
