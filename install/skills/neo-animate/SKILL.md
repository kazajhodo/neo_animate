---
name: neo-animate
description: Add scroll/viewport animations to Neo Alchemist components — reveal-on-scroll, staggered item cascades, exit animations, and parallax. Use when asked to animate a component, add reveal/entrance/scroll effects, stagger cards or list items, add parallax, or when working with neo-animate-* classes or the animate/animate_speed/animate_delay/animate_stagger props. NOT for CSS transition/hover styling inside a component (plain Tailwind, see neo-component) or for the animation catalog's keyframes themselves (neo/src/css/animate.css).
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Animating Neo components (neo_animate)

The `neo_animate` module reveals elements as they scroll into view. It reuses the
`neo/animate` catalog (an animate.css fork, classes `.neo-animate--<name>` in
[web/modules/contrib/neo/src/css/animate.css](web/modules/contrib/neo/src/css/animate.css))
and drives it with an IntersectionObserver
([web/modules/contrib/neo_animate/src/js/neo-animate.ts](web/modules/contrib/neo_animate/src/js/neo-animate.ts)).
The library is attached globally on non-admin pages — components need **no**
`libraryOverrides` and **no** JS of their own.

## Give a component animation controls (the 90% case)

Add the props to the component's `*.component.yml`:

```yaml
animate:
  type: animate            # dropdown: None / Fade In / Fade Up / Zoom In / …
animate_speed:
  type: animate_speed      # Fastest … Slowest
animate_delay:
  type: animate_delay      # None … Longest
animate_stagger:
  type: animate_stagger    # Off / On — needs item markers, see below
```

Then `drush cr`. The chosen classes land on the component root via `apply: true`
— no twig changes for the root reveal. **Exception:** if the root paints a
background (`bg-default component-bg`), revealing it animates the whole colored
block — reveal the content instead (see *Background components* below).

**Stagger** needs one twig addition: put `neo-animate-item` on the repeating
element (the card/tile inside the `{% for %}`). With Stagger On, items cascade
in at +100ms each (tune with `data-neo-animate-stagger="150"` on the root). The
items are observed independently and cascade as **they** scroll into view — not
when the component root does — so the cascade is visible even to a slow scroller
(items crossing in the same frame, e.g. a horizontal row, form one batch).
Editors turning Stagger on without item markers is a harmless no-op.

## Background components: reveal the content, not the colored root

`apply: true` lands the reveal on the component **root**. If that root paints a
background (`bg-default component-bg`), the whole **colored block** animates in —
almost never what you want. Reveal the *content* instead:

1. In the `.component.yml`, override `apply: false` on `animate`, `animate_speed`,
   `animate_delay` (a component's own prop keys win over the shared prop-def).
2. In the twig, apply them to the inner content wrapper (the
   `container-content py-component` div — add one if the component has none). The
   three must sit **together on one element** (speed/delay are compound
   `.neo-animate--animated.neo-animate--*` modifiers). `getValue()` returns the
   raw key (`fade_up`), NOT the classes — so **merge the Attribute objects**:

```twig
<div {{ attributes.addClass(['bg-default', 'component-bg']) }}>   {# root: bg stays static #}
  <div{{ animate.merge(animate_speed).merge(animate_delay).addClass(['container-content', 'py-component']) }}>
    … content …
  </div>
</div>
```

`scheme`/`spacing` stay on the root (still `apply: true`) — the scheme must wrap
the bg, and `--spacing-component` inherits down to the wrapper's `py-component`.

### Stagger on a background component — keep the reveal and stagger together

The per-item cascade has a hard requirement: **the enter class
(`neo-animate-enter--*`) and `neo-animate-stagger` must be on the SAME element**
(the stagger container), and that element must be an **ancestor of the
`neo-animate-item` children**. The driver's `cascade()` reads the animation name
from the stagger container, so a container with no enter class makes the cascade a
**silent no-op** — and the item pre-hide rule
(`[class*='neo-animate-enter--'].neo-animate-stagger .neo-animate-item`) won't fire
either, so everything just appears at once. **Never split the reveal from the
stagger** (e.g. reveal on the wrapper, `neo-animate-stagger` left on the root). When
you move the reveal to the content wrapper, move `animate_stagger` there too
(also `apply: false`) and merge it onto the same element:

```twig
<div{{ animate.merge(animate_speed).merge(animate_delay).merge(animate_stagger).addClass(['container-content', 'py-component']) }}>
```

The wrapper then block-reveals its own non-item content (heading, etc.) while the
items cascade, and the background root never animates. (`neo-animate-stagger` alone
does not animate the element it sits on — only `neo-animate` + an enter class does
the block reveal, so it's the enter class that must travel with the stagger.)

## Playground

Point frontend devs at **`/admin/config/neo/animate`** (Configuration → Neo →
Animate) to experiment: a live playground with copy-ready output (classes / Twig
filter / Alchemist props), the full catalog gallery, stagger + parallax demos,
and a reduced-motion toggle. Gated by `access neo_animate demo`.

## Twig filters (ordinary templates, non-Alchemist)

For node/views/block/field twigs, two filters apply animations without
hand-writing classes (no library attach needed — the driver is global):

```twig
<div{{ attributes|neo_animate('fade-up', {delay: 200, speed: 'slow'}) }}>…</div>
{{ content.field_images|neo_animate_children('zoom-in', 2, 200) }}
```

`neo_animate(attribute, name, options)` adds markers to an attributes object;
`neo_animate_children(build, name, delayByDelta, delayStep, options)` staggers a
render array's children (delays cycle by group; pass a delayByDelta larger than
the child count for a straight cascade). Names: friendly (`fade-up`, `zoom-in`,
`slide-left`, `flip-up`, `bounce`) or any raw catalog name. Options: `speed`
bucket, `delay` (ms number or bucket string), `once` (`false` → repeat), `exit`
(catalog name). Defined in [TwigExtension.php](web/modules/contrib/neo_animate/src/TwigExtension.php).

## Class vocabulary (for hand-written twig)

Authors write **markers**; only the driver JS writes `neo-animate--animated`
and raw catalog classes. Don't add catalog classes (`neo-animate--fadeInUp`)
statically — they'd need `--animated` anyway and would fight the driver.

| Class / attr | Meaning |
| --- | --- |
| `neo-animate neo-animate-enter--<catalogName>` | reveal on enter (e.g. `neo-animate-enter--fadeInUpSmall`) |
| `neo-animate--{fastest…slowest}` / `neo-animate--delay-*` | speed / delay (catalog compound modifiers — inert until armed; that's by design) |
| `neo-animate-stagger` + child `neo-animate-item` | cascade items |
| `neo-animate-repeat` | re-arm on exit (replays every time it re-enters) |
| `neo-animate-exit--<catalogName>` | play an Out animation on exit, then re-arm (implies repeat) |
| `data-neo-parallax="0.15"` | rAF parallax; clamp ±1; **never on the same element as a reveal** (transforms fight — use a child/wrapper) |

Catalog names: `fadeIn`, `fadeInUpSmall`, `fadeInDownSmall`, `fadeInLeftSmall`,
`fadeInRightSmall`, `zoomIn`, `slideInUp`, `bounceIn`, `flipInX`, … and matching
`*Out*` names for exits — grep `neo/src/css/animate.css` for the full list.

## Safety model (why you don't need to worry)

- Pre-hide is opacity-only and gated behind `html.neo-animate-ready`, added by
  an inline head script only when JS runs AND `prefers-reduced-motion` is off —
  no-JS and reduced-motion visitors always see content, no FOUC.
- **Alchemist preview** plays animations immediately on every iframe reload
  (demo mode) — nothing scroll-gated, nothing stuck hidden. When the preview
  parent requests an html2canvas capture (`screenshotComponents` postMessage),
  running animations jump to their final frame first.
- A typo'd catalog name still un-hides the element (the `--animated` marker
  alone releases the pre-hide) — content is never lost.

## Pitfalls

- **Don't write catalog classes statically** — markers only (see table).
- A custom `animate`-style prop list must keep `none: { value: '' }` (empty
  style maps are rejected by the style shape).
- Speed/delay modifiers look inert if you test them without the driver — they
  are compound selectors on `.neo-animate--animated`. Working as intended.
- Everything here is plain CSS — **no Tailwind rebuild** when adding these
  classes; `drush cr` after prop-def or component.yml edits.
- Parallax + reveal on the same element: the reveal's fill-mode transform
  overrides the parallax inline transform. Wrap instead.
- **Reveal on a background component animates the colored block** — reveal an inner
  content wrapper instead (`apply: false` + merge onto the wrapper). And keep the
  enter class and `neo-animate-stagger` on the **same** element, or the cascade
  silently no-ops. See *Background components*.
