<?php

namespace Drupal\traphuman\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TweetAccountEntityForm.
 */
class TweetAccountEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $tweet_account_entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $tweet_account_entity->label(),
      '#description' => $this->t("Label for the Tweet account entity."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $tweet_account_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\traphuman\Entity\TweetAccountEntity::load',
      ],
      '#disabled' => !$tweet_account_entity->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */
    $form['account'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Twitter Account'),
        '#maxlength' => 255,
        '#default_value' => $tweet_account_entity->getAccount(),
        '#description' => $this->t("Twitter Account."),
        '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $tweet_account_entity = $this->entity;
    $status = $tweet_account_entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Tweet account entity.', [
          '%label' => $tweet_account_entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Tweet account entity.', [
          '%label' => $tweet_account_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($tweet_account_entity->toUrl('collection'));
  }

}
