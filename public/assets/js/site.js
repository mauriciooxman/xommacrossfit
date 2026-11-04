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

// Propiedades CSS desde datos validados, compatibles con la CSP sin estilos inline.
document.querySelectorAll('[data-photo-position]').forEach((media) => {
  media.style.setProperty('--photo-position', media.dataset.photoPosition);
  media.style.setProperty('--photo-position-mobile', media.dataset.photoPositionMobile);
});

// El video es manual; ante un fallo de carga permanece la foto de respaldo.
const heroVideo = document.querySelector('.hero-video');
if (heroVideo) {
  const showPoster = () => { heroVideo.hidden = true; };
  heroVideo.addEventListener('error', showPoster);
  heroVideo.querySelector('source')?.addEventListener('error', showPoster);
}

// Sin JS todos los días siguen visibles; filtrar solo después de inicializar.
document.querySelectorAll('[data-agenda]').forEach((agenda) => {
  const controls = agenda.querySelector('.day-controls');
  const tabs = [...controls.querySelectorAll('button')];
  const panels = [...agenda.querySelectorAll('.agenda-day')];
  const selectDay = (index, focus = false) => {
    tabs.forEach((tab, i) => {
      tab.setAttribute('aria-selected', String(i === index));
      tab.tabIndex = i === index ? 0 : -1;
      panels[i].hidden = i !== index;
    });
    if (focus) tabs[index].focus();
  };
  controls.setAttribute('role', 'tablist');
  tabs.forEach((tab, index) => {
    tab.setAttribute('role', 'tab');
    panels[index].setAttribute('role', 'tabpanel');
    panels[index].setAttribute('aria-labelledby', tab.id);
    panels[index].tabIndex = 0;
    tab.addEventListener('click', () => selectDay(index));
    tab.addEventListener('keydown', (event) => {
      let next;
      if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
      if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
      if (event.key === 'Home') next = 0;
      if (event.key === 'End') next = tabs.length - 1;
      if (next !== undefined) {
        event.preventDefault();
        selectDay(next, true);
      }
    });
  });
  selectDay(0);
  controls.hidden = false;
});
