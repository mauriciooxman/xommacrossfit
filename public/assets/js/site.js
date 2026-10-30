// El menú completo permanece disponible cuando JavaScript está desactivado.
const toggle = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#navigation');
if (toggle && navigation) {
  toggle.hidden = false;
  document.documentElement.classList.add('has-js');
  const closeMenu = () => toggle.setAttribute('aria-expanded', 'false');
  toggle.addEventListener('click', () => {
    toggle.setAttribute('aria-expanded', String(toggle.getAttribute('aria-expanded') !== 'true'));
  });
  navigation.addEventListener('click', (event) => {
    if (event.target.closest('a')) closeMenu();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
      closeMenu();
      toggle.focus();
    }
  });
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.header-inner')) closeMenu();
  });
}

// El video es manual; ante un fallo de carga permanece la foto de respaldo.
const heroVideo = document.querySelector('.hero-video');
if (heroVideo) {
  const showPoster = () => { heroVideo.hidden = true; };
  heroVideo.addEventListener('error', showPoster);
  heroVideo.querySelector('source')?.addEventListener('error', showPoster);
}
