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
class TraphumanTemplateWizardStep3Form extends FormBase {

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
        $build['template_h1'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Header 1'),
            '#description' => $this->t('The name must be at least 5 characters long.'),
            '#default_value' => '',
            '#required' => FALSE,
        ];
        $build['template_logo'] = [
            '#title' => $this->t('Upload logo'),
            '#type' => 'managed_file',
            '#upload_location' => 'public://managed',
            '#upload_validators' => [
                'file_validate_extensions' => ['jpg jpeg gif png'],
            ],
        ];
        $build['template_h2'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Header 2'),
            '#description' => $this->t('The name must be at least 5 characters long.'),
            '#default_value' => '',
            '#required' => FALSE,
        ];
        $build['template_body1'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Text before link'),
            '#description' => $this->t('Paste your HTML here.'),
            '#default_value' => '',
            '#required' => TRUE,
        ];
        $build['template_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('URL text'),
            '#description' => $this->t('The name must be at least 5 characters long.'),
            '#default_value' => '',
            '#required' => FALSE,
        ];
        $build['template_color'] = array(
            '#type' => 'color',
            '#title' => $this->t('URL color'),
            '#default_value' => '#000000',
        );
        $build['template_body2'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Text after link'),
            '#description' => $this->t('Paste your HTML here.'),
            '#default_value' => '',
            '#required' => TRUE,
        ];
        $build['template_sign'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Sign'),
            '#description' => $this->t('Paste your HTML here.'),
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
      }
      else {
        $build['preview'] = [
            '#markup' => '<h1>Template unknown</h1>',
        ];
      }
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_template_wizard_step3';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      if($tem = $form_state->getValue('template_id')) {
        $template_h1 = $form_state->getValue('template_h1');
        $template_h2 = $form_state->getValue('template_h2');
        $template_body1 = $form_state->getValue('template_body1');
        $template_body2 = $form_state->getValue('template_body2');
        $template_color = $form_state->getValue('template_color');
        $template_sign = $form_state->getValue('template_sign');
        $template_url = $form_state->getValue('template_url');
        $file_storage = \Drupal::entityTypeManager()->getStorage('file');
        foreach($form_state->getValue('template_logo') as $fid){
          $file = $file_storage->load($fid);
          $this->fileUsage->add($file, 'traphuman', 'user', $this->currentUser->id(), 1);
        }
        $template_logo = file_create_url($file->getFileUri());

        $body = '<img src="'.$template_logo.'" /> <h1 style="color: '.$template_color.';">'.$template_h1.'</h1>
<h2>'.$template_h2.'</h2>
<p>'.$template_body1.'</p>
<div style="text-align: center"><a href="{URL}">'.$template_url.'</a></div>
<p>'.$template_body2.'</p>
<hr />
<br /><br />
<div>'.$template_sign.'</div>';

        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($tem);
        $node->set('body', $body);
        $node->save();
      }
      else {
        \Drupal\Core\Form\drupal_set_message('Error.');
      }
      $form_state->setRedirect('traphuman.template_wizard_step4', array('tem' => $tem));
    }

}