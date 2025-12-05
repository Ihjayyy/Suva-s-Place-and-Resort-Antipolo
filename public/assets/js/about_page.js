const learnBtn = document.getElementById('learnBtn');
const extraContent = document.getElementById('extraText');
const showLessBtn = document.getElementById('showLessBtn')

learnBtn.addEventListener('click', () => {
    extraContent.style.display = 'block';
    learnBtn.style.display = 'none';
});

showLessBtn.addEventListener('click', () => {
    extraContent.style.display = 'none';
    learnBtn.style.display = 'inline-block';
    window.scrollTo({ top: learnBtn.offsetTop - 100, behavior: 'smooth' });
});