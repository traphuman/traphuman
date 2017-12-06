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
class TraphumanAddCampaign extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state) {
      $current_path = \Drupal::service('path.current')->getPath();
      $path_args = explode('/', $current_path);
      if((count($path_args)) == 5) {
        $build = $this->getForm($path_args[4]);
        $build['campaign_id'] = [
            '#type' => 'hidden',
            '#default_value' => $path_args[4],
        ];
      }
      else {
        $build = $this->getForm();
      }
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_addcampaign';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $title = $form_state->getValue('campaign_name');
        if (strlen($title) < 5) {
            // Set an error for the form element with a key of "title".
            $form_state->setErrorByName('campaign_name', $this->t('The name must be at least 5 characters long.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      $trigger = $form_state->getTriggeringElement();
      if($trigger['#id'] == 'edit-submit') {
        $title = $form_state->getValue('campaign_name');
        if($nid = $form_state->getValue('campaign_id')) {
          $node_storage = \Drupal::entityTypeManager()->getStorage('node');
          $node = $node_storage->load($nid);
          $node->setTitle($title);
        }
        else {
          // Create node object with attached file.
          $node = Node::create([
              'type' => 'campaign',
              'title' => $title,
          ]);
        }
        $node->save();
        $form_state->setRedirect('traphuman.campaign_list');
      }
      elseif($trigger['#id'] == 'edit-settemplate') {
        if($nid = $form_state->getValue('campaign_id')) {
          $form_state->setRedirect('traphuman.template_list_cam', array('cam' => $nid));
        }
      }
      elseif($trigger['#id'] == 'edit-setgroups') {
        if($nid = $form_state->getValue('campaign_id')) {
          $form_state->setRedirect('traphuman.contactgroup_list_cam', array('cam' => $nid));
        }
      }
    }

    public function getForm($nid = NULL) {
      if($nid != NULL) {
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($nid);
        $campaign_name = $node->getTitle();
      }
      else {
        $campaign_name = '';
      }
      $form['campaign_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name of Campaign'),
        '#default_value' => $campaign_name,
        '#description' => $this->t('The name must be at least 5 characters long.'),
        '#required' => TRUE,
      ];
      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ];
      if($nid != NULL) {
        $form['actions']['settemplate'] = [
            '#type' => 'submit',
            '#value' => $this->t('Set Template >'),
        ];
        $form['actions']['setgroups'] = [
            '#type' => 'submit',
            '#value' => $this->t('Set Groups >'),
        ];
      }
      return $form;
    }
}