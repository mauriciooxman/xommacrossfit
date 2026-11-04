// Prueba del selector con DOM simulado; no valida renderizado visual.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function element(id) {
  return {
    id, hidden: false, attributes: {}, events: {}, focused: false,
    setAttribute(key, value) { this.attributes[key] = value; },
    addEventListener(key, handler) { this.events[key] = handler; },
    focus() { this.focused = true; },
  };
}
const tabs = Array.from({ length: 8 }, (_, i) => element(`day-control-${i}`));
const panels = Array.from({ length: 8 }, (_, i) => element(`agenda-day-${i}`));
const controls = element('controls');
controls.hidden = true;
controls.querySelectorAll = () => tabs;
const agenda = {
  querySelector: () => controls,
  querySelectorAll: () => panels,
};
const document = {
  querySelector: () => null,
  querySelectorAll: (selector) => selector === '[data-agenda]' ? [agenda] : [],
};
vm.runInNewContext(fs.readFileSync('public/assets/js/site.js', 'utf8'), { document });
function selected(index) {
  assert.equal(controls.hidden, false);
  assert.equal(controls.attributes.role, 'tablist');
  tabs.forEach((tab, i) => {
    assert.equal(tab.attributes.role, 'tab');
    assert.equal(tab.attributes['aria-selected'], String(i === index));
    assert.equal(tab.tabIndex, i === index ? 0 : -1);
    assert.equal(panels[i].hidden, i !== index);
    assert.equal(panels[i].attributes['aria-labelledby'], tab.id);
  });
}
function key(index, value) {
  let prevented = false;
  tabs[index].events.keydown({ key: value, preventDefault() { prevented = true; } });
  return prevented;
}
selected(0);
tabs[2].events.click();
selected(2); // Miércoles por clic.
assert.equal(key(2, 'ArrowRight'), true);
selected(3);
assert.equal(tabs[3].focused, true);
key(3, 'End');
selected(7); // Feriados.
key(7, 'ArrowRight');
selected(0);
key(0, 'ArrowLeft');
selected(7);
key(7, 'Home');
selected(0);
assert.equal(key(0, 'Tab'), false); // No atrapar la navegación de teclado.
selected(0);
console.log('OK: agenda por clic, flechas, Home/End, foco, selección y paneles (DOM simulado).');
