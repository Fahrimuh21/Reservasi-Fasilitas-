import { nextTick, onMounted, onUnmounted } from 'vue';

export function useScrollReveal(options = {}) {
    const selector = options.selector || '[data-reveal]';
    let observer;

    function observe() {
        if (!observer) return;
        document.querySelectorAll(`${selector}:not(.is-revealed)`).forEach(element => observer.observe(element));
    }

    function refresh() {
        nextTick(observe);
    }

    onMounted(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.querySelectorAll(selector).forEach(element => element.classList.add('is-revealed'));
            return;
        }
        observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            });
        }, { threshold: options.threshold ?? 0.14, rootMargin: options.rootMargin || '0px 0px -8% 0px' });
        observe();
    });

    onUnmounted(() => observer?.disconnect());

    return { refresh };
}
