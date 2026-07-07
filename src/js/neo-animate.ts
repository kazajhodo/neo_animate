/**
 * Neo Animate.
 *
 * Scroll-triggered viewport animations. Authors write marker classes (via the
 * animate* Alchemist props or by hand in twig); this driver applies the real
 * neo/animate catalog classes at the right moments:
 *
 *  - `neo-animate` + `neo-animate-enter--<name>`: reveal on viewport enter
 *    (once by default).
 *  - `neo-animate-repeat`: re-arm on exit (silent reset while offscreen).
 *  - `neo-animate-exit--<name>`: play this catalog animation on exit, then
 *    re-arm (implies repeat).
 *  - `neo-animate-stagger` (+ children marked `neo-animate-item`): the items are
 *    observed independently and cascade with incremental delays (default 100ms;
 *    tune with data-neo-animate-stagger="<ms>") as THEY enter the viewport — not
 *    when the component root does — so a slow scroller always sees the cascade.
 *    Items crossing in the same frame (e.g. a horizontal card row) form one
 *    batch; items entering at separate scroll moments each start a fresh cascade.
 *  - Speed/delay come from the catalog's compound modifier classes
 *    (neo-animate--fast, neo-animate--delay-slow, ...), which are inert until
 *    the element is armed with neo-animate--animated.
 *  - `data-neo-parallax="<speed>"`: rAF parallax (see neo-parallax.ts).
 *
 * Only this driver ever writes `neo-animate--animated` and raw catalog classes.
 * The pre-hide state lives in src/css/neo-animate.css behind the
 * html.neo-animate-ready gate, so no-JS and reduced-motion visitors always see
 * content. In the Alchemist preview iframe animations play immediately on load
 * (demo mode); when the preview parent requests an html2canvas capture
 * (`screenshotComponents` postMessage) running animations jump to their final
 * frame first.
 */
import initParallax from './neo-parallax';

(function (Drupal, once, drupalSettings) {

  const ENTER_PREFIX = 'neo-animate-enter--';
  const EXIT_PREFIX = 'neo-animate-exit--';
  const SPEED_MODIFIERS = [
    'neo-animate--fastest',
    'neo-animate--faster',
    'neo-animate--fast',
    'neo-animate--slow',
    'neo-animate--slower',
    'neo-animate--slowest',
  ];
  // Delay marker -> ms, matching the catalog's calc() ramp of the default
  // --animate-delay: 0.5s. Used as the stagger base so items inherit the
  // configured delay even when the root itself does not animate.
  const DELAY_MS: Record<string, number> = {
    'neo-animate--delay-fastest': 167,
    'neo-animate--delay-faster': 250,
    'neo-animate--delay-fast': 400,
    'neo-animate--delay-default': 500,
    'neo-animate--delay': 500,
    'neo-animate--delay-slow': 900,
    'neo-animate--delay-slower': 1000,
    'neo-animate--delay-slowest': 1500,
  };
  const THRESHOLD = 0.12;
  const STAGGER_MS = 100;

  const reducedMotion = (): boolean =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const isPreview = (): boolean =>
    typeof drupalSettings.neoAnimate !== 'undefined' && !!drupalSettings.neoAnimate.preview;

  // The Alchemist preview parent can request an html2canvas capture at any
  // time via a `screenshotComponents` postMessage (the capture library is
  // attached to every desktop preview frame — the ?size param is NOT a
  // screenshot signal). When a capture is requested, jump every animation to
  // its final frame so the screenshot never shows half-transparent content.
  let screenshotListener = false;
  const finishOnScreenshot = (): void => {
    if (screenshotListener) {
      return;
    }
    screenshotListener = true;
    window.addEventListener('message', (event) => {
      if (event.data && event.data.type === 'screenshotComponents') {
        document.querySelectorAll<HTMLElement>('.neo-animate--animated').forEach((el) => {
          el.getAnimations().forEach((animation) => {
            try {
              animation.finish();
            }
            catch (e) {
              // Infinite animations cannot finish; ignore.
            }
          });
        });
      }
    });
  };

  const nameFrom = (el: HTMLElement, prefix: string): string | null => {
    for (const cls of el.classList) {
      if (cls.startsWith(prefix)) {
        return cls.substring(prefix.length);
      }
    }
    return null;
  };

  const delayOf = (el: HTMLElement): number => {
    for (const cls of Object.keys(DELAY_MS)) {
      if (el.classList.contains(cls)) {
        return DELAY_MS[cls];
      }
    }
    return 0;
  };

  const speedModsOf = (el: HTMLElement): string[] =>
    SPEED_MODIFIERS.filter((mod) => el.classList.contains(mod));

  // Stagger items belonging to this root only (nesting-safe).
  const items = (root: HTMLElement): HTMLElement[] =>
    Array.from(root.querySelectorAll<HTMLElement>('.neo-animate-item')).filter(
      (item) => item.closest('.neo-animate-stagger') === root,
    );

  /**
   * Apply a catalog animation. Records what was added (merged with anything
   * still recorded) so reset() removes exactly the JS-applied classes and
   * never touches statically-authored modifiers. Safety: even with a typo'd
   * catalog name, neo-animate--animated alone un-hides the element (see the
   * pre-hide selector), so content is never lost.
   */
  const arm = (el: HTMLElement, name: string, extra: string[] = []): void => {
    const prior = (el.dataset.neoAnimateApplied || '').split(' ').filter(Boolean);
    const added = ['neo-animate--' + name, ...extra];
    el.dataset.neoAnimateApplied = Array.from(new Set([...prior, ...added])).join(' ');
    // Honor an authored per-element delay (the neo_animate Twig filters set
    // data-neo-animate-delay) unless a delay was already set inline, e.g. by
    // the stagger pass.
    if (!el.style.animationDelay && el.dataset.neoAnimateDelay) {
      el.style.animationDelay = `${parseInt(el.dataset.neoAnimateDelay, 10) || 0}ms`;
    }
    el.classList.add(...added, 'neo-animate--animated');
  };

  const reset = (el: HTMLElement): void => {
    const applied = (el.dataset.neoAnimateApplied || '').split(' ').filter(Boolean);
    el.classList.remove('neo-animate--animated', ...applied);
    delete el.dataset.neoAnimateApplied;
    el.style.removeProperty('animation-delay');
  };

  // Roots whose exit animation is mid-flight, mapped to a cancel function.
  // Re-entering the viewport during an exit must cancel the pending
  // animationend cleanup, or it would clobber the fresh enter state.
  const pendingExit = new WeakMap<HTMLElement, () => void>();

  // Arm a batch of stagger items with an incremental delay, first-to-last in
  // DOM order. The animation to play is read from the root's enter class, so a
  // stagger with no configured animation is a no-op.
  const cascade = (root: HTMLElement, batch: HTMLElement[]): void => {
    const enterName = nameFrom(root, ENTER_PREFIX);
    if (!enterName || !batch.length) {
      return;
    }
    const base = delayOf(root);
    const step = parseInt(root.dataset.neoAnimateStagger || '', 10) || STAGGER_MS;
    const mods = speedModsOf(root);
    batch
      .slice()
      .sort((a, b) =>
        a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1,
      )
      .forEach((item, i) => {
        item.style.animationDelay = `${base + i * step}ms`;
        arm(item, enterName, mods);
      });
  };

  const enter = (root: HTMLElement): void => {
    const enterName = nameFrom(root, ENTER_PREFIX);
    if (!enterName || !root.classList.contains('neo-animate')) {
      return;
    }
    const cancelExit = pendingExit.get(root);
    if (cancelExit) {
      cancelExit();
      // Force a style flush so re-adding a catalog class that was present a
      // moment ago still restarts its animation.
      void root.offsetWidth;
    }
    arm(root, enterName);
  };

  const leave = (root: HTMLElement): void => {
    const exitName = nameFrom(root, EXIT_PREFIX);
    if (!exitName || !root.classList.contains('neo-animate')) {
      // Repeat without an exit animation: the element is offscreen, so a
      // silent reset re-hides it (pre-hide CSS) with no visible jump.
      reset(root);
      return;
    }
    // Swap the enter catalog class for the exit one. The style flush between
    // reset and arm guarantees a fresh animation even if the same exit class
    // ran recently. Configured delays must not postpone an exit.
    reset(root);
    void root.offsetWidth;
    root.style.animationDelay = '0ms';
    arm(root, exitName);
    const onEnd = (event: AnimationEvent): void => {
      if (event.target === root) {
        cleanup();
      }
    };
    const cleanup = (): void => {
      root.removeEventListener('animationend', onEnd);
      pendingExit.delete(root);
      reset(root);
    };
    pendingExit.set(root, cleanup);
    root.addEventListener('animationend', onEnd);
  };

  // ---- Root reveal observer (each root's own enter animation) ----
  let observer: IntersectionObserver | null = null;
  const armed = new WeakSet<HTMLElement>();

  const getObserver = (): IntersectionObserver => {
    if (!observer) {
      observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            const root = entry.target as HTMLElement;
            if (entry.isIntersecting) {
              if (!armed.has(root)) {
                armed.add(root);
                enter(root);
                // Once by default: stop watching unless exit/repeat re-arms.
                if (!nameFrom(root, EXIT_PREFIX) && !root.classList.contains('neo-animate-repeat')) {
                  (observer as IntersectionObserver).unobserve(root);
                }
              }
            }
            else if (armed.has(root)) {
              armed.delete(root);
              leave(root);
            }
          });
        },
        { threshold: THRESHOLD },
      );
    }
    return observer;
  };

  // ---- Stagger item observer (items cascade on THEIR own entry) ----
  let staggerObserver: IntersectionObserver | null = null;
  const itemArmed = new WeakSet<HTMLElement>();

  const getStaggerObserver = (): IntersectionObserver => {
    if (!staggerObserver) {
      staggerObserver = new IntersectionObserver(
        (entries) => {
          // Group the freshly-entering items by their stagger root; each group
          // becomes one cascade batch (so a row that scrolls in together fans
          // out, while items that enter at different scroll moments restart the
          // cascade instead of inheriting a stale delay).
          const batches = new Map<HTMLElement, HTMLElement[]>();
          entries.forEach((entry) => {
            const item = entry.target as HTMLElement;
            const root = item.closest('.neo-animate-stagger') as HTMLElement | null;
            if (!root) {
              return;
            }
            if (entry.isIntersecting) {
              if (itemArmed.has(item)) {
                return;
              }
              itemArmed.add(item);
              if (!batches.has(root)) {
                batches.set(root, []);
              }
              (batches.get(root) as HTMLElement[]).push(item);
              // Once by default: stop watching unless the root repeats.
              if (!root.classList.contains('neo-animate-repeat')) {
                (staggerObserver as IntersectionObserver).unobserve(item);
              }
            }
            else if (itemArmed.has(item) && root.classList.contains('neo-animate-repeat')) {
              // Repeat: reset offscreen so it re-cascades on the next entry.
              itemArmed.delete(item);
              reset(item);
            }
          });
          batches.forEach((batch, root) => cascade(root, batch));
        },
        { threshold: THRESHOLD },
      );
    }
    return staggerObserver;
  };

  Drupal.behaviors.neoAnimate = {
    attach(context: HTMLElement) {
      once('neo-animate', '.neo-animate, .neo-animate-stagger', context).forEach((element) => {
        const root = element as HTMLElement;
        // Reduced motion: the ready class is absent so nothing is hidden;
        // simply do not animate.
        if (reducedMotion()) {
          return;
        }
        const staggerItems = root.classList.contains('neo-animate-stagger') ? items(root) : [];
        if (isPreview() || !('IntersectionObserver' in window)) {
          // Preview demo mode: play immediately on every iframe reload so the
          // editor sees each change fire. Exit/repeat never runs here.
          finishOnScreenshot();
          enter(root);
          cascade(root, staggerItems);
          return;
        }
        // The root reveals itself when it enters; each stagger item cascades
        // when it enters.
        if (root.classList.contains('neo-animate')) {
          getObserver().observe(root);
        }
        staggerItems.forEach((item) => getStaggerObserver().observe(item));
      });
      if (!reducedMotion()) {
        initParallax(once('neo-parallax', '[data-neo-parallax]', context) as HTMLElement[]);
      }
    },
  };

})(Drupal, once, drupalSettings);
