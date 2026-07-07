<?php

declare(strict_types=1);

namespace Drupal\neo_animate\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * The Neo Animate playground / gallery.
 */
class AnimateDemoController extends ControllerBase {

  /**
   * Curated catalog animations grouped for the dropdown and gallery.
   *
   * The neo/animate catalog holds ~90 keyframes; these are the reveal-worthy
   * "In" families plus the attention-seekers. Out/Big variants are omitted —
   * they translate thousands of pixels and read as broken in a small tile.
   */
  protected const GROUPS = [
    'Fade' => [
      'fadeIn', 'fadeInUp', 'fadeInUpSmall', 'fadeInDown', 'fadeInDownSmall',
      'fadeInLeft', 'fadeInLeftSmall', 'fadeInRight', 'fadeInRightSmall',
      'fadeInTopLeft', 'fadeInTopRight', 'fadeInBottomLeft', 'fadeInBottomRight',
    ],
    'Zoom' => ['zoomIn', 'zoomInUp', 'zoomInDown', 'zoomInLeft', 'zoomInRight'],
    'Slide' => ['slideInUp', 'slideInDown', 'slideInLeft', 'slideInRight'],
    'Bounce' => ['bounceIn', 'bounceInUp', 'bounceInDown', 'bounceInLeft', 'bounceInRight'],
    'Flip & Rotate' => [
      'flipInX', 'flipInY', 'rotateIn',
      'rotateInDownLeft', 'rotateInDownRight', 'rotateInUpLeft', 'rotateInUpRight',
    ],
    'Special' => [
      'backInUp', 'backInDown', 'backInLeft', 'backInRight',
      'lightSpeedInLeft', 'lightSpeedInRight', 'rollIn', 'jackInTheBox', 'comingIn',
    ],
    'Attention' => [
      'bounce', 'flash', 'pulse', 'rubberBand', 'shakeX', 'shakeY',
      'headShake', 'swing', 'tada', 'wobble', 'jello', 'heartBeat',
    ],
  ];

  /**
   * Catalog name => Alchemist `animate` prop key, for the copy snippet.
   *
   * Mirrors neo_animate.neo_component_prop_defs.yml; only these have a prop.
   */
  protected const PROP_MAP = [
    'fadeIn' => 'fade',
    'fadeInUpSmall' => 'fade_up',
    'fadeInDownSmall' => 'fade_down',
    'fadeInLeftSmall' => 'fade_left',
    'fadeInRightSmall' => 'fade_right',
    'zoomIn' => 'zoom_in',
    'slideInUp' => 'slide_up',
    'bounceIn' => 'bounce_in',
    'flipInX' => 'flip_in',
  ];

  /**
   * Builds the demo page.
   */
  public function demo(): array {
    $groups = [];
    foreach (self::GROUPS as $label => $names) {
      $groups[$label] = array_map(fn(string $name) => [
        'name' => $name,
        'label' => $this->humanize($name),
      ], $names);
    }

    return [
      '#theme' => 'neo_animate_demo',
      '#groups' => $groups,
      '#speeds' => [
        '' => $this->t('Default (0.5s)'),
        'fastest' => $this->t('Fastest (0.17s)'),
        'faster' => $this->t('Faster (0.25s)'),
        'fast' => $this->t('Fast (0.4s)'),
        'slow' => $this->t('Slow (0.9s)'),
        'slower' => $this->t('Slower (1s)'),
        'slowest' => $this->t('Slowest (1.5s)'),
      ],
      '#delays' => [
        '' => $this->t('None'),
        'fastest' => $this->t('Tiny (0.17s)'),
        'faster' => $this->t('Short (0.25s)'),
        'fast' => $this->t('Medium (0.4s)'),
        'default' => $this->t('Standard (0.5s)'),
        'slow' => $this->t('Long (0.9s)'),
        'slower' => $this->t('Longer (1s)'),
        'slowest' => $this->t('Longest (1.5s)'),
      ],
      '#attached' => [
        'library' => ['neo_animate/demo'],
        'drupalSettings' => ['neoAnimateDemo' => ['propMap' => self::PROP_MAP]],
      ],
    ];
  }

  /**
   * Convert "fadeInUpSmall" => "Fade In Up Small".
   */
  protected function humanize(string $name): string {
    return ucwords(trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $name)));
  }

}
