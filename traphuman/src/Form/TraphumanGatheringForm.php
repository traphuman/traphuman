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
class TraphumanGatheringForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state) {

      $build['company_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Company\' s name'),
          '#description' => $this->t('The name must be at least 5 characters long.'),
          '#required' => TRUE,
      ];
      $build['domain'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Domain'),
          '#description' => $this->t('i.e: example.com'),
          '#required' => TRUE,
      ];

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
        return 'traphuman_forms_gathering';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      $company_name = $form_state->getValue('company_name');
      $domain = $form_state->getValue('domain');
      if($output = shell_exec('gathering_info.sh '.$domain.' "'.$company_name.'"')) {
        drupal_set_message($output);
      }
      else {
        $output = shell_exec('du ./* -hs');
        drupal_set_message('Error in script execution: '.$output);
      }
    }

}