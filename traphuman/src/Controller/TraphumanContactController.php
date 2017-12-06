<?php
/**
 * @file
 * Contains \Drupal\traphuman\Controller\TraphumanTemplateController.
 */

namespace Drupal\traphuman\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;

/**
 * Controlador para devolver el contenido de las páginas definidas
 */
class TraphumanContactController extends ControllerBase {
  public function contactlist() {
      $build['traphuman_theming_markup'] = array(
        '#markup' => '<p>' . $this->t('Create your own contacts.') . '</p>',
      );
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $contacts = $node_storage->loadByProperties(['type' => 'contact', 'status' => 1]);
      $header = ['Name', 'Mail', 'Created', 'Edit', 'Delete'];
      foreach($contacts as $nid => $contact) {
          $url_delete = Url::fromRoute('traphuman.delete_confirm_contact', array('num' => $nid));
          $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
          $url_edit = Url::fromRoute('traphuman.edit_contact', array('num' => $nid));
          $link_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);
          $rows[] = [$contact->getTitle(), $contact->get('field_email')->getValue()[0]['value'], date('Y-m-d H:i:s', $contact->getCreatedTime()), $link_edit, $link_delete];
      }
      $build['traphuman_theming_table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
      ];
      return $build;
  }

  public function contactlistgru($gru, $cam = NULL ) {
      if($cam != NULL) {
        $url_edit = Url::fromRoute('traphuman.contactgroup_list_cam', array('cam' => $cam));
        $link_edit = Link::fromTextAndUrl(t('Return to campaign group list'), $url_edit);
        $link_edit = $link_edit->toString();
        $build['traphuman_theming_markup'] = array(
            '#markup' => $link_edit,
        );
      }
      $build['traphuman_theming_markup_1'] = [
          '#markup' => '<div class="grid"><div>',
      ];
      $select_form = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanContactSelectForm', $gru, $cam);
      $build['traphuman_form_template_select'] = $select_form;
      $build['traphuman_theming_markup_2'] = [
          '#markup' => '</div><div><h2>...or you can create one new:</h2>',
      ];
      $create_form = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanContactForm', NULL, $gru);
      $create_form['contactgroup_id'] = [
          '#type' => 'hidden',
          '#default_value' => $gru,
      ];
      $build['traphuman_form_create_contact'] = $create_form;
      $build['traphuman_theming_markup_3'] = [
          '#markup' => '</div></div>',
      ];
      return $build;
  }

  public function deletecontact($num, $gru = null, $cam = null) {

    $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'contactgroup')
        ->condition('field_contacts',$num);
    $entity_ids = $query->execute();

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $groups = $node_storage->loadMultiple($entity_ids);

    foreach ($groups as $nid => $group) {
      $field_contacts = $group->get('field_contacts')->getValue();
      foreach ($field_contacts as $key => $contact) {
        if($contact['target_id'] == $num) {
          unset($field_contacts[$key]);
        }
      }
      $group->set('field_contacts', $field_contacts);
      $group->save();
    }

    $node = $node_storage->load($num);
    $node->delete();
    if($cam == NULL) {
      if ($gru == NULL) {
        $url = Url::fromRoute('traphuman.contact_list');
      } else {
        $url = Url::fromRoute('traphuman.contact_list_gru', array('gru' => $gru));
      }
    }
    else {
      $url = Url::fromRoute('traphuman.contact_list_gru_cam', array('gru' => $gru, 'cam' => $cam));
    }
    return new RedirectResponse($url->toString());
  }

  public function deleteconfirmcontact($num, $gru = null, $cam = null) {
    $build['traphuman_theming_markup'] = array(
        '#markup' => '<p>Are you sure to delete contact '.$num.'?</p>',
    );

    $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'contactgroup')
        ->condition('field_contacts',$num);
    $entity_ids = $query->execute();

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $groups = $node_storage->loadMultiple($entity_ids);

    $groups_array = [];
    foreach ($groups as $nid => $group) {
      $groups_array[] = $group->getTitle();
    }

    $build['traphuman_theming_list'] = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#title' => 'This contact will be deleted from the next groups',
        '#items' => $groups_array,
        '#attributes' => ['class' => 'mylist'],
        '#wrapper_attributes' => ['class' => 'container'],
    ];

    if($cam == NULL) {
      if ($gru == NULL) {
        $url_back = Url::fromRoute('traphuman.contact_list');
        $url_delete = Url::fromRoute('traphuman.delete_contact', array('num' => $num));
      } else {
        $url_back = Url::fromRoute('traphuman.contact_list_gru', array('gru' => $gru));
        $url_delete = Url::fromRoute('traphuman.delete_contact_gru', array('num' => $num, 'gru' => $gru));
      }
    }
    else {
      $url_back = Url::fromRoute('traphuman.contact_list_gru_cam', array('gru' => $gru, 'cam' => $cam));
      $url_delete = Url::fromRoute('traphuman.delete_contact_gru_cam', array('num' => $num, 'gru' => $gru, 'cam' => $cam));
    }
    $link_back = Link::fromTextAndUrl(t('No'), $url_back);
    $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
    $build['traphuman_theming_markup2'] = array(
        '#markup' => $link_delete->toString() . ' | '.$link_back->toString(),
    );
    return $build;
  }

  public function editcontact($num) {
    $build['traphuman_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanContactForm', $num);
    return $build;
  }

  public function editcontactgru($num, $gru, $cam = NULL) {
    $build['traphuman_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanContactForm', $num, $gru, $cam);
    return $build;
  }

}