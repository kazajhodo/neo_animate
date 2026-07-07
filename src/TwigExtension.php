<?php

declare(strict_types=1);

namespace Drupal\neo_animate;

use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig authoring API for neo_animate.
 *
 * Lets ordinary Drupal templates apply the module's scroll-reveal animations
 * to an attributes object, or stagger the children of a render array, without
 * hand-writing the marker classes. Emits the same marker classes the Alchemist
 * props and the driver understand (neo-animate, neo-animate-enter--*, speed /
 * delay / repeat / exit), so it needs no library attach of its own — the driver
 * is already attached globally on non-admin pages.
 *
 * @see \Drupal\neo_animate\TwigExtension::animate()
 */
class TwigExtension extends AbstractExtension {

  /**
   * Friendly animation name => neo/animate catalog class.
   *
   * Names describe the direction the element enters FROM. Any value not listed
   * passes through unchanged as a raw catalog name, so the full catalog
   * (fadeInUpSmall, zoomIn, tada, jello, flipInX, …) is reachable directly.
   */
  protected const ANIMATION_MAP = [
    'fade' => 'fadeIn',
    'fade-up' => 'fadeInUpSmall',
    'fade-down' => 'fadeInDownSmall',
    'fade-left' => 'fadeInLeftSmall',
    'fade-right' => 'fadeInRightSmall',
    'zoom-in' => 'zoomIn',
    'zoom-out' => 'zoomOut',
    'slide-up' => 'slideInUp',
    'slide-down' => 'slideInDown',
    'slide-left' => 'slideInLeft',
    'slide-right' => 'slideInRight',
    'flip-up' => 'flipInX',
    'flip-down' => 'flipInX',
    'flip-left' => 'flipInY',
    'flip-right' => 'flipInY',
    'bounce' => 'bounceIn',
  ];

  /**
   * Valid speed buckets (catalog compound modifiers).
   */
  protected const SPEEDS = ['fastest', 'faster', 'fast', 'slow', 'slower', 'slowest'];

  /**
   * Valid delay buckets (catalog compound modifiers).
   */
  protected const DELAYS = ['fastest', 'faster', 'fast', 'default', 'slow', 'slower', 'slowest'];

  /**
   * {@inheritdoc}
   */
  public function getFilters(): array {
    return [
      new TwigFilter('neo_animate', [$this, 'animate']),
      new TwigFilter('neo_animate_children', [$this, 'animateChildren']),
    ];
  }

  /**
   * Apply an animation to an attributes object.
   *
   * Usage: `<div{{ attributes|neo_animate('fade-up', {delay: 200}) }}>`
   *
   * @param \Drupal\Core\Template\Attribute|array|null $attribute
   *   The attributes to animate. A new Attribute is created when none is given.
   * @param string $animation
   *   A friendly name (fade-up, zoom-in, slide-left, …) or a raw catalog name.
   * @param array $options
   *   Optional: `speed` (bucket), `delay` (ms int or bucket string), `once`
   *   (bool, default TRUE — FALSE replays on re-entry), `exit` (catalog name).
   *
   * @return \Drupal\Core\Template\Attribute
   *   The attributes with animation markers added.
   */
  public function animate($attribute = NULL, string $animation = 'fade-up', array $options = []): Attribute {
    if (!$attribute instanceof Attribute) {
      $attribute = new Attribute(is_array($attribute) ? $attribute : []);
    }
    $attribute->addClass($this->classes($animation, $options));
    if (isset($options['delay']) && is_numeric($options['delay'])) {
      $attribute->setAttribute('data-neo-animate-delay', (int) $options['delay']);
    }
    return $attribute;
  }

  /**
   * Stagger an animation across the children of a render array.
   *
   * Usage: `{{ content.field_images|neo_animate_children('zoom-in', 2, 200) }}`
   * — each child reveals on scroll; with delayByDelta = 2 and delayStep = 200,
   * delays cycle 0ms, 200ms, 0ms, 200ms, … (grouping every 2 items). Pass a
   * delayByDelta larger than the child count for a straight sequential cascade.
   *
   * @param array $build
   *   The render array whose children should be animated.
   * @param string $animation
   *   A friendly name or a raw catalog name.
   * @param int $delayByDelta
   *   Group size for the incremental delay; 0 applies no per-child delay.
   * @param int $delayStep
   *   Milliseconds added per position within a group.
   * @param array $options
   *   Same options as animate() (`delay` here is overridden by the stagger).
   *
   * @return array
   *   The render array with each child carrying animation markers.
   */
  public function animateChildren($build, string $animation = 'fade-up', int $delayByDelta = 0, int $delayStep = 200, array $options = []) {
    if (empty($build) || !is_array($build)) {
      return $build;
    }
    $classes = $this->classes($animation, $options);
    $delta = 0;
    foreach (Element::children($build) as $key) {
      if (!is_array($build[$key])) {
        continue;
      }
      // Wrap bare markup so it can carry the animation attributes.
      if (empty($build[$key]['#type']) || in_array($build[$key]['#type'], ['markup', 'plain_text'], TRUE)) {
        $build[$key] = ['#type' => 'html_tag', '#tag' => 'div', 'value' => $build[$key]];
      }
      $attributes = $build[$key]['#attributes'] ?? [];
      $existing = $attributes['class'] ?? [];
      $attributes['class'] = array_values(array_unique(array_merge(is_array($existing) ? $existing : [$existing], $classes)));
      if ($delayByDelta > 0) {
        $attributes['data-neo-animate-delay'] = $delta * $delayStep;
        $delta = ($delta + 1) % $delayByDelta;
      }
      $build[$key]['#attributes'] = $attributes;
    }
    return $build;
  }

  /**
   * Build the marker class list for an animation + options.
   */
  protected function classes(string $animation, array $options): array {
    $catalog = self::ANIMATION_MAP[$animation] ?? $animation;
    $classes = ['neo-animate', 'neo-animate-enter--' . $catalog];
    if (!empty($options['speed']) && in_array($options['speed'], self::SPEEDS, TRUE)) {
      $classes[] = 'neo-animate--' . $options['speed'];
    }
    if (!empty($options['delay']) && is_string($options['delay']) && in_array($options['delay'], self::DELAYS, TRUE)) {
      $classes[] = 'neo-animate--delay-' . $options['delay'];
    }
    if (array_key_exists('once', $options) && !$options['once']) {
      $classes[] = 'neo-animate-repeat';
    }
    if (!empty($options['exit'])) {
      $classes[] = 'neo-animate-exit--' . (self::ANIMATION_MAP[$options['exit']] ?? $options['exit']);
    }
    return $classes;
  }

}
