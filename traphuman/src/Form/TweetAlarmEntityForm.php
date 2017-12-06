<?php

namespace Drupal\traphuman\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TweetAlarmEntityForm.
 */
class TweetAlarmEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $tweet_alarm_entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $tweet_alarm_entity->label(),
      '#description' => $this->t("Label for the Tweet alarm entity."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $tweet_alarm_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\traphuman\Entity\TweetAlarmEntity::load',
      ],
      '#disabled' => !$tweet_alarm_entity->isNew(),
    ];

    $form['mail'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Mail'),
        '#maxlength' => 255,
        '#default_value' => $tweet_alarm_entity->getMail(),
        '#description' => $this->t("Mail."),
        '#required' => TRUE,
    ];

    $form['account'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Twitter Account'),
        '#maxlength' => 255,
        '#default_value' => $tweet_alarm_entity->getAccount(),
        '#description' => $this->t("Twitter Account."),
        '#required' => TRUE,
    ];

    /*
    $form['lastsend'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Last send'),
        '#maxlength' => 255,
        '#default_value' => $tweet_alarm_entity->getLastsend(),
        '#description' => $this->t("Last send."),
        '#required' => TRUE,
    ]; */

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $tweet_alarm_entity = $this->entity;
    if($tweet_alarm_entity->getLastsend() == '') {
      $tweet_alarm_entity->setLastsend(time());
    }
    $status = $tweet_alarm_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Tweet alarm entity.', [
          '%label' => $tweet_alarm_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Tweet alarm entity.', [
          '%label' => $tweet_alarm_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($tweet_alarm_entity->toUrl('collection'));
  }

}
