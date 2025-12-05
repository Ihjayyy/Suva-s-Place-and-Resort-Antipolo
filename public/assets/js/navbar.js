

const burger = document.getElementById('burger');
const navLinks = document.getElementById('nav-links');
const navbar = document.querySelector('.navbar');

burger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    burger.classList.toggle('active');
});



window.addEventListener('scroll', () => {
    if(window.scrollY > 10) {
        navbar.classList.add('scrolled');
    }else{
        navbar.classList.remove('scrolled');
    }
});

