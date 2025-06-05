<?php

declare(strict_types=1);

namespace Drupal\neo_animate;

use Drupal\Core\Template\Attribute;
use Drupal\neo_settings\SettingsTrait;

/**
 * An animation set.
 */
class Animate {

  use SettingsTrait;

  /**
   * The settings ID.
   *
   * @var string
   */
  protected string $settingsId = 'neo_animate.settings';

  /**
   * The animation.
   *
   * @var string|null
   */
  protected string|null $animation = NULL;

  /**
   * The offset.
   *
   * @var ind|null
   */
  protected int|null $offset = NULL;

  /**
   * The delay.
   *
   * @var int|null
   */
  protected int|null $delay = NULL;

  /**
   * The duration.
   *
   * @var int|null
   */
  protected int|null $duration = NULL;

  /**
   * The easing.
   *
   * @var string|null
   */
  protected string|null $easing = NULL;

  /**
   * Perform only once.
   *
   * @var bool|null
   */
  protected bool|null $once = NULL;

  /**
   * Mirror the animation.
   *
   * @var bool|null
   */
  protected bool|null $mirror = NULL;

  /**
   * The anchor.
   *
   * @var string|null
   */
  protected string|null $anchor = NULL;

  /**
   * The anchor placement.
   *
   * @var string|null
   */
  protected string|null $anchorPlacement = NULL;

  /**
   * Constructs a new Modal.
   *
   * @param array $options
   *   The options.
   */
  public function __construct(array $options = []) {
    $options += $this->getSettings()->getDiffValues();
    if (!empty($options)) {
      $class = new \ReflectionClass($this);
      foreach ($options as $key => $option) {
        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
          $param = $class->getMethod($method)->getParameters()[0]->getType();
          if ((string) $param === 'bool') {
            $option = (bool) $option;
          }
          $this->$method($option);
        }
      }
    }
  }

  /**
   * Returns an array of animations.
   *
   * @return array
   *   An array of animations, where the keys represent the animation values
   *   and the values represent the human-readable labels.
   */
  public static function getAnimations():array {
    return [
      'fade' => t('Fade'),
      'fade-up' => t('Fade Up'),
      'fade-down' => t('Fade Down'),
      'fade-left' => t('Fade Left'),
      'fade-right' => t('Fade Right'),
      'fade-up-right' => t('Fade Up Right'),
      'fade-up-left' => t('Fade Up Left'),
      'fade-down-right' => t('Fade Down Right'),
      'fade-down-left' => t('Fade Down Left'),
      'flip-up' => t('Flip Up'),
      'flip-down' => t('Flip Down'),
      'flip-left' => t('Flip Left'),
      'flip-right' => t('Flip Right'),
      'slide-up' => t('Slide Up'),
      'slide-down' => t('Slide Down'),
      'slide-left' => t('Slide Left'),
      'slide-right' => t('Slide Right'),
      'zoom-in' => t('Zoom In'),
      'zoom-in-up' => t('Zoom In Up'),
      'zoom-in-down' => t('Zoom In Down'),
      'zoom-in-left' => t('Zoom In Left'),
      'zoom-in-right' => t('Zoom In Right'),
      'zoom-out' => t('Zoom Out'),
      'zoom-out-up' => t('Zoom Out Up'),
      'zoom-out-down' => t('Zoom Out Down'),
      'zoom-out-left' => t('Zoom Out Left'),
      'zoom-out-right' => t('Zoom Out Right'),
    ];
  }

  /**
   * Returns an array of animation placements.
   *
   * @return array
   *   An array of modal placements, where the keys represent the placement
   *   values and the values represent the human-readable labels.
   */
  public static function getPlacements():array {
    return [
      'top-center' => t('Top Center'),
      'top-bottom' => t('Top Bottom'),
      'top-top' => t('Top Top'),
      'center-center' => t('Center Center'),
      'center-bottom' => t('Center Bottom'),
      'center-top' => t('Center Top'),
      'bottom-center' => t('Bottom Center'),
      'bottom-bottom' => t('Bottom Bottom'),
      'bottom-top' => t('Bottom Top'),
    ];
  }

  /**
   * Returns an array of easing types.
   *
   * @return array
   *   An array of easing types, where the keys represent the easing
   *   function values and the values represent the human-readable labels.
   */
  public static function getEasing():array {
    return [
      'linear' => t('Linear'),
      'ease' => t('Ease'),
      'ease-in' => t('Ease In'),
      'ease-out' => t('Ease Out'),
      'ease-in-out' => t('Ease In Out'),
      'ease-in-back' => t('Ease In Back'),
      'ease-out-back' => t('Ease Out Back'),
      'ease-in-out-back' => t('Ease In Out Back'),
      'ease-in-sine' => t('Ease In Sine'),
      'ease-out-sine' => t('Ease Out Sine'),
      'ease-in-out-sine' => t('Ease In Out Sine'),
      'ease-in-quad' => t('Ease In Quad'),
      'ease-out-quad' => t('Ease Out Quad'),
      'ease-in-out-quad' => t('Ease In Out Quad'),
      'ease-in-cubic' => t('Ease In Cubic'),
      'ease-out-cubic' => t('Ease Out Cubic'),
      'ease-in-out-cubic' => t('Ease In Out Cubic'),
      'ease-in-quart' => t('Ease In Quart'),
      'ease-out-quart' => t('Ease Out Quart'),
      'ease-in-out-quart' => t('Ease In Out Quart'),
    ];
  }

  /**
   * Set the animation.
   *
   * @param string $animation
   *   The animation.
   */
  public function setAnimation(string $animation):void {
    if (!array_key_exists($animation, self::getAnimations())) {
      throw new \InvalidArgumentException('Invalid animation');
    }
    $this->animation = $animation;
  }

  /**
   * Set the animation to fade.
   */
  public function setAnimationFade():void {
    $this->setAnimation('fade');
  }

  /**
   * Set the animation to fade-up.
   */
  public function setAnimationFadeUp():void {
    $this->setAnimation('fade-up');
  }

  /**
   * Set the animation to fade-down.
   */
  public function setAnimationFadeDown():void {
    $this->setAnimation('fade-down');
  }

  /**
   * Set the animation to fade-left.
   */
  public function setAnimationFadeLeft():void {
    $this->setAnimation('fade-left');
  }

  /**
   * Set the animation to fade-right.
   */
  public function setAnimationFadeRight():void {
    $this->setAnimation('fade-right');
  }

  /**
   * Set the animation to fade-up-right.
   */
  public function setAnimationFadeUpRight():void {
    $this->setAnimation('fade-up-right');
  }

  /**
   * Set the animation to fade-up-left.
   */
  public function setAnimationFadeUpLeft():void {
    $this->setAnimation('fade-up-left');
  }

  /**
   * Set the animation to fade-down-right.
   */
  public function setAnimationFadeDownRight():void {
    $this->setAnimation('fade-down-right');
  }

  /**
   * Set the animation to fade-down-left.
   */
  public function setAnimationFadeDownLeft():void {
    $this->setAnimation('fade-down-left');
  }

  /**
   * Set the animation to flip-up.
   */
  public function setAnimationFlipUp():void {
    $this->setAnimation('flip-up');
  }

  /**
   * Set the animation to flip-down.
   */
  public function setAnimationFlipDown():void {
    $this->setAnimation('flip-down');
  }

  /**
   * Set the animation to flip-left.
   */
  public function setAnimationFlipLeft():void {
    $this->setAnimation('flip-left');
  }

  /**
   * Set the animation to flip-right.
   */
  public function setAnimationFlipRight():void {
    $this->setAnimation('flip-right');
  }

  /**
   * Set the animation to slide-up.
   */
  public function setAnimationSlideUp():void {
    $this->setAnimation('slide-up');
  }

  /**
   * Set the animation to slide-down.
   */
  public function setAnimationSlideDown():void {
    $this->setAnimation('slide-down');
  }

  /**
   * Set the animation to slide-left.
   */
  public function setAnimationSlideLeft():void {
    $this->setAnimation('slide-left');
  }

  /**
   * Set the animation to slide-right.
   */
  public function setAnimationSlideRight():void {
    $this->setAnimation('slide-right');
  }

  /**
   * Set the animation to zoom-in.
   */
  public function setAnimationZoomIn():void {
    $this->setAnimation('zoom-in');
  }

  /**
   * Set the animation to zoom-in-up.
   */
  public function setAnimationZoomInUp():void {
    $this->setAnimation('zoom-in-up');
  }

  /**
   * Set the animation to zoom-in-down.
   */
  public function setAnimationZoomInDown():void {
    $this->setAnimation('zoom-in-down');
  }

  /**
   * Set the animation to zoom-in-left.
   */
  public function setAnimationZoomInLeft():void {
    $this->setAnimation('zoom-in-left');
  }

  /**
   * Set the animation to zoom-in-right.
   */
  public function setAnimationZoomInRight():void {
    $this->setAnimation('zoom-in-right');
  }

  /**
   * Set the animation to zoom-out.
   */
  public function setAnimationZoomOut():void {
    $this->setAnimation('zoom-out');
  }

  /**
   * Set the animation to zoom-out-up.
   */
  public function setAnimationZoomOutUp():void {
    $this->setAnimation('zoom-out-up');
  }

  /**
   * Set the animation to zoom-out-down.
   */
  public function setAnimationZoomOutDown():void {
    $this->setAnimation('zoom-out-down');
  }

  /**
   * Set the animation to zoom-out-left.
   */
  public function setAnimationZoomOutLeft():void {
    $this->setAnimation('zoom-out-left');
  }

  /**
   * Set the animation to zoom-out-right.
   */
  public function setAnimationZoomOutRight():void {
    $this->setAnimation('zoom-out-right');
  }

  /**
   * Set the anchor.
   *
   * Element whose offset will be used to trigger animation instead of an
   * actual one.
   *
   * @param string $anchor
   *   The anchor.
   */
  public function setAnchor(string $anchor):void {
    $this->anchor = $anchor;
  }

  /**
   * Set the placement.
   *
   * @param string $placement
   *   The placement.
   */
  public function setPlacement(string $placement):void {
    if (!array_key_exists($placement, self::getPlacements())) {
      throw new \InvalidArgumentException('Invalid placement');
    }
    $this->anchorPlacement = $placement;
  }

  /**
   * Set the placement to top-center.
   */
  public function setPlacementTopCenter():void {
    $this->setPlacement('top-center');
  }

  /**
   * Set the placement to top-bottom.
   */
  public function setPlacementTopBottom():void {
    $this->setPlacement('top-bottom');
  }

  /**
   * Set the placement to top-top.
   */
  public function setPlacementTopTop():void {
    $this->setPlacement('top-top');
  }

  /**
   * Set the placement to center-center.
   */
  public function setPlacementCenterCenter():void {
    $this->setPlacement('center-center');
  }

  /**
   * Set the placement to center-bottom.
   */
  public function setPlacementCenterBottom():void {
    $this->setPlacement('center-bottom');
  }

  /**
   * Set the placement to center-top.
   */
  public function setPlacementCenterTop():void {
    $this->setPlacement('center-top');
  }

  /**
   * Set the placement to bottom-center.
   */
  public function setPlacementBottomCenter():void {
    $this->setPlacement('bottom-center');
  }

  /**
   * Set the placement to bottom-bottom.
   */
  public function setPlacementBottomBottom():void {
    $this->setPlacement('bottom-bottom');
  }

  /**
   * Set the placement to bottom-top.
   */
  public function setPlacementBottomTop():void {
    $this->setPlacement('bottom-top');
  }

  /**
   * Set the offset.
   *
   * Offset (in px) from the original trigger point.
   *
   * @param int $offset
   *   The offset.
   */
  public function setOffset(int $offset):void {
    $this->offset = $offset;
  }

  /**
   * Set the delay.
   *
   * Values from 0 to 3000, with step 50ms.
   *
   * @param int $delay
   *   The delay.
   */
  public function setDelay(int $delay):void {
    $this->delay = $delay;
  }

  /**
   * Set the duration.
   *
   * Values from 0 to 3000, with step 50ms.
   *
   * @param int $duration
   *   The duration.
   */
  public function setDuration(int $duration):void {
    $this->duration = $duration;
  }

  /**
   * Set once.
   *
   * Whether animation should happen only once - while scrolling down.
   *
   * @param bool $once
   *   The once.
   */
  public function setOnce(bool $once = TRUE):void {
    $this->once = $once;
  }

  /**
   * Set the mirror.
   *
   * Whether elements should animate out while scrolling past them.
   *
   * @param bool $mirror
   *   The mirror.
   */
  public function setMirror(bool $mirror = TRUE):void {
    $this->mirror = $mirror;
  }

  /**
   * Set the easing.
   *
   * @param string $easing
   *   The easing.
   */
  public function setEasing(string $easing):void {
    if (!array_key_exists($easing, self::getEasing())) {
      throw new \InvalidArgumentException('Invalid easing');
    }
    $this->easing = $easing;
  }

  /**
   * Set the easing to linear.
   */
  public function setEasingLinear():void {
    $this->setEasing('linear');
  }

  /**
   * Set the easing to ease.
   */
  public function setEasingEase():void {
    $this->setEasing('ease');
  }

  /**
   * Set the easing to ease-in.
   */
  public function setEasingEaseIn():void {
    $this->setEasing('ease-in');
  }

  /**
   * Set the easing to ease-out.
   */
  public function setEasingEaseOut():void {
    $this->setEasing('ease-out');
  }

  /**
   * Set the easing to ease-in-out.
   */
  public function setEasingEaseInOut():void {
    $this->setEasing('ease-in-out');
  }

  /**
   * Set the easing to ease-in-back.
   */
  public function setEasingEaseInBack():void {
    $this->setEasing('ease-in-back');
  }

  /**
   * Set the easing to ease-out-back.
   */
  public function setEasingEaseOutBack():void {
    $this->setEasing('ease-out-back');
  }

  /**
   * Set the easing to ease-in-out-back.
   */
  public function setEasingEaseInOutBack():void {
    $this->setEasing('ease-in-out-back');
  }

  /**
   * Set the easing to ease-in-sine.
   */
  public function setEasingEaseInSine():void {
    $this->setEasing('ease-in-sine');
  }

  /**
   * Set the easing to ease-out-sine.
   */
  public function setEasingEaseOutSine():void {
    $this->setEasing('ease-out-sine');
  }

  /**
   * Set the easing to ease-in-out-sine.
   */
  public function setEasingEaseInOutSine():void {
    $this->setEasing('ease-in-out-sine');
  }

  /**
   * Set the easing to ease-in-quad.
   */
  public function setEasingEaseInQuad():void {
    $this->setEasing('ease-in-quad');
  }

  /**
   * Set the easing to ease-out-quad.
   */
  public function setEasingEaseOutQuad():void {
    $this->setEasing('ease-out-quad');
  }

  /**
   * Set the easing to ease-in-out-quad.
   */
  public function setEasingEaseInOutQuad():void {
    $this->setEasing('ease-in-out-quad');
  }

  /**
   * Set the easing to ease-in-cubic.
   */
  public function setEasingEaseInCubic():void {
    $this->setEasing('ease-in-cubic');
  }

  /**
   * Set the easing to ease-out-cubic.
   */
  public function setEasingEaseOutCubic():void {
    $this->setEasing('ease-out-cubic');
  }

  /**
   * Set the easing to ease-in-out-cubic.
   */
  public function setEasingEaseInOutCubic():void {
    $this->setEasing('ease-in-out-cubic');
  }

  /**
   * Set the easing to ease-in-quart.
   */
  public function setEasingEaseInQuart():void {
    $this->setEasing('ease-in-quart');
  }

  /**
   * Set the easing to ease-out-quart.
   */
  public function setEasingEaseOutQuart():void {
    $this->setEasing('ease-out-quart');
  }

  /**
   * Set the easing to ease-in-out-quart.
   */
  public function setEasingEaseInOutQuart():void {
    $this->setEasing('ease-in-out-quart');
  }

  /**
   * Returns the attributes for the tooltip.
   *
   * @return \Drupal\Core\Template\Attribute
   *   The attributes.
   */
  public function getAttributes():Attribute {
    $attributes = [];
    $attributes['class'][] = 'use-neo-animation';

    $attributes['data-aos'] = $this->animation ?? $this->getSettings()->getValue('animation');
    if ($this->offset && $this->offset !== $this->getSettings()->getValue('offset')) {
      $attributes['data-offset'] = $this->offset;
    }
    if ($this->delay && $this->delay !== $this->getSettings()->getValue('delay')) {
      $attributes['data-aos-delay'] = $this->delay;
    }
    if ($this->duration && $this->duration !== $this->getSettings()->getValue('duration')) {
      $attributes['data-aos-duration'] = $this->duration;
    }
    if ($this->easing && $this->easing !== $this->getSettings()->getValue('easing')) {
      $attributes['data-aos-easing'] = $this->easing;
    }
    if (!is_null($this->once) && (bool) $this->once !== (bool) $this->getSettings()->getValue('once')) {
      $attributes['data-aos-once'] = $this->once ? 'true' : 'false';
    }
    if (!is_null($this->mirror) && (bool) $this->mirror !== (bool) $this->getSettings()->getValue('mirror')) {
      $attributes['data-aos-mirror'] = $this->mirror ? 'true' : 'false';
    }
    if ($this->anchor) {
      $attributes['data-aos-anchor'] = $this->anchor;
    }
    if ($this->anchorPlacement && $this->anchorPlacement !== $this->getSettings()->getValue('anchorPlacement')) {
      $attributes['data-aos-anchor-placement'] = $this->anchorPlacement;
    }
    return new Attribute($attributes);
  }

  /**
   * Returns the attachments for the modal.
   *
   * @return array
   *   The attachments.
   */
  public function getAttachments():array {
    $attachments = [];
    $attachments['library'][] = 'neo_animate/animate';
    $globalOptions = $this->getSettings()->getDiffConfigValues();
    unset($globalOptions['animation']);
    if ($globalOptions) {
      $attachments['drupalSettings']['neoAnimate']['defaults'] = $globalOptions;
    }
    return $attachments;
  }

  /**
   * Apply the animation to a renderable array.
   *
   * @param array $build
   *   The renderable array.
   */
  public function applyTo(array &$build):void {
    if (empty($build['#type']) || in_array($build['#type'], [
      'markup',
      'plain_text',
    ])) {
      $build = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        'value' => $build,
      ];
    }
    $build['#attributes'] = $build['#attributes'] ?? [];
    $attribute = new Attribute($build['#attributes']);
    $attribute->merge($this->getAttributes());
    $build['#attributes'] = $attribute->toArray();
    foreach ($this->getAttachments() as $attachmentType => $attachments) {
      foreach ($attachments as $key => $attachment) {
        $build['#attached'][$attachmentType][$key] = $attachment;
      }
    }
  }

}
