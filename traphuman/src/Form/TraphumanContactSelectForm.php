<?php

namespace Drupal\traphuman\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Implements the Simple form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class TraphumanContactSelectForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $gru = null, $cam = null) {

      $header = [
          'nid' => $this->t('Id'),
          'name' => $this->t('Name'),
          'email' => $this->t('Email'),
      ];
      $options = array();
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $contacts = $node_storage->loadByProperties(['type' => 'contact', 'status' => 1]);
      foreach($contacts as $nid => $contact) {
        $options[$nid] = [
            'nid' => $nid,
            'name' => $contact->getTitle(),
            'email' => $contact->get('field_email')->getValue()[0]['value'],
        ];

      }

      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($gru);
      if(!$nids_selected = $node->get('field_contacts')->getValue()[0]['target_id']) {
        $nids_selected = NULL;
      }
      else {
        $nids_selected = array();
        $nids_selected_all = $node->get('field_contacts')->getValue();
        foreach ($nids_selected_all as $nid_cg => $nid_val_cg) {
          $nids_selected[intval($nid_val_cg['target_id'])] = intval($nid_val_cg['target_id']);
        }
      }

      $build['contact_selection'] = [
          '#type'    => 'tableselect',
          '#header'  => $header,
          '#options' => $options,
          '#multiple' => TRUE,
          '#empty'   => $this->t('No contact groups found'),
          '#default_value' => $nids_selected,
      ];

      $build['contactgroup_id'] = [
          '#type' => 'hidden',
          '#default_value' => $gru,
      ];

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
          '#value' => $this->t('Save'),
      ];

      $build['actions']['edit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Edit contact'),
      ];

      $build['actions']['delete'] = [
          '#type' => 'submit',
          '#value' => $this->t('Delete contact'),
      ];

      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_contacts_selection';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
      /* TO DO */
      /* comprobar que han seleccionado una */
      /* TO DO */
      /* si se ha pulsado delete comprobar que ninguna campaña está usando la plantilla */
      $trigger = $form_state->getTriggeringElement();
      if(($trigger['#id'] == 'edit-edit')||($trigger['#id'] == 'edit-delete')) {
        $nids_ok = array();
        $nids = $form_state->getValue('contact_selection');
        foreach ($nids as $key => $value) {
          if($value != 0) {
            $nids_ok[] = $value;
          }
        }
        if((count($nids_ok)) != 1) {
          $form_state->setErrorByName('contact_selection', $this->t('You must select one single item.'));
        }
      }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      $nid_c = $form_state->getValue('contactgroup_id');
      $trigger = $form_state->getTriggeringElement();
      if($trigger['#id'] == 'edit-submit') {
        if($nids = $form_state->getValue('contact_selection')) {
          $node_storage = \Drupal::entityTypeManager()->getStorage('node');
          $node = $node_storage->load($nid_c);
          $node->set('field_contacts', $nids);
          $node->save();
        }
        // Si va a la misma página no es necesario redirect
      }
      elseif($trigger['#id'] == 'edit-delete') {
        if($nids = $form_state->getValue('contact_selection')) {
          foreach ($nids as $key => $value) {
            if($value != 0) {
              $nid = $value;
            }
          }
          if($nid_cam = $form_state->getValue('campaign_id')) {
            $form_state->setRedirect('traphuman.delete_confirm_contact_gru_cam', array('num' => $nid, 'gru' => $nid_c, 'cam' => $nid_cam));
          }
          else {
            $form_state->setRedirect('traphuman.delete_confirm_contact_gru', array('num' => $nid, 'gru' => $nid_c));
          }
        }
        else {
          $form_state->setRedirect('traphuman.contact_list_gru', array('gru' => $nid_c));
        }
        /*
        if($nids = $form_state->getValue('contact_selection')) {
          $node_storage = \Drupal::entityTypeManager()->getStorage('node');
          foreach ($nids as $knid) {
            if($node = $node_storage->load($knid)) {
              $node->delete();
            }
          }
        } */
      }
      elseif($trigger['#id'] == 'edit-edit') {
        if($nids = $form_state->getValue('contact_selection')) {
          foreach ($nids as $key => $value) {
            if($value != 0) {
              $nid = $value;
            }
          }
          if($nid_cam = $form_state->getValue('campaign_id')) {
            $form_state->setRedirect('traphuman.edit_contact_gru_cam', array('num' => $nid, 'gru' => $nid_c, 'cam' => $nid_cam));
          }
          else {
            $form_state->setRedirect('traphuman.edit_contact_gru', array('num' => $nid, 'gru' => $nid_c));
          }
        }
        else {
          $form_state->setRedirect('traphuman.contact_list_gru', array('gru' => $nid_c));
        }
      }
    }

}