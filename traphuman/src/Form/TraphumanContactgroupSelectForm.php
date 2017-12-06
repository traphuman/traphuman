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
class TraphumanContactgroupSelectForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $cam = null) {

      $header = [
          'nid' => $this->t('Id'),
          'name' => $this->t('Name'),
          'contacts' => $this->t('Contacts'),
      ];
      $options = array();
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $contactgroups = $node_storage->loadByProperties(['type' => 'contactgroup', 'status' => 1]);
      foreach($contactgroups as $nid => $contactgroup) {
        // http://traphuman.devep.es/traphuman/contact-list/nid
        if($cam != NULL)
          $url_contacts = Url::fromRoute('traphuman.contact_list_gru_cam', array('gru' => $nid, 'cam' => $cam));
          else
          $url_contacts = Url::fromRoute('traphuman.contact_list_gru', array('gru' => $nid));
        $link_contacts = Link::fromTextAndUrl(t('Set contacts'), $url_contacts);
        $options[$nid] = [
            'nid' => $nid,
            'name' => $contactgroup->getTitle(),
            'contacts' => $link_contacts,
        ];

      }

      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($cam);
      if(!$nids_selected = $node->get('field_contactgroups')->getValue()[0]['target_id']) {
        $nids_selected = NULL;
      }
      else {
        $nids_selected = array();
        $nids_selected_all = $node->get('field_contactgroups')->getValue();
        foreach ($nids_selected_all as $nid_cg => $nid_val_cg) {
          $nids_selected[intval($nid_val_cg['target_id'])] = intval($nid_val_cg['target_id']);
        }
      }

      $build['contactgroup_selection'] = [
          '#type'    => 'tableselect',
          '#header'  => $header,
          '#options' => $options,
          '#multiple' => TRUE,
          '#empty'   => $this->t('No contact groups found'),
          '#default_value' => $nids_selected,
      ];

      $build['campaign_id'] = [
          '#type' => 'hidden',
          '#default_value' => $cam,
      ];

      $build['actions'] = [
          '#type' => 'actions',
      ];

      $build['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Save'),
      ];

      $build['actions']['edit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Edit group'),
      ];

      $build['actions']['delete'] = [
          '#type' => 'submit',
          '#value' => $this->t('Delete group'),
      ];

      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_contactgroups_selection';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
      /* TO DO */
      /* comprobar que han seleccionado una */
      /* TO DO */
      /* si se ha pulsado delete comprobar que ninguna campaña está usando la plantilla */
      $trigger = $form_state->getTriggeringElement();
      if(($trigger['#id'] == 'edit-edit')||($trigger['#id'] == 'edit-delete')) {
        $nids_ok = array();
        $nids = $form_state->getValue('contactgroup_selection');
        foreach ($nids as $key => $value) {
          if($value != 0) {
            $nids_ok[] = $value;
          }
        }
        if((count($nids_ok)) != 1) {
          $form_state->setErrorByName('contactgroup_selection', $this->t('You must select one single item.'));
        }
      }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      $nid_c = $form_state->getValue('campaign_id');
      $trigger = $form_state->getTriggeringElement();
      if($trigger['#id'] == 'edit-submit') {
        if($nids = $form_state->getValue('contactgroup_selection')) {
          $node_storage = \Drupal::entityTypeManager()->getStorage('node');
          $node = $node_storage->load($nid_c);
          $node->set('field_contactgroups', $nids);
          $node->save();
        }
        $form_state->setRedirect('traphuman.contactgroup_list_cam', array('cam' => $nid_c));
      }
      elseif($trigger['#id'] == 'edit-delete') {
          if($nids = $form_state->getValue('contactgroup_selection')) {
              foreach ($nids as $key => $value) {
                  if($value != 0) {
                      $nid = $value;
                  }
              }
              $form_state->setRedirect('traphuman.delete_confirm_contactgroup_cam', array('num' => $nid, 'cam' => $nid_c));
          }
          else {
              $form_state->setRedirect('traphuman.template_list_cam', array('cam' => $nid_c));
          }
          /*
          $node_storage = \Drupal::entityTypeManager()->getStorage('node');
          foreach ($nids as $knid) {
            if($node = $node_storage->load($knid)) {
              $node->delete();
            }
          }
        } */
        // $form_state->setRedirect('traphuman.contactgroup_list_cam', array('cam' => $nid_c));
      }
      elseif($trigger['#id'] == 'edit-edit') {
        if($nids = $form_state->getValue('contactgroup_selection')) {
          foreach ($nids as $key => $value) {
            if($value != 0) {
              $nid = $value;
            }
          }
          $form_state->setRedirect('traphuman.edit_contactgroup_cam', array('num' => $nid, 'cam' => $nid_c));
        }
        else {
          $form_state->setRedirect('traphuman.template_list_cam', array('cam' => $nid_c));
        }
      }
    }

}