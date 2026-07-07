/**
 * Neo Animate: parallax.
 *
 * Elements with data-neo-parallax="<speed>" translate vertically at a fraction
 * of scroll speed (e.g. 0.15; negative values move against the scroll; clamped
 * to ±1). A shared IntersectionObserver keeps a set of near-viewport elements
 * and a single rAF-throttled handler updates only those, so any number of
 * parallax elements costs one measurement pass per scrolled frame.
 *
 * Never combine with a reveal on the same element — the catalog animation's
 * transform (fill-mode: both) and the parallax inline transform fight. Put the
 * parallax on a child or wrapper instead.
 *
 * Element discovery (once/context) stays in neo-animate.ts; this module just
 * receives the elements so all Drupal globals live in one place.
 */
const active = new Set<HTMLElement>();
let observer: IntersectionObserver | null = null;
let listening = false;
let ticking = false;

const update = (): void => {
  ticking = false;
  const vpH = window.innerHeight;
  active.forEach((el) => {
    const rect = el.getBoundingClientRect();
    const speed = Math.max(-1, Math.min(1, parseFloat(el.dataset.neoParallax || '') || 0.15));
    // Distance from element center to viewport center, scaled by speed.
    const centerOffset = rect.top + rect.height / 2 - vpH / 2;
    el.style.transform = `translate3d(0, ${(-centerOffset * speed).toFixed(2)}px, 0)`;
  });
};

const requestUpdate = (): void => {
  if (!ticking && active.size) {
    ticking = true;
    window.requestAnimationFrame(update);
  }
};

const getObserver = (): IntersectionObserver => {
  if (!observer) {
    // Track elements within half a viewport of view so motion is already
    // correct as they scroll in.
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          const el = entry.target as HTMLElement;
          if (entry.isIntersecting) {
            active.add(el);
            el.style.willChange = 'transform';
            requestUpdate();
          } else {
            active.delete(el);
            el.style.willChange = '';
          }
        });
      },
      { rootMargin: '50% 0px' },
    );
  }
  return observer;
};

export default function initParallax(elements: HTMLElement[]): void {
  if (!elements.length) {
    return;
  }
  elements.forEach((el) => getObserver().observe(el));
  if (!listening) {
    listening = true;
    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate, { passive: true });
  }
}
