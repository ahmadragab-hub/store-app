/**
 * Home hero slider: buttons + touch/pointer swipe.
 */
export function initHeroSlider(root = document.querySelector('[data-hero-slider]')) {
    if (!root) {
        return;
    }

    const track = root.querySelector('[data-hero-track]');
    const slides = Array.from(root.querySelectorAll('[data-hero-slide]'));
    const prevBtn = root.querySelector('[data-hero-prev]');
    const nextBtn = root.querySelector('[data-hero-next]');
    const dots = Array.from(root.querySelectorAll('[data-hero-dot]'));

    if (!track || slides.length === 0) {
        return;
    }

    let index = 0;
    let pointerId = null;
    let startX = 0;
    let deltaX = 0;
    let dragging = false;
    const threshold = 48;

    const clamp = (value) => {
        const total = slides.length;
        return ((value % total) + total) % total;
    };

    const setAria = () => {
        slides.forEach((slide, i) => {
            slide.setAttribute('aria-hidden', i === index ? 'false' : 'true');
        });
        dots.forEach((dot, i) => {
            const active = i === index;
            dot.setAttribute('aria-current', active ? 'true' : 'false');
            dot.classList.toggle('is-active', active);
        });
    };

    const goTo = (nextIndex, { animate = true } = {}) => {
        index = clamp(nextIndex);
        track.style.transition = animate ? 'transform 420ms cubic-bezier(0.22, 1, 0.36, 1)' : 'none';
        track.style.transform = `translate3d(${-index * 100}%, 0, 0)`;
        setAria();
    };

    const onPointerDown = (event) => {
        if (event.target.closest('a, button')) {
            return;
        }

        pointerId = event.pointerId;
        startX = event.clientX;
        deltaX = 0;
        dragging = true;
        track.style.transition = 'none';
        track.setPointerCapture?.(pointerId);
    };

    const onPointerMove = (event) => {
        if (!dragging || event.pointerId !== pointerId) {
            return;
        }

        deltaX = event.clientX - startX;
        const width = root.clientWidth || 1;
        const percent = (deltaX / width) * 100;
        track.style.transform = `translate3d(${-index * 100 + percent}%, 0, 0)`;
    };

    const endDrag = (event) => {
        if (!dragging || (event && event.pointerId !== pointerId)) {
            return;
        }

        dragging = false;
        pointerId = null;

        if (Math.abs(deltaX) > threshold) {
            goTo(deltaX < 0 ? index + 1 : index - 1);
        } else {
            goTo(index);
        }

        deltaX = 0;
    };

    prevBtn?.addEventListener('click', () => goTo(index - 1));
    nextBtn?.addEventListener('click', () => goTo(index + 1));
    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            const target = Number(dot.dataset.heroDot);
            if (!Number.isNaN(target)) {
                goTo(target);
            }
        });
    });

    track.addEventListener('pointerdown', onPointerDown);
    track.addEventListener('pointermove', onPointerMove);
    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);
    track.addEventListener('pointerleave', (event) => {
        if (dragging) {
            endDrag(event);
        }
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goTo(index - 1);
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            goTo(index + 1);
        }
    });

    goTo(0, { animate: false });
}
