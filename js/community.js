class CommunityManager {
    constructor() {
        this.feedContainer = document.getElementById('feed-container');
        this.postForm = document.getElementById('post-form');
        this.currentPage = 1;
        this.loading = false;
        this.hasMore = true;

        this.initializeEventListeners();
        this.loadFeed();
    }

    initializeEventListeners() {
        // Post form submission
        this.postForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handlePostSubmission(e.target);
        });

        // Infinite scroll
        window.addEventListener('scroll', () => {
            if (this.shouldLoadMore()) {
                this.loadFeed();
            }
        });

        // Filter change
        document.getElementById('feed-filter')?.addEventListener('change', (e) => {
            this.currentPage = 1;
            this.hasMore = true;
            this.feedContainer.innerHTML = '';
            this.loadFeed(e.target.value);
        });
    }

    async handlePostSubmission(form) {
        try {
            const formData = new FormData(form);
            
            const response = await fetch('/api/community/post.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                // Clear form
                form.reset();
                
                // Add new post to feed
                this.prependPost(data.post);
                
                // Show success message
                this.showNotification('Post created successfully!', 'success');
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            this.showNotification(error.message, 'error');
        }
    }

    async loadFeed(filter = 'all') {
        if (this.loading || !this.hasMore) return;
        
        this.loading = true;
        this.showLoader();

        try {
            const response = await fetch(`/api/community/post.php?page=${this.currentPage}&filter=${filter}`);
            const data = await response.json();

            if (data.success) {
                if (data.posts.length === 0) {
                    this.hasMore = false;
                    if (this.currentPage === 1) {
                        this.showEmptyState();
                    }
                } else {
                    data.posts.forEach(post => this.appendPost(post));
                    this.currentPage++;
                }
            }
        } catch (error) {
            this.showNotification('Error loading feed', 'error');
        } finally {
            this.hideLoader();
            this.loading = false;
        }
    }

    appendPost(post) {
        const postElement = this.createPostElement(post);
        this.feedContainer.appendChild(postElement);
    }

    prependPost(post) {
        const postElement = this.createPostElement(post);
        this.feedContainer.insertBefore(postElement, this.feedContainer.firstChild);
    }

    createPostElement(post) {
        const article = document.createElement('article');
        article.className = 'post-card';
        article.innerHTML = `
            <header class="post-header">
                <img src="${post.profile_image}" alt="${post.username}" class="profile-image">
                <div class="post-meta">
                    <h3>${post.username}</h3>
                    <time datetime="${post.created_at}">${this.formatDate(post.created_at)}</time>
                </div>
            </header>
            <div class="post-content">
                <h2>${post.title}</h2>
                <p>${post.description}</p>
                <img src="${post.image_url}" alt="${post.title}" class="post-image">
            </div>
            <div class="post-tags">
                ${this.formatTags(post.tags)}
            </div>
            <footer class="post-footer">
                <button class="like-button ${post.is_liked ? 'liked' : ''}" 
                        onclick="communityManager.handleLike(${post.id}, this)">
                    <i class="fas fa-heart"></i>
                    <span>${post.likes_count}</span>
                </button>
                <button class="comment-button" onclick="communityManager.showComments(${post.id})">
                    <i class="fas fa-comment"></i>
                    <span>${post.comments_count}</span>
                </button>
            </footer>
        `;
        return article;
    }

    async handleLike(postId, button) {
        try {
            const isLiked = button.classList.contains('liked');
            const action = isLiked ? 'unlike' : 'like';

            const response = await fetch('/api/community/interaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&post_id=${postId}`
            });

            const data = await response.json();

            if (data.success) {
                button.classList.toggle('liked');
                const countSpan = button.querySelector('span');
                countSpan.textContent = parseInt(countSpan.textContent) + (isLiked ? -1 : 1);
            }
        } catch (error) {
            this.showNotification('Error updating like', 'error');
        }
    }

    async showComments(postId) {
        try {
            const response = await fetch(`/api/community/post.php?id=${postId}`);
            const data = await response.json();

            if (data.success) {
                this.showCommentsModal(data.post);
            }
        } catch (error) {
            this.showNotification('Error loading comments', 'error');
        }
    }

    showCommentsModal(post) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <header class="modal-header">
                    <h2>Comments</h2>
                    <button onclick="this.closest('.modal').remove()">&times;</button>
                </header>
                <div class="comments-container">
                    ${this.formatComments(post.comments)}
                </div>
                <form class="comment-form" onsubmit="communityManager.handleCommentSubmission(event, ${post.id})">
                    <textarea placeholder="Add a comment..." required></textarea>
                    <button type="submit">Post</button>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    }

    async handleCommentSubmission(event, postId) {
        event.preventDefault();
        const form = event.target;
        const content = form.querySelector('textarea').value;

        try {
            const response = await fetch('/api/community/interaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=comment&post_id=${postId}&content=${encodeURIComponent(content)}`
            });

            const data = await response.json();

            if (data.success) {
                form.reset();
                this.showComments(postId); // Refresh comments
            }
        } catch (error) {
            this.showNotification('Error posting comment', 'error');
        }
    }

    // Utility Methods
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatTags(tags) {
        if (!tags) return '';
        return tags.split(',')
            .map(tag => `<span class="tag">#${tag.trim()}</span>`)
            .join('');
    }

    formatComments(comments) {
        if (!comments.length) {
            return '<p class="no-comments">No comments yet</p>';
        }

        return comments.map(comment => `
            <div class="comment">
                <img src="${comment.profile_image}" alt="${comment.username}" class="profile-image">
                <div class="comment-content">
                    <h4>${comment.username}</h4>
                    <p>${comment.content}</p>
                    <time datetime="${comment.created_at}">${this.formatDate(comment.created_at)}</time>
                </div>
            </div>
        `).join('');
    }

    shouldLoadMore() {
        const scrollPosition = window.innerHeight + window.scrollY;
        const pageHeight = document.documentElement.scrollHeight;
        return scrollPosition > pageHeight - 1000 && !this.loading && this.hasMore;
    }

    showLoader() {
        const loader = document.createElement('div');
        loader.className = 'loader';
        loader.innerHTML = '<div class="spinner"></div>';
        this.feedContainer.appendChild(loader);
    }

    hideLoader() {
        const loader = this.feedContainer.querySelector('.loader');
        if (loader) loader.remove();
    }

    showEmptyState() {
        this.feedContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-camera"></i>
                <h2>No posts yet</h2>
                <p>Be the first to share your outfit!</p>
            </div>
        `;
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize community features
const communityManager = new CommunityManager();
