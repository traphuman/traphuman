<?php

namespace Drupal\traphuman\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Implements the Simple form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class TraphumanTemplateForm extends FormBase {

    protected $currentUser;
    protected $fileUsage;

    public function __construct(AccountInterface $current_user,
                                DatabaseFileUsageBackend $file_usage) {
        $this->currentUser = $current_user;
        $this->fileUsage = $file_usage;
    }
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('current_user'),
            $container->get('file.usage')
        );
    }


    public function buildForm(array $form, FormStateInterface $form_state, $cam = NULL, $num = NULL) {
      $current_path = \Drupal::service('path.current')->getPath();
      $path_args = explode('/', $current_path);
      if(((count($path_args)) == 5)||($num != NULL)) {
        if((count($path_args)) == 5)
          $template_id = $path_args[4];
        else
          $template_id = $num;
        $build['template_id'] = [
            '#type' => 'hidden',
            '#default_value' => $template_id,
        ];
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($template_id);
        $template_name = $node->getTitle();
        $template_body = $node->get('body')->getValue()[0]['value'];
        $template_sender = $node->get('field_sender')->getValue()[0]['value'];
        $template_company_supplanted = $node->get('field_company_suplantted')->getValue()[0]['value'];
        $template_attachment = $node->get('field_attachment')->getValue()[0]['target_id'];
      }
      else {
        $template_name = '';
        $template_body = '';
        $template_sender = '';
        $template_company_supplanted = '';
        $template_attachment = '';
      }

      $build['template_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Name of Template'),
          '#description' => $this->t('The name must be at least 5 characters long.'),
          '#default_value' => $template_name,
          '#required' => TRUE,
      ];
      $build['template_body'] = [
          '#type' => 'textarea',
          '#title' => $this->t('HTML body of Template'),
          '#description' => $this->t('Paste your HTML template here.'),
          '#default_value' => $template_body,
          '#required' => TRUE,
      ];
      $build['template_sender'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Sender'),
          '#description' => $this->t('The name must be at least 5 characters long.'),
          '#default_value' => $template_sender,
          '#required' => FALSE,
      ];
      $build['template_company_supplanted'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Company supplanted'),
          '#description' => $this->t('The name must be at least 5 characters long.'),
          '#default_value' => $template_company_supplanted,
          '#required' => FALSE,
      ];

      $build['template_attachment'] = [
          '#title' => $this->t('Upload file'),
          '#type' => 'managed_file',
          '#upload_location' => 'public://managed',
          '#upload_validators' => [
              'file_validate_extensions' => ['pdf xls xlsx doc docx'],
          ],
          '#default_value' => array($template_attachment),
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
        $build['preview'] = [
            '#markup' => '<h1>Preview</h1><hr /><hr />'.$template_body.'<hr /><hr /><h1>END Preview</h1>',
        ];
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_template';
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
      $body = $form_state->getValue('template_body');
      $template_sender = $form_state->getValue('template_sender');
      $template_company_supplanted = $form_state->getValue('template_company_supplanted');
        $file_storage = \Drupal::entityTypeManager()->getStorage('file');
        foreach($form_state->getValue('template_attachment') as $fid){
            $file = $file_storage->load($fid);
            $this->fileUsage->add($file, 'traphuman', 'user', $this->currentUser->id(), 1);
        }
      if($form_state->getValue('template_attachment')) {
        $field_file = $file->id();
      }
      else {
        $field_file = FALSE;
      }
      if($nid = $form_state->getValue('template_id')) {
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($nid);
        $node->setTitle($title);
        $node->set('body', $body);
        $node->set('field_sender', $template_sender);
        $node->set('field_company_suplantted', $template_company_supplanted);
        $node->set('field_attachment', $field_file);
      }
      else {
        // Create node object with attached file.
        $node = Node::create([
            'type' => 'template',
            'title' => $title,
            'body' => $body,
            'field_sender' => $template_sender,
            'field_company_suplantted' => $template_company_supplanted,
            'field_attachment' => $field_file,
        ]);
      }
      $node->save();
      if($nid_c = $form_state->getValue('campaign_id')) {
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node_c = $node_storage->load($nid_c);
        $node_c->set('field_template', $node->id());
        $node_c->save();
        $form_state->setRedirect('traphuman.template_list_cam', array('cam' => $nid_c));
      }
      else {
        $form_state->setRedirect('traphuman.template_list');
      }
    }

}