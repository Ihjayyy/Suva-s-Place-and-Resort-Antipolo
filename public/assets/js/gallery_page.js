const cards = document.querySelectorAll('.highlight-card');
let current = 1;

function updateCards() {
  cards.forEach((card, i) => {
    card.classList.toggle('active', i === current);
  });
}

document.querySelector('.next').onclick = () => {
  current = (current + 1) % cards.length;
  updateCards();
};

document.querySelector('.prev').onclick = () => {
  current = (current - 1 + cards.length) % cards.length;
  updateCards();
};

updateCards();


const track = document.querySelector('.facilities-track');
const images = track.children;
const dotsContainer = document.querySelector('.dots');
let index = 0;

[...images].forEach((_, i) => {
  const dot = document.createElement('span');
  if (i === 0) dot.classList.add('active');
  dotsContainer.appendChild(dot);
});

const dots = dotsContainer.children;

function updateSlider() {
  track.style.transform = `translateX(-${index * 420}px)`;
  [...dots].forEach((d, i) => d.classList.toggle('active', i === index));
}

document.getElementById('facNext').onclick = () => {
  index = (index + 1) % images.length;
  updateSlider();
};

document.getElementById('facPrev').onclick = () => {
  index = (index - 1 + images.length) % images.length;
  updateSlider();
};
