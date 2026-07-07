/**
 * Neo Animate playground / gallery (admin tool).
 *
 * Applies the neo/animate catalog classes directly with explicit replay (not
 * the scroll driver — you don't want to scroll to test), wires the copy-ready
 * output snippets, and runs the stagger / parallax / reduced-motion demos.
 */
((Drupal, once, drupalSettings) => {
  const propMap = (drupalSettings.neoAnimateDemo && drupalSettings.neoAnimateDemo.propMap) || {};

  Drupal.behaviors.neoAnimateDemo = {
    attach(context) {
      once('neo-animate-demo', '[data-neo-animate-demo]', context).forEach((root) => {
        const q = (sel) => root.querySelector(sel);
        const control = (name) => q(`[data-nad="${name}"]`);
        const isReduced = () =>
          root.classList.contains('nad-reduced') ||
          window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Strip any catalog classes/inline delay we applied, then (re)play one.
        const clear = (el) => {
          [...el.classList].forEach((c) => {
            if (c.indexOf('neo-animate--') === 0) {
              el.classList.remove(c);
            }
          });
          el.style.removeProperty('animation-delay');
        };
        const play = (el, name, opts = {}) => {
          clear(el);
          if (isReduced()) {
            el.style.opacity = '1';
            return;
          }
          // Force a reflow so re-adding the same class restarts the animation.
          void el.offsetWidth;
          el.classList.add('neo-animate--' + name);
          if (opts.speed) {
            el.classList.add('neo-animate--' + opts.speed);
          }
          if (opts.delay) {
            el.classList.add('neo-animate--delay-' + opts.delay);
          }
          if (opts.delayMs != null) {
            el.style.animationDelay = opts.delayMs + 'ms';
          }
          el.classList.add('neo-animate--animated');
        };

        // ---- Playground ----
        const preview = q('[data-nad-preview]');
        const readOpts = () => ({
          name: control('animation').value,
          speed: control('speed').value,
          delay: control('delay').value,
          repeat: control('repeat').checked,
        });

        const setSnippet = (key, value) => {
          const el = q(`[data-nad-out="${key}"]`);
          if (el) {
            el.textContent = value;
          }
        };
        const twigOptions = (o) => {
          const parts = [];
          if (o.speed) parts.push(`speed: '${o.speed}'`);
          if (o.delay) parts.push(`delay: '${o.delay}'`);
          if (o.repeat) parts.push('once: false');
          return parts.length ? `, {${parts.join(', ')}}` : '';
        };
        const refresh = () => {
          const o = readOpts();
          const classes = ['neo-animate', 'neo-animate-enter--' + o.name];
          if (o.speed) classes.push('neo-animate--' + o.speed);
          if (o.delay) classes.push('neo-animate--delay-' + o.delay);
          if (o.repeat) classes.push('neo-animate-repeat');
          setSnippet('classes', classes.join(' '));
          setSnippet('twig', `{{ attributes|neo_animate('${o.name}'${twigOptions(o)}) }}`);

          const propRow = q('[data-nad-prop-row]');
          if (propMap[o.name]) {
            const lines = [`animate: ${propMap[o.name]}`];
            if (o.speed) lines.push(`animate_speed: ${o.speed}`);
            if (o.delay) lines.push(`animate_delay: ${o.delay}`);
            setSnippet('prop', lines.join('\n'));
            propRow.classList.remove('is-hidden');
          } else {
            propRow.classList.add('is-hidden');
          }
          play(preview, o.name, o);
        };

        root.querySelectorAll('[data-nad="animation"], [data-nad="speed"], [data-nad="delay"], [data-nad="repeat"]').forEach((el) => {
          el.addEventListener('change', refresh);
        });
        q('[data-nad-play]').addEventListener('click', refresh);

        // Reduced-motion simulation toggle.
        control('reduced').addEventListener('change', (e) => {
          root.classList.toggle('nad-reduced', e.target.checked);
          refresh();
        });

        // Copy buttons.
        root.querySelectorAll('[data-nad-copy]').forEach((btn) => {
          btn.addEventListener('click', () => {
            const key = btn.getAttribute('data-nad-copy');
            const text = q(`[data-nad-out="${key}"]`).textContent;
            navigator.clipboard.writeText(text).then(() => {
              const original = btn.textContent;
              btn.textContent = 'Copied';
              btn.classList.add('is-copied');
              window.setTimeout(() => {
                btn.textContent = original;
                btn.classList.remove('is-copied');
              }, 1200);
            });
          });
        });

        // ---- Stagger ----
        const staggerItems = () => Array.from(root.querySelectorAll('[data-nad-stagger] .nad-stagger-item'));
        q('[data-nad-stagger-play]').addEventListener('click', () => {
          const o = readOpts();
          staggerItems().forEach((item, i) => play(item, o.name, { speed: o.speed, delayMs: i * 100 }));
        });

        // ---- Parallax ----
        const frame = q('[data-nad-parallax-scroll]');
        const badge = q('[data-nad-parallax]');
        if (frame && badge) {
          const speed = parseFloat(badge.getAttribute('data-speed')) || 0.35;
          let ticking = false;
          frame.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(() => {
              ticking = false;
              badge.style.transform = isReduced() ? 'none' : `translateY(${(frame.scrollTop * speed).toFixed(1)}px)`;
            });
          }, { passive: true });
        }

        // ---- Gallery ----
        const galleryPlay = (tile) => {
          const box = tile.querySelector('.nad-tile-box');
          play(box, tile.getAttribute('data-nad-tile'));
        };
        root.querySelectorAll('[data-nad-tile]').forEach((tile) => {
          tile.addEventListener('click', () => galleryPlay(tile));
        });
        // Play each tile once as it scrolls into view.
        if ('IntersectionObserver' in window) {
          const io = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
              if (entry.isIntersecting) {
                galleryPlay(entry.target);
                obs.unobserve(entry.target);
              }
            });
          }, { threshold: 0.4 });
          root.querySelectorAll('[data-nad-tile]').forEach((tile) => io.observe(tile));
        }

        // Initial paint.
        refresh();
      });
    },
  };
})(Drupal, once, drupalSettings);
