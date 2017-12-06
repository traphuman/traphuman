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
class TraphumanTemplateWizardStep2Form extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $tem = NULL) {

      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      if($node = $node_storage->load($tem)) {
        $build['template_id'] = [
            '#type' => 'hidden',
            '#default_value' => $tem,
        ];
        $build['template_sender'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Sender'),
            '#description' => $this->t('The name must be at least 5 characters long.'),
            '#default_value' => '',
            '#required' => FALSE,
        ];
        $build['template_company_supplanted'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Company supplanted'),
            '#description' => $this->t('The name must be at least 5 characters long.'),
            '#default_value' => '',
            '#required' => FALSE,
        ];
        $build['actions'] = [
            '#type' => 'actions',
        ];

        $build['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Next >'),
        ];
      }
      else {
        $build['preview'] = [
            '#markup' => '<h1>Template unknown</h1>',
        ];
      }
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_template_wizard_step2';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $template_sender = $form_state->getValue('template_sender');
        if (strlen($template_sender) < 5) {
            // Set an error for the form element with a key of "title".
            $form_state->setErrorByName('template_sender', $this->t('The name must be at least 5 characters long.'));
        }
        $template_company_supplanted = $form_state->getValue('template_company_supplanted');
        if (strlen($template_company_supplanted) < 5) {
          // Set an error for the form element with a key of "title".
          $form_state->setErrorByName('template_company_supplanted', $this->t('The name must be at least 5 characters long.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      if($tem = $form_state->getValue('template_id')) {
        $template_sender = $form_state->getValue('template_sender');
        $template_company_supplanted = $form_state->getValue('template_company_supplanted');
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($tem);
        $node->set('field_sender', $template_sender);
        $node->set('field_company_suplantted', $template_company_supplanted);
        $node->save();
      }
      else {
        \Drupal\Core\Form\drupal_set_message('Error.');
      }
      $form_state->setRedirect('traphuman.template_wizard_step3', array('tem' => $tem));
    }

}