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
class TraphumanOsintTweetDayFilterForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $tem = NULL) {


      $number = Xss::filter(\Drupal::request()->get('number'));
      if((!isset($number))||($number == '')) {
        $number = 1;
      }
      $date = Xss::filter(\Drupal::request()->get('date'));
      if((!isset($date))||($date == '')) {
        $date = date('Y-m-d');
      }

      $build['filters'] = [
          '#type'  => 'fieldset',
          '#title' => $this->t('Filter'),
      ];

      $build['filters']['date'] = [
          '#title'         => 'Date',
          '#type'          => 'date',
          '#default_value' => $date
      ];

      $build['filters']['number'] = [
          '#title'         => 'Min Times',
          '#type'          => 'number',
          '#min'           => 1,
          '#max'           => 10,
          '#default_value' => $number
      ];

      $build['filters']['actions'] = [
          '#type'       => 'actions'
      ];

      $build['filters']['actions']['submit'] = [
          '#type'  => 'submit',
          '#value' => $this->t('Filter')
      ];
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_template_osint_filter_day';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      $form_state->setRedirect(
          'traphuman.osint_tweetconsult_byday',
              [
                  'number' => $form_state->getValue('number'),
                  'date'   => $form_state->getValue('date'),
              ]);
    }

}