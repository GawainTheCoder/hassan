// assets/js/script.js
document.addEventListener('DOMContentLoaded', function() {
    console.log("Portfolio App script loaded.");
    
    // Fade in content
    const main = document.querySelector('main');
    if (main) {
        main.style.opacity = '0';
        main.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            main.style.opacity = '1';
        }, 100);
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.startsWith('#')) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Close alert messages when clicking the X
    const closeButtons = document.querySelectorAll('.alert-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });
    
    // Add confirm dialog to delete buttons
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Disable notice message after 5 seconds
    const notices = document.querySelectorAll('.notice');
    if (notices.length > 0) {
        setTimeout(() => {
            notices.forEach(notice => {
                notice.style.opacity = '0';
                notice.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    notice.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }
});
