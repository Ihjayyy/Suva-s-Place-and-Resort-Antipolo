// Review Slider Functionality
document.addEventListener('DOMContentLoaded', function() {
    const reviewContainer = document.getElementById('reviewContainer');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (!reviewContainer || !prevBtn || !nextBtn) return;
    
    const reviews = reviewContainer.querySelectorAll('.review-card');
    let currentIndex = 0;
    
    // Hide all reviews except the first one
    function showReview(index) {
        reviews.forEach((review, i) => {
            review.style.display = i === index ? 'block' : 'none';
        });
    }
    
    // Initialize
    if (reviews.length > 0) {
        showReview(currentIndex);
    }
    
    // Next button
    nextBtn.addEventListener('click', function() {
        currentIndex = (currentIndex + 1) % reviews.length;
        showReview(currentIndex);
    });
    
    // Previous button
    prevBtn.addEventListener('click', function() {
        currentIndex = (currentIndex - 1 + reviews.length) % reviews.length;
        showReview(currentIndex);
    });
    
    // Auto-slide every 5 seconds
    setInterval(function() {
        currentIndex = (currentIndex + 1) % reviews.length;
        showReview(currentIndex);
    }, 5000);
});