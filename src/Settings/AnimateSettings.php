<?php

namespace Drupal\neo_animate\Settings;

use Drupal\Core\Form\FormStateInterface;
use Drupal\neo_animate\Animate;
use Drupal\neo_settings\Plugin\SettingsBase;

/**
 * Module settings.
 *
 * @Settings(
 *   id = "neo_animate",
 *   label = @Translation("Neo Animate"),
 *   config_name = "neo_animate.settings",
 *   menu_title = @Translation("Animate"),
 *   route = "/admin/config/neo/neo-animate",
 *   admin_permission = "administer neo_animate",
 *   variation_allow = false,
 *   variation_conditions = false,
 *   variation_ordering = false,
 * )
 */
class AnimateSettings extends SettingsBase {

  /**
   * {@inheritdoc}
   */
  protected function buildBaseForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildBaseForm($form, $form_state);

    $form['disable'] = [
      '#type' => 'select',
      '#title' => $this->t('Disable'),
      '#description' => $this->t('Disable the animations.'),
      '#default_value' => $this->getValue('disable'),
      '#options' => [
        '' => $this->t('None'),
        'phone' => $this->t('Phone'),
        'tablet' => $this->t('Tablet'),
        'mobile' => $this->t('Mobile'),
      ],
    ];

    $form['initClassName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Init Class Name'),
      '#description' => $this->t('The class name that will be added to the element when initialized.'),
      '#default_value' => $this->getValue('initClassName'),
    ];

    $form['animatedClassName'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Animated Class Name'),
      '#description' => $this->t('The class name that will be added to the element when animating.'),
      '#default_value' => $this->getValue('animatedClassName'),
    ];

    $form['useClassNames'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Class Names'),
      '#description' => $this->t('If true, will add content of `data-aos` as classes on scroll.'),
      '#default_value' => $this->getValue('useClassNames'),
    ];

    $form['disableMutationObserver'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Mutation Observer'),
      '#description' => $this->t("Disable automatic mutations' detections."),
      '#default_value' => $this->getValue('disableMutationObserver'),
    ];

    $form['debounceDelay'] = [
      '#type' => 'number',
      '#title' => $this->t('Debounce Delay'),
      '#description' => $this->t('The delay on debounce used while resizing window.'),
      '#default_value' => $this->getValue('debounceDelay'),
    ];

    $form['throttleDelay'] = [
      '#type' => 'number',
      '#title' => $this->t('Throttle Delay'),
      '#description' => $this->t('The delay on throttle used while scrolling the page.'),
      '#default_value' => $this->getValue('throttleDelay'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Instance settings are settings that are set both in the base form and the
   * variation form. They are editable in both forms and the values are merged
   * together.
   */
  protected function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['animation'] = [
      '#type' => 'select',
      '#title' => $this->t('Animation'),
      '#description' => $this->t('The animation to apply.'),
      '#default_value' => $this->getValue('animation'),
      '#options' => Animate::getAnimations(),
    ];

    $form['offset'] = [
      '#type' => 'number',
      '#title' => $this->t('Offset'),
      '#description' => $this->t('Offset (in px) from the original trigger point.'),
      '#default_value' => $this->getValue('offset'),
    ];

    $form['delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Delay'),
      '#description' => $this->t('Values from 0 to 3000, with step 50ms.'),
      '#default_value' => $this->getValue('delay'),
    ];

    $form['duration'] = [
      '#type' => 'number',
      '#title' => $this->t('Duration'),
      '#description' => $this->t('Values from 50 to 3000, with step 50ms.'),
      '#default_value' => $this->getValue('duration'),
    ];

    $form['easing'] = [
      '#type' => 'select',
      '#title' => $this->t('Easing'),
      '#description' => $this->t('The easing function to use.'),
      '#default_value' => $this->getValue('easing'),
      '#options' => Animate::getEasing(),
    ];

    $form['mirror'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mirror'),
      '#description' => $this->t('If true, will mirror the animation (will run the animation the same way in and out).'),
      '#default_value' => $this->getValue('mirror'),
    ];

    $form['once'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Once'),
      '#description' => $this->t('If true, will only run the animation once.'),
      '#default_value' => $this->getValue('once'),
    ];

    $form['anchorPlacement'] = [
      '#type' => 'select',
      '#title' => $this->t('Anchor Placement'),
      '#description' => $this->t('Anchor placement - which one of the element sides to use for the anchor.'),
      '#default_value' => $this->getValue('anchorPlacement'),
      '#options' => Animate::getPlacements(),
    ];

    // $form[]['#markup'] = '<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
    // $form['animate1'] = [
    //   '#markup' => $this->t('Animate1'),
    // ];
    // $form[]['#markup'] = '<br><br><br><br><br><br><br><br><br>';
    // $form['animate2'] = [
    //   '#markup' => $this->t('Animate1'),
    // ];
    // $animation = new Animate();
    // // $animation->setAnimationFadeUp();
    // $animation->setMirror();
    // $animation->applyTo($form['animate1']);
    // $animation->applyTo($form['animate2']);
    // $form[]['#markup'] = '<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
    return $form;
  }

}
