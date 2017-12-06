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
class TraphumanTemplateWizardStep1Form extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state) {
      $build['template_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Name of Template'),
          '#description' => $this->t('The name must be at least 5 characters long.'),
          '#default_value' => '',
          '#required' => TRUE,
      ];

      $build['actions'] = [
          '#type' => 'actions',
      ];

      $build['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Next >'),
      ];

      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_template_wizard_step1';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $title = $form_state->getValue('template_name');
        if (strlen($title) < 5) {
            // Set an error for the form element with a key of "title".
            $form_state->setErrorByName('template_name', $this->t('The name must be at least 5 characters long.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      $title = $form_state->getValue('template_name');
      $node = Node::create([
          'type' => 'template',
          'title' => $title,
          'body' => '',
          'field_sender' => '',
          'field_company_suplantted' => '',
      ]);
      $node->save();
      $form_state->setRedirect('traphuman.template_wizard_step2', array('tem' => $node->id()));
    }

}