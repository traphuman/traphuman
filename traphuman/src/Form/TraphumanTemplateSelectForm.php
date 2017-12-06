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
class TraphumanTemplateSelectForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $cam = null) {

      $header = [
          'nid' => $this->t('Id'),
          'name' => $this->t('Name'),
          'sender' => $this->t('Sender'),
          'edit' => $this->t('Edit'),
          'delete' => $this->t('Delete'),
      ];
      $options = array();
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $templates = $node_storage->loadByProperties(['type' => 'template', 'status' => 1]);
      foreach($templates as $nid => $template) {
        $url_delete = Url::fromRoute('traphuman.delete_confirm_template_cam', array('num' => $nid, 'cam' => $cam));
        $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
        $url_edit = Url::fromRoute('traphuman.edit_template_cam', array('num' => $nid, 'cam' => $cam));
        $link_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);
        $options[$nid] = [
            'nid' => $nid,
            'name' => $template->getTitle(),
            'sender' => $template->get('field_sender')->getValue()[0]['value'],
            'edit' => $link_edit,
            'delete' => $link_delete,
        ];

      }

      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($cam);
      if(!$nids_selected = $node->get('field_template')->getValue()[0]['target_id']) {
        $nids_selected = NULL;
      }

      $build['template_selection'] = [
          '#type'    => 'tableselect',
          '#header'  => $header,
          '#options' => $options,
          '#multiple' => FALSE,
          '#empty'   => $this->t('No templates found'),
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

      /* $build['actions']['edit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Edit template'),
      ];

      $build['actions']['delete'] = [
          '#type' => 'submit',
          '#value' => $this->t('Delete template'),
      ]; */

      $build['actions']['selectgroups'] = [
          '#type' => 'submit',
          '#value' => $this->t('Select groups'),
      ];

      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_template_selection';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
      /* TO DO */
      /* comprobar que han seleccionado una */
      /* TO DO */
      /* si se ha pulsado delete comprobar que ninguna campaña está usando la plantilla */
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      $nid_c = $form_state->getValue('campaign_id');
      $trigger = $form_state->getTriggeringElement();
      if($trigger['#id'] == 'edit-submit') {
        if($nid = $form_state->getValue('template_selection')) {
          $node_storage = \Drupal::entityTypeManager()->getStorage('node');
          $node = $node_storage->load($nid_c);
          $node->set('field_template', $nid);
          $node->save();
        }
        $form_state->setRedirect('traphuman.template_list_cam', array('cam' => $nid_c));
      }
      /*
      elseif($trigger['#id'] == 'edit-delete') {
        if($nid = $form_state->getValue('template_selection')) {
          $node_storage = \Drupal::entityTypeManager()->getStorage('node');
          $node = $node_storage->load($nid);
          $node->delete();
        }
        $form_state->setRedirect('traphuman.template_list_cam', array('cam' => $nid_c));
      }
      elseif($trigger['#id'] == 'edit-edit') {
        if($nid = $form_state->getValue('template_selection')) {
          $form_state->setRedirect('traphuman.edit_template_cam', array('num' => $nid, 'cam' => $nid_c));
        }
        else {
          $form_state->setRedirect('traphuman.template_list_cam', array('cam' => $nid_c));
        }
      } */
      elseif($trigger['#id'] == 'edit-selectgroups') {
        $form_state->setRedirect('traphuman.contactgroup_list_cam', array('cam' => $nid_c));
      }
    }

}