const currentUserId = Number(window.currentUserId ?? NaN);
const currentUserRole =
	typeof window.currentUserRole === "string" ? window.currentUserRole : "";
const blogList = document.getElementById("blogList");
const blogForm = document.getElementById("blogForm");
const formMessage = document.getElementById("formMessage");
const formHeading = document.getElementById("formHeading");
const blogIdInput = document.getElementById("blogId");
const submitBtn = document.getElementById("submitBtn");
const cancelEditBtn = document.getElementById("cancelEditBtn");
let activeEditId = null;

function escapeHtml(text) {
	return text
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

function renderMessage(element, message, isError = false) {
	if (!element) return;
	element.textContent = message;
	element.className = isError ? "error" : "success";
}

function showToast(message, type) {
	const container = document.getElementById("toast-container");
	if (!container) return;
	const toast = document.createElement("div");
	toast.className = "toast " + (type || "success");
	toast.textContent = message;
	container.appendChild(toast);
	setTimeout(function () {
		if (toast.parentNode) toast.parentNode.removeChild(toast);
	}, 3500);
}

function fetchBlogs() {
	const params =
		"module=blog&action=get&csrf_token=" + encodeURIComponent(window.csrfToken);
	const xhr = new XMLHttpRequest();
	xhr.open("POST", "../../ajax_handler.php", true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.onload = function () {
		if (xhr.status < 200 || xhr.status >= 300) {
			blogList.innerHTML =
				'<div class="error">Network error while loading blogs.</div>';
			return;
		}

		try {
			const data = JSON.parse(xhr.responseText);
			if (data.status !== "success") {
				if (data.message === "Unauthorized") {
					blogList.innerHTML =
						'<div class="alert">Please <a href="../registration/sign-in.php">login</a> to view blog posts.</div>';
					return;
				}

				blogList.innerHTML = '<div class="error">Unable to load blogs.</div>';
				return;
			}

			const blogs = Array.isArray(data.data.blogs) ? data.data.blogs : [];
			if (blogs.length === 0) {
				window.currentBlogs = [];
				blogList.innerHTML =
					"<p>No blog posts yet. Be the first to share your experience.</p>";
				return;
			}

			window.currentBlogs = blogs;
			blogList.innerHTML = blogs
				.map((blog) => {
					const canEdit = currentUserId === Number(blog.user_id);
					const canDelete =
						currentUserRole === "admin" ||
						currentUserId === Number(blog.user_id);
					return `
                <article class="blog-card">
                    <h4 class="blog-title">${escapeHtml(blog.title)}</h4>
                    <div class="blog-meta">By ${escapeHtml(blog.author_name)} | ${escapeHtml(blog.created_at)}</div>
                    <div class="blog-content">${escapeHtml(blog.content)}</div>
                    ${canEdit || canDelete ? `<div class="delete-area">${canEdit ? `<button class="btn btn-danger" style="background:#2563eb; color:#fff; margin-right:10px;" onclick="startEdit(${blog.id})">Edit</button>` : ""}${canDelete ? `<button class="btn btn-danger" onclick="deleteBlog(${blog.id})">Delete</button>` : ""}</div>` : ""}
                </article>
            `;
				})
				.join("");
		} catch (error) {
			blogList.innerHTML =
				'<div class="error">Network error while loading blogs.</div>';
		}
	};
	xhr.onerror = function () {
		blogList.innerHTML =
			'<div class="error">Network error while loading blogs.</div>';
	};
	xhr.send(params);
}

function resetFormState() {
	activeEditId = null;
	if (blogIdInput) {
		blogIdInput.value = "";
	}
	if (blogForm) {
		blogForm.reset();
	}
	if (formHeading) {
		formHeading.textContent = "Post a Blog";
	}
	if (submitBtn) {
		submitBtn.textContent = "Post Blog";
	}
	if (cancelEditBtn) {
		cancelEditBtn.style.display = "none";
	}
	if (formMessage) {
		formMessage.textContent = "";
		formMessage.className = "";
	}
}

function startEdit(blogId) {
	const blog = (window.currentBlogs || []).find(
		(item) => Number(item.id) === Number(blogId),
	);
	if (!blog) {
		showToast("Blog post not found.", "error");
		return;
	}

	activeEditId = blogId;
	blogIdInput.value = blogId;
	document.getElementById("title").value = blog.title;
	document.getElementById("content").value = blog.content;
	formHeading.textContent = "Edit Blog Post";
	submitBtn.textContent = "Save Changes";
	cancelEditBtn.style.display = "inline-flex";
	formMessage.textContent = "";
	formMessage.className = "";
}

function cancelEdit() {
	resetFormState();
}

function deleteBlog(blogId) {
	if (!confirm("Delete this blog post?")) {
		return;
	}

	const params =
		"module=blog&action=delete&id=" +
		encodeURIComponent(blogId) +
		"&csrf_token=" +
		encodeURIComponent(window.csrfToken);
	const xhr = new XMLHttpRequest();
	xhr.open("POST", "../../ajax_handler.php", true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.onload = function () {
		if (xhr.status < 200 || xhr.status >= 300) {
			showToast("Network error while deleting the blog post.", "error");
			return;
		}

		try {
			const data = JSON.parse(xhr.responseText);
			if (data.status !== "success") {
				showToast(data.message || "Unable to delete post.", "error");
				return;
			}

			showToast("Blog post deleted successfully.", "success");
			fetchBlogs();
		} catch (error) {
			showToast("Network error while deleting the blog post.", "error");
		}
	};
	xhr.onerror = function () {
		showToast("Network error while deleting the blog post.", "error");
	};
	xhr.send(params);
}

if (blogForm) {
	blogForm.addEventListener("submit", function (event) {
		event.preventDefault();
		formMessage.textContent = "";
		formMessage.className = "";

		const title = document.getElementById("title").value.trim();
		const content = document.getElementById("content").value.trim();
		const blogIdValue = blogIdInput.value.trim();

		if (!title || !content) {
			renderMessage(formMessage, "Title and content are required.", true);
			return;
		}

		let params =
			"module=blog&action=save&title=" +
			encodeURIComponent(title) +
			"&content=" +
			encodeURIComponent(content) +
			"&csrf_token=" +
			encodeURIComponent(window.csrfToken);

		if (blogIdValue) {
			params += "&blog_id=" + encodeURIComponent(blogIdValue);
		}

		const xhr = new XMLHttpRequest();
		xhr.open("POST", "../../ajax_handler.php", true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onload = function () {
			if (xhr.status < 200 || xhr.status >= 300) {
				renderMessage(
					formMessage,
					"Network error while saving the blog.",
					true,
				);
				return;
			}

			try {
				const data = JSON.parse(xhr.responseText);
				if (data.status !== "success") {
					renderMessage(
						formMessage,
						data.message || "Unable to save post.",
						true,
					);
					return;
				}

				renderMessage(
					formMessage,
					data.blog_id && blogIdValue
						? "Blog updated successfully."
						: "Blog posted successfully.",
				);
				resetFormState();
				fetchBlogs();
			} catch (error) {
				renderMessage(
					formMessage,
					"Network error while saving the blog.",
					true,
				);
			}
		};
		xhr.onerror = function () {
			renderMessage(formMessage, "Network error while saving the blog.", true);
		};
		xhr.send(params);
	});
	cancelEditBtn.addEventListener("click", cancelEdit);
}

fetchBlogs();
