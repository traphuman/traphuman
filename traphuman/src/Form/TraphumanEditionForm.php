<?php

namespace Drupal\traphuman\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Implements the Simple form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class TraphumanEditionForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $cam = NULL) {


      $build['edition_time'] = [
          '#type' => 'datetime',
          '#title' => $this->t('Date of start to send'),
          '#description' => $this->t('Select the date to start sending.'),
          '#default_value' => DrupalDateTime::createFromTimestamp(time()),
          '#required' => TRUE,
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
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_edition';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $edition_time = $form_state->getValue('edition_time');
        $campaign_id = $form_state->getValue('campaign_id');

        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node_campaign = $node_storage->load($campaign_id);

        $node_template = $node_storage->load($node_campaign->get('field_template')->getValue()[0]['target_id']);
        $node = Node::create([
            'type' => 'edition',
            'title' => $node_template->getTitle(),
            'body' => $node_template->get('body')->getValue()[0]['value'],
            'field_attachment' => $node_template->get('field_attachment')->getValue(),
            'field_campaign' => $campaign_id,
            'field_start_date' => strtotime($edition_time->format('Y/m/d H:i:s')),
            'field_finish_date' => 0,
            'field_percent_sent' => 0,
            'field_percent_open' => 0,
            'field_company_suplantted' => $node_template->get('field_company_suplantted')->getValue(),
            'field_sender' => $node_template->get('field_sender')->getValue(),
        ]);
        $node->save();

        $groups = $node_campaign->get('field_contactgroups')->getValue();
        foreach ($groups as $key => $group) {
            if($node_group = $node_storage->load($group['target_id'])) {
              $contacts = $node_group->get('field_contacts')->getValue();
              foreach ($contacts as $key => $contact) {
                if ($node_contact = $node_storage->load($contact['target_id'])) {
                  $node_mail = Node::create([
                      'type' => 'mail',
                      'title' => $node_template->getTitle(),
                      'field_edition' => $node->ID(),
                      'field_email' => $node_contact->get('field_email')->getValue()[0]['value'],
                      'field_attachment_click_date' => 0,
                      'field_link_click_date' => 0,
                      'field_open_date' => 0,
                      'field_sent_date' => 0,
                  ]);
                  $node_mail->save();
                }
              }
            }
        }

    }

}