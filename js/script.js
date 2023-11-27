window.addEventListener('scroll', function() {
    var header = document.getElementById('header');
    var intro = document.getElementById('intro');

    // Check if we've scrolled beyond the height of the header
    if(window.pageYOffset > header.offsetHeight) {
        header.classList.add('header-small');
        intro.classList.add('content-visible');
    } else {
        header.classList.remove('header-small');
        intro.classList.remove('content-visible');
    }
});

