<?php

namespace Drupal\traphuman\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;

/**
 * Implements the Simple form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class TraphumanContactForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $num = NULL, $gru = NULL, $cam = NULL) {
      if($num != NULL) {
        $build['contact_id'] = [
            '#type' => 'hidden',
            '#default_value' => $num,
        ];
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load(intval($num));
        $contact_name = $node->getTitle();
        $contact_email = $node->get('field_email')->getValue()[0]['value'];
      }
      else {
        $contact_name = '';
        $contact_email = '';
      }

      $build['contact_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Name of Contact'),
          '#description' => $this->t('The name must be at least 5 characters long.'),
          '#default_value' => $contact_name,
          '#required' => TRUE,
      ];
      $build['contact_email'] = [
          '#type' => 'email',
          '#title' => $this->t('Email of Contact'),
          '#description' => $this->t('The name must be at least 5 characters long.'),
          '#default_value' => $contact_email,
          '#required' => TRUE,
      ];

      if($gru != NULL) {
          $build['contactgroup_id'] = [
              '#type' => 'hidden',
              '#default_value' => $gru,
          ];
      }

      if($cam != NULL) {
        $build['campaign_id'] = [
            '#type' => 'hidden',
            '#default_value' => $cam,
        ];
      }

      $build['actions'] = [
          '#type' => 'actions',
      ];

      $build['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
      ];
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_contact';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $title = $form_state->getValue('contact_name');
        if (strlen($title) < 5) {
            // Set an error for the form element with a key of "title".
            $form_state->setErrorByName('contact_name', $this->t('The name must be at least 5 characters long.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      $title = $form_state->getValue('contact_name');
      $email = $form_state->getValue('contact_email');
      if($nid = $form_state->getValue('contact_id')) {
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($nid);
        $node->setTitle($title);
        $node->set('field_email', $email);
      }
      else {
        // Create node object with attached file.
        $node = Node::create([
            'type' => 'contact',
            'title' => $title,
            'field_email' => $email,
        ]);
      }
      $node->save();
      if($nid_c = $form_state->getValue('contactgroup_id')) {
        if($nid = $form_state->getValue('contact_id')) {
          if ($nid_cam = $form_state->getValue('campaign_id')) {
            $form_state->setRedirect('traphuman.contact_list_gru_cam', array('gru' => $nid_c, 'cam' => $nid_cam));
          } else {
            $form_state->setRedirect('traphuman.contact_list_gru', array('gru' => $nid_c));
          }
        }
      }
      else {
        $form_state->setRedirect('traphuman.contact_list');
      }

    }

}