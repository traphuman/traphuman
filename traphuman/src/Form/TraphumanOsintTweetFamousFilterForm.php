<?php

namespace Drupal\traphuman\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Xss;

/**
 * Implements the Simple form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class TraphumanOsintTweetFamousFilterForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $tem = NULL) {

      $query = \Drupal::entityQuery('tweet_account_entity');
      $accounts = $query->execute();

      /*
      $account_storage = \Drupal::entityTypeManager()->getStorage('tweet_account_entity');
      foreach($accounts as $account_id) {
        $account = $account_storage->load($account_id);
      }; */

      $build = array();

      if((count($accounts))>0) {

        $famous = Xss::filter(\Drupal::request()->get('famous'));
        if ((!isset($famous)) || ($famous == '')) {
          $famous = $accounts[0];
        }

        $build['filters'] = [
            '#type' => 'fieldset',
            '#title' => $this->t('Filter'),
        ];

        $build['filters']['famous'] = [
            '#title' => 'Famous',
            '#type' => 'select',
            '#options' => $accounts,
            '#default_value' => $famous,
        ];

        $build['filters']['actions'] = [
            '#type' => 'actions'
        ];

        $build['filters']['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Filter')
        ];
      }
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_template_osint_filter_famous';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      $form_state->setRedirect(
          'traphuman.osint_tweetconsult_famousauthor',
              [
                  'famous' => $form_state->getValue('famous'),
              ]);
    }

}