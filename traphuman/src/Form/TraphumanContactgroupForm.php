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
class TraphumanContactgroupForm extends FormBase {

    public function buildForm(array $form, FormStateInterface $form_state, $cam = NULL, $num = NULL) {
      if($num != NULL) {
        $build['contactgroup_id'] = [
            '#type' => 'hidden',
            '#default_value' => $num,
        ];
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load(intval($num));
        $contactgroup_name = $node->getTitle();
      }
      else {
        $contactgroup_name = '';
      }

      $build['contactgroup_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Name of Contact Group'),
          '#description' => $this->t('The name must be at least 5 characters long.'),
          '#default_value' => $contactgroup_name,
          '#required' => TRUE,
      ];

      $build['upload'] =[
          '#type' => 'managed_file',
          '#title' => $this->t('CSV File'),
          '#description' => $this->t('Upload a semicolon (;) CSV File. (Name;mail)'),
          '#upload_location'      => 'public://traphuman/csv',
          '#upload_validators'    => [
              'file_validate_extensions'    => array('csv'),
              'file_validate_size'          => array(25600000)
          ],
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
          '#value' => $this->t('Submit'),
      ];
      return $build;
    }

    public function getFormId() {
        return 'traphuman_forms_contactgroup';
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        $title = $form_state->getValue('contactgroup_name');
        if (strlen($title) < 5) {
            // Set an error for the form element with a key of "title".
            $form_state->setErrorByName('contactgroup_name', $this->t('The name must be at least 5 characters long.'));
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
      $title = $form_state->getValue('contactgroup_name');
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      if($nid = $form_state->getValue('contactgroup_id')) {
        $node = $node_storage->load($nid);
        $node->setTitle($title);
      }
      else {
        // Create node object with attached file.
        $node = Node::create([
            'type' => 'contactgroup',
            'title' => $title,
        ]);
      }



      if($destination = \Drupal\file\Entity\File::load($form_state->getValue('upload')[0])) {
        $destination_uri = $destination->getFileUri();
        if ((file_exists($destination_uri)) && ((filesize($destination_uri)) > 0)) {
          $archivo = fopen($destination_uri, 'r');
          $content = '';
          $contactos_add = array();
          while (!feof($archivo)) {
            $content = fgetcsv($archivo);
            if ($content) {
              foreach ($content as $val) {
                if($val != '') {
                  $valores = explode(';', $val);
                  $nombre = trim($valores[0]);
                  $email = trim($valores[1]);
                  $query = \Drupal::entityQuery('node')
                      ->condition('status', 1)
                      ->condition('type', 'contact')
                      ->condition('field_email', $email, '=', 'en');
                  $nids = $query->execute();
                  if ($element = reset($nids)) {
                    $node_contact = $node_storage->load($element);
                  }
                  else {
                    $node_contact = Node::create([
                        'type' => 'contact',
                        'title' => $nombre,
                        'field_email' => $email,
                    ]);
                    $node_contact->save();
                  }

                  $contactos_add[] = ['target_id' => $node_contact->id()];
                }
              }
            }
          }
          fclose($archivo);
          $contactos_actuales = $node->get('field_contacts')->getValue();
          $contactos_actuales = array_merge($contactos_actuales, $contactos_add);
          $node->set('field_contacts', $contactos_actuales);
        }
      }

      $node->save();


      if($nid_c = $form_state->getValue('campaign_id')) {
        $form_state->setRedirect('traphuman.contactgroup_list_cam', array('cam' => $nid_c));
      }
      else {
        $form_state->setRedirect('traphuman.contactgroup_list');
      }
    }

}