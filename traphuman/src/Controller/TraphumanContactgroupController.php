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
class TraphumanContactgroupController extends ControllerBase {
  public function contactgrouplist() {
      $build['traphuman_theming_markup'] = array(
        '#markup' => '<p>' . $this->t('Create your own contact groups.') . '</p>',
      );
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $contactgroups = $node_storage->loadByProperties(['type' => 'contactgroup', 'status' => 1]);
      $header = ['Name', 'Created', 'Contacts', 'Edit', 'Delete'];
      foreach($contactgroups as $nid => $contactgroup) {
          $url_delete = Url::fromRoute('traphuman.delete_confirm_contactgroup', array('num' => $nid));
          $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
          $url_edit = Url::fromRoute('traphuman.edit_contactgroup', array('num' => $nid));
          $link_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);
          $url_contacts = Url::fromRoute('traphuman.contact_list_gru', array('gru' => $nid));
          $link_contacts = Link::fromTextAndUrl(t('Contacts'), $url_contacts);
          $rows[] = [$contactgroup->getTitle(), date('Y-m-d H:i:s', $contactgroup->getCreatedTime()), $link_contacts, $link_edit, $link_delete];
      }
      $build['traphuman_theming_table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
      ];
      return $build;
  }

  public function contactgrouplistcam($cam) {
    $url_edit = Url::fromRoute('traphuman.edit_campaign', array('num' => $cam));
    $link_edit = Link::fromTextAndUrl(t('Return to campaign'), $url_edit);
    $link_edit = $link_edit->toString();
    $url_list = Url::fromRoute('traphuman.campaign_list');
    $link_list = Link::fromTextAndUrl(t('Return to campaign list'), $url_list);
    $link_list = $link_list->toString();
    $build['traphuman_theming_markup'] = array(
        '#markup' => $link_edit.' | '.$link_list,
    );
    $build['traphuman_theming_markup_1'] = [
        '#markup' => '<div class="grid"><div>',
    ];
    $select_form = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanContactgroupSelectForm', $cam);
    $build['traphuman_form_template_select'] = $select_form;
    $build['traphuman_theming_markup_2'] = [
        '#markup' => '</div><div><h2>...or you can create one new:</h2>',
    ];
    $create_form = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanContactgroupForm', $cam);
    $create_form['campaign_id'] = [
        '#type' => 'hidden',
        '#default_value' => $cam,
    ];
    $build['traphuman_form_create_template'] = $create_form;
    $build['traphuman_theming_markup_3'] = [
        '#markup' => '</div></div>',
    ];
    /* return array(
        '#markup' => '<p>' . $this->t('Campaign number ' . $cam) . '</p>',
    ); */
    return $build;
  }

    public function deleteconfirmcontactgroup($num, $cam = NULL) {
        $build['traphuman_theming_markup'] = array(
            '#markup' => '<p>Are you sure to delete group '.$num.'?</p>',
        );

        $query = \Drupal::entityQuery('node')
            ->condition('status', 1)
            ->condition('type', 'campaign')
            ->condition('field_contactgroups',$num);
        $entity_ids = $query->execute();

        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $campaigns = $node_storage->loadMultiple($entity_ids);

        $campaigns_array = [];
        foreach ($campaigns as $nid => $campaign) {
            $campaigns_array[] = $campaign->getTitle();
        }

        $build['traphuman_theming_list'] = [
            '#theme' => 'item_list',
            '#list_type' => 'ul',
            '#title' => 'This template will be deleted from the next campaigns',
            '#items' => $campaigns_array,
            '#attributes' => ['class' => 'mylist'],
            '#wrapper_attributes' => ['class' => 'container'],
        ];


        if($cam == NULL) {
            $url_back = Url::fromRoute('traphuman.contactgroup_list');
            $url_delete = Url::fromRoute('traphuman.delete_contactgroup', array('num' => $num));
        }
        else {
            $url_back = Url::fromRoute('traphuman.contactgroup_list_cam', array('cam' => $cam));
            $url_delete = Url::fromRoute('traphuman.delete_contactgroup_cam', array('num' => $num, 'cam' => $cam));
        }
        $link_back = Link::fromTextAndUrl(t('No'), $url_back);
        $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
        $build['traphuman_theming_markup2'] = array(
            '#markup' => $link_delete->toString() . ' | '.$link_back->toString(),
        );

        return $build;
    }

  public function deletecontactgroup($num, $cam = NULL) {

      $query = \Drupal::entityQuery('node')
          ->condition('status', 1)
          ->condition('type', 'campaign')
          ->condition('field_contactgroups',$num);
      $entity_ids = $query->execute();

      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $campaigns = $node_storage->loadMultiple($entity_ids);

      foreach ($campaigns as $nid => $campaign) {
          $field_contactgroups = $campaign->get('field_contactgroups')->getValue();
          foreach ($field_contactgroups as $key => $group) {
              if($group['target_id'] == $num) {
                  unset($field_contactgroups[$key]);
              }
          }
          $campaign->set('field_contactgroups', $field_contactgroups);
          $campaign->save();
      }

      $node = $node_storage->load($num);
      $node->delete();
      if($cam == NULL) {
          $url = Url::fromRoute('traphuman.contactgroup_list');
      }
      else {
          $url = Url::fromRoute('traphuman.contactgroup_list_cam', array('cam' => $cam));
      }
      return new RedirectResponse($url->toString());
  }

  public function editcontactgroup($num) {
    $build['traphuman_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanContactgroupForm', NULL, $num);
    return $build;
  }

  public function editcontactgroupcam($num, $cam) {
    $build['traphuman_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanContactgroupForm', $cam, $num);
    return $build;
  }

}