# Neo | Animate

Scroll-triggered viewport animations for Neo components: reveal on enter, item
stagger, parallax, and optional exit animations. Built on the existing
`neo/animate` catalog (an animate.css fork namespaced as `.neo-animate--*`) plus
a small IntersectionObserver driver — no external animation library.

## How it works

Authors write **marker classes** (they carry no animation CSS themselves); the
driver (`src/js/neo-animate.ts`) applies the real catalog classes at the right
moments. Only the driver ever writes `neo-animate--animated` and raw catalog
names. Elements queued for a reveal are pre-hidden (opacity only) behind an
`html.neo-animate-ready` gate added by an inline head script — no-JS visitors
and `prefers-reduced-motion` users always see content.

## Class API

| Class / attribute | Written by | Meaning |
| --- | --- | --- |
| `neo-animate` | prop / dev | Reveal this element on viewport enter |
| `neo-animate-enter--<catalogName>` | prop / dev | Which catalog animation plays on enter (e.g. `neo-animate-enter--fadeInUpSmall`) |
| `neo-animate-exit--<catalogName>` | dev | Play this catalog animation on exit, then re-arm (implies repeat) |
| `neo-animate-repeat` | dev | Re-arm on exit without an exit animation |
| `neo-animate-stagger` | prop / dev | Cascade `.neo-animate-item` descendants (+100ms each; tune with `data-neo-animate-stagger="<ms>"`) |
| `neo-animate-item` | dev, in twig | Marks a staggerable child |
| `neo-animate--{fastest,faster,fast,slow,slower,slowest}` | prop / dev | Speed — catalog compound modifiers, inert until armed |
| `neo-animate--delay-{fastest…slowest}` | prop / dev | Delay — same |
| `data-neo-parallax="<speed>"` | dev | rAF parallax (e.g. `0.15`, clamped ±1). **Never on the same element as a reveal** — transforms fight; use a child/wrapper |

## Playground (`/admin/config/neo/animate`)

A frontend-developer tool under **Configuration → Neo → Animate**:

- **Playground** — pick animation / speed / delay / repeat, preview live with a
  Replay button, and copy the result in three forms (marker classes, the
  `neo_animate` Twig filter, the Alchemist prop values).
- **Gallery** — every catalog animation grouped by family, click a tile to replay.
- **Stagger** and **Parallax** live demos.
- **Simulate reduced motion** toggle to preview the accessible fallback.

Gated by the `access neo_animate demo` permission (uid 1 bypasses; grant it to
any role that should see the tool).

## Twig authoring (ordinary templates)

For non-Alchemist templates (node/views/block/field twigs) two filters apply the
same animations without hand-writing marker classes — no library attach needed
(the driver is global):

```twig
{# animate an attributes object #}
<div{{ attributes|neo_animate('fade-up', {delay: 200, speed: 'slow'}) }}>…</div>

{# stagger a render array's children: +200ms every 2nd item #}
{{ content.field_images|neo_animate_children('zoom-in', 2, 200) }}
```

Friendly names (`fade`, `fade-up/down/left/right`, `zoom-in/out`, `slide-*`,
`flip-*`, `bounce`) map to catalog effects; any other value passes through as a
raw catalog name, so the full catalog (`tada`, `jello`, `fadeInUpSmall`, …) is
reachable. Options: `speed` (bucket), `delay` (ms number or bucket string),
`once` (`false` → replays via repeat), `exit` (a catalog name). Precise `delay`
values ride on a `data-neo-animate-delay` attribute the driver applies on reveal.

## Alchemist props

Any component opts in with:

```yaml
animate:
  type: animate
animate_speed:
  type: animate_speed
animate_delay:
  type: animate_delay
animate_stagger:
  type: animate_stagger
```

Stagger additionally needs the component's repeating element to carry the
`neo-animate-item` class in its twig. Editors picking Stagger on a component
without item markers is a harmless no-op; Animation "None" never hides anything.

## Behavior matrix

| Context | Behavior |
| --- | --- |
| Live page | Pre-hidden, reveals at 12% visibility, once by default |
| Alchemist preview iframe | Plays immediately on every reload (demo mode); exit/repeat disabled |
| Preview screenshot capture (`screenshotComponents` postMessage) | Running animations jump to their final frame before html2canvas captures |
| `prefers-reduced-motion` | Nothing hidden, nothing animated |
| JavaScript disabled | Nothing hidden (ready class never added) |

## Pitfalls

- Catalog speed/delay modifiers are compound selectors — inert without
  `neo-animate--animated`. That's by design; don't "fix" them.
- A custom style list for the `animate` prop must keep a `none: { value: '' }`
  option (empty styles maps are rejected).
- All classes here are plain CSS (catalog + this module) — no Tailwind rebuild
  is needed when using them; `drush cr` after editing prop defs or component yml.
