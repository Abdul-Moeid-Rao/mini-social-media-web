document.addEventListener('DOMContentLoaded', function () {
    // Like button functionality
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function () {
            const postId = this.dataset.postId;
            const isLiked = this.classList.contains('liked');
            const action = isLiked ? 'unlike' : 'like';
            const likeCount = this.querySelector('.like-count');

            fetch('like_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&action=${action}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.toggle('liked');
                        likeCount.textContent = data.like_count;
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Toggle and load comments
    document.querySelectorAll('.comment-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const postId = this.dataset.postId;
            const wrapper = document.getElementById(`wrapper-${postId}`);
            const commentSection = document.getElementById(`comments-${postId}`);

            if (wrapper.style.display === 'none') {
                if (commentSection.innerHTML.trim() === '') {
                    loadComments(postId);
                }
                wrapper.style.display = 'block';
            } else {
                wrapper.style.display = 'none';
            }
        });
    });

    // Comment form submission
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const postId = this.dataset.postId;
            const input = this.querySelector('input');
            const content = input.value.trim();
            const commentSection = document.getElementById(`comments-${postId}`);

            if (!content) return;

            fetch('comment_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&content=${encodeURIComponent(content)}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const c = data.comment;
                        const html = `
                            <div class="comment">
                                <img src="uploads/${c.profile_pic}" alt="" class="comment-pic">
                                <div>
                                    <strong>${c.full_name}</strong><br>
                                    ${c.content}
                                </div>
                            </div>
                        `;
                        commentSection.innerHTML += html;
                        input.value = '';
                        const countSpan = document.getElementById(`comment-count-${postId}`);
                        countSpan.textContent = parseInt(countSpan.textContent) + 1;
                    }
                });
        });
    });

    // Follow button functionality
    document.querySelectorAll('.follow-btn').forEach(button => {
        button.addEventListener('click', function () {
            const userId = this.dataset.userId;
            const isFollowing = this.classList.contains('following');
            const action = isFollowing ? 'unfollow' : 'follow';

            fetch('follow_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `user_id=${userId}&action=${action}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.toggle('following');
                        this.textContent = isFollowing ? 'Follow' : 'Following';

                        const followerCount = document.querySelector('.stat .count');
                        if (followerCount) {
                            const currentCount = parseInt(followerCount.textContent);
                            followerCount.textContent = isFollowing ? currentCount - 1 : currentCount + 1;
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Image preview (optional: not implemented visually here)
    const postImageInput = document.getElementById('post-image');
    if (postImageInput) {
        postImageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    // Placeholder for image preview logic
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Function to load comments
    function loadComments(postId) {
        const commentsSection = document.getElementById(`comments-${postId}`);

        fetch(`load_comments.php?post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.comments)) {
                    commentsSection.innerHTML = '';
                    let html = '';
                    data.comments.forEach(c => {
                        html += `
                            <div class="comment">
                                <img src="uploads/${c.profile_pic}" alt="" class="comment-pic">
                                <div>
                                    <strong>${c.full_name}</strong><br>
                                    ${c.content}
                                </div>
                            </div>
                        `;
                    });
                    commentsSection.innerHTML = html;
                    document.getElementById(`comment-count-${postId}`).textContent = data.comments.length;
                }
            })
            .catch(error => console.error('Error:', error));
    }
});
