import './bootstrap';
import { initHeroSlider } from './hero-slider';

document.addEventListener('DOMContentLoaded', () => {
    initHeroSlider();

    const navToggle = document.querySelector('[data-mobile-nav-toggle]');
    const navPanel = document.querySelector('[data-mobile-nav]');
    if (navToggle && navPanel) {
        navToggle.addEventListener('click', () => {
            const open = navPanel.classList.toggle('hidden') === false;
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    const filtersDrawer = document.querySelector('[data-shop-filters-drawer]');
    const openFilters = document.querySelector('[data-shop-filters-open]');
    const closeFilters = document.querySelectorAll('[data-shop-filters-close]');

    const setFiltersOpen = (open) => {
        if (!filtersDrawer || !openFilters) {
            return;
        }
        filtersDrawer.classList.toggle('is-open', open);
        filtersDrawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        openFilters.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('overflow-hidden', open);
    };

    openFilters?.addEventListener('click', () => setFiltersOpen(true));
    closeFilters.forEach((el) => el.addEventListener('click', () => setFiltersOpen(false)));
});
