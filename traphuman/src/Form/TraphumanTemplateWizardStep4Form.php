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
class TraphumanTemplateWizardStep4Form extends FormBase {

  protected $current_user;
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

    public function buildForm(array $form, FormStateInterface $form_state, $tem = NULL) {

      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      if($node = $node_storage->load($tem)) {
        $build['template_id'] = [
            '#type' => 'hidden',
            '#default_value' => $tem,
        ];
        $build['template_attachment'] = [
            '#title' => $this->t('Upload file'),
            '#type' => 'managed_file',
            '#upload_location' => 'public://managed',
            '#upload_validators' => [
                'file_validate_extensions' => ['pdf xls xlsx doc docx'],
            ],
        ];
        $build['actions'] = [
            '#type' => 'actions',
        ];

        $build['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Finish'),
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
        return 'traphuman_forms_template_wizard_step4';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      if($tem = $form_state->getValue('template_id')) {
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

        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($tem);
        $node->set('field_attachment', $field_file);
        $node->save();
      }
      else {
        \Drupal\Core\Form\drupal_set_message('Error.');
      }
      $form_state->setRedirect('traphuman.template_wizard_finish', array('tem' => $tem));
    }

}