/**
 * Точка входу. Кожен модуль відповідає за одну поведінку і сам вирішує,
 * чи є для нього робота на цій сторінці — тому імпорт безумовний,
 * а ініціалізація ні до чого не призводить, якщо потрібних вузлів немає.
 */

import { initHeroEntrance, initImageReveal } from './modules/entrance.js';
import { initReveal } from './modules/reveal.js';
import { initCounters } from './modules/counters.js';
import { initModals } from './modules/modal.js';
import { initServiceModal } from './modules/services.js';
import { initLightbox } from './modules/lightbox.js';
import { initWorksFilter, initCompare, initShowAll } from './modules/works.js';
import { initBookingForm } from './modules/form.js';
import { initTabs, initMobileNav, initHeaderState, initVideoFacade } from './modules/ui.js';

const start = () => {
    // Перший екран — раніше за все інше: він уже перед очима.
    initHeroEntrance();
    initImageReveal();

    initReveal();
    initCounters();
    initModals();
    initServiceModal();
    initLightbox();
    initWorksFilter();
    initCompare();
    initShowAll();
    initBookingForm();
    initTabs();
    initMobileNav();
    initHeaderState();
    initVideoFacade();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
