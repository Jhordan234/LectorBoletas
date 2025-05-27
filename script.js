document.addEventListener('DOMContentLoaded', () => {
    const items = document.querySelectorAll('.carousel-item');
    let currentItem = 0;

    function showNextItem() {
        items[currentItem].classList.remove('active');
        currentItem = (currentItem + 1) % items.length;
        items[currentItem].classList.add('active');
    }

    // Change slide every 5 seconds
    setInterval(showNextItem, 5000);

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});