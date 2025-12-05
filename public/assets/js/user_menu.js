// User Menu Dropdown Functionality
document.addEventListener('DOMContentLoaded', function() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userMenuBtn && userDropdown) {
        // Toggle dropdown on button click
        userMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userDropdown.classList.toggle('active');
            userMenuBtn.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
                userMenuBtn.classList.remove('active');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Review Modal Functionality
    const addReviewBtn = document.getElementById('addReviewBtn');
    const reviewModal = document.getElementById('reviewModal');
    const closeModal = document.querySelector('.close-modal');
    
    if (addReviewBtn && reviewModal) {
        addReviewBtn.addEventListener('click', function() {
            reviewModal.classList.add('active');
        });
        
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                reviewModal.classList.remove('active');
            });
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === reviewModal) {
                reviewModal.classList.remove('active');
            }
        });
    }
    
    // Review Form Submission
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            const rating = document.querySelector('input[name="rating"]:checked');
            const reviewText = document.getElementById('review_text').value.trim();
            
            if (!rating) {
                e.preventDefault();
                alert('Please select a rating!');
                return;
            }
            
            if (reviewText.length < 10) {
                e.preventDefault();
                alert('Please write at least 10 characters for your review!');
                return;
            }
        });
    }
});