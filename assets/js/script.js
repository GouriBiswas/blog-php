// Confirm delete actions
function confirmDelete(event, message) {
    if (!confirm(message || 'Are you sure you want to delete this item?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

// Preview image before upload with enhanced functionality
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                
                // Add a subtle animation
                preview.classList.remove('image-preview-animation');
                setTimeout(() => {
                    preview.classList.add('image-preview-animation');
                }, 10);
            }
            
            // Update custom file input label
            const label = input.nextElementSibling;
            if (label && label.classList.contains('custom-file-label')) {
                label.textContent = input.files[0].name;
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Theme switching functionality
function setTheme(themeName) {
    localStorage.setItem('theme', themeName);
    document.documentElement.setAttribute('data-theme', themeName);
    
    // Update theme toggle icon
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        themeIcon.className = themeName === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Toggle between dark and light theme
function toggleTheme() {
    const currentTheme = localStorage.getItem('theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
}

// Initialize theme from localStorage
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
}

// Initialize tooltips and other components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initTheme();
    
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add event listeners to delete links
    document.querySelectorAll('.delete-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-message');
            return confirmDelete(e, message);
        });
    });
    
    // Add animation classes to cards
    document.querySelectorAll('.card').forEach(function(card, index) {
        setTimeout(function() {
            card.classList.add('fade-in');
        }, index * 100);
    });
    
    // Initialize custom file inputs
    const fileInputs = document.querySelectorAll('.custom-file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Choose file';
            const label = this.nextElementSibling;
            if (label) {
                label.textContent = fileName;
            }
        });
    });
    
    // Apply solid-card class to all cards for enhanced styling
    document.querySelectorAll('.card:not(.solid-card)').forEach(card => {
        card.classList.add('solid-card');
    });
});

// Like functionality with AJAX
function likePost(postId, button) {
    // Disable the button temporarily to prevent multiple clicks
    button.disabled = true;
    
    // Create a new XMLHttpRequest
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'like-post.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                if (response.success) {
                    // Update UI
                    button.classList.toggle('liked');
                    const likesCountElement = document.getElementById(`likes-count-${postId}`);
                    if (likesCountElement) {
                        likesCountElement.textContent = response.likes_count;
                    }
                    
                    // Update icon
                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.className = button.classList.contains('liked') ? 'fas fa-heart' : 'far fa-heart';
                    }
                    
                    // Add animation
                    button.classList.add('animate-like');
                    setTimeout(() => {
                        button.classList.remove('animate-like');
                    }, 500);
                } else {
                    console.error('Error:', response.message);
                }
            } catch (e) {
                console.error('Invalid JSON response:', e);
            }
        } else {
            console.error('Request failed with status:', this.status);
        }
        
        // Re-enable the button
        button.disabled = false;
    };
    
    xhr.onerror = function() {
        console.error('Request failed');
        button.disabled = false;
    };
    
    xhr.send(`post_id=${postId}`);
    
    // Prevent default link behavior
    return false;
}

// Function to share a post
function sharePost(postId) {
    // Get the current URL
    const postUrl = `${window.location.origin}${window.location.pathname}?id=${postId}`;
    
    // Check if the Web Share API is available
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: postUrl
        })
        .then(() => console.log('Shared successfully'))
        .catch((error) => console.log('Error sharing:', error));
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(postUrl)
            .then(() => {
                // Show a temporary message
                const shareBtn = document.querySelector('.post-share .btn');
                if (shareBtn) {
                    const originalText = shareBtn.innerHTML;
                    shareBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    setTimeout(() => {
                        shareBtn.innerHTML = originalText;
                    }, 2000);
                }
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
            });
    }
}