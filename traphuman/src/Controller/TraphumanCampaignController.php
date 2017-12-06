<?php
/**
 * @file
 * Contains \Drupal\traphuman\Controller\TraphumanCampaignController.
 */

namespace Drupal\traphuman\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;

/**
 * Controlador para devolver el contenido de las páginas definidas
 */
class TraphumanCampaignController extends ControllerBase {

  public function mainmenu() {
    $url_campaign_list = Url::fromRoute('traphuman.campaign_list');
    $link_campaign_list = Link::fromTextAndUrl(t('Campaign administration'), $url_campaign_list);
    $url_template_list = Url::fromRoute('traphuman.template_list');
    $link_template_list = Link::fromTextAndUrl(t('Template administration'), $url_template_list);
    $url_contact_list = Url::fromRoute('traphuman.contact_list');
    $link_contact_list = Link::fromTextAndUrl(t('Contact administration'), $url_contact_list);
    $url_contactgroup_list = Url::fromRoute('traphuman.contactgroup_list');
    $link_contactgroup_list = Link::fromTextAndUrl(t('Contact groups administration'), $url_contactgroup_list);
    $menu[] = $link_campaign_list;
    $menu[] = $link_template_list;
    $menu[] = $link_contact_list;
    $menu[] = $link_contactgroup_list;
    $build = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#title' => 'Menu',
        '#items' => $menu,
        '#attributes' => ['class' => 'myclass'],
        '#wrapper_attributes' => ['class' => 'my_list_container'],
    ];
    return $build;
  }

  public function campaignlist() {
      $build['traphuman_theming_markup'] = array(
        '#markup' => '<p>' . $this->t('Create your own campaigns.') . '</p>',
      );
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $campaigns = $node_storage->loadByProperties(['type' => 'campaign', 'status' => 1]);
      $header = ['Name', 'Created', 'Template', 'Groups', 'Send', 'Edit', 'Delete'];
      foreach($campaigns as $nid => $campaign) {
          $url_delete = Url::fromRoute('traphuman.delete_confirm_campaign', array('num' => $nid));
          $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
          $url_edit = Url::fromRoute('traphuman.edit_campaign', array('num' => $nid));
          $link_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);

          $url_send = Url::fromRoute('traphuman.campaign_editions', array('cam' => $nid));
          $link_send = Link::fromTextAndUrl(t('Sents'), $url_send);

          if($template = $campaign->get('field_template')->getValue()[0]['target_id']) {
            if($template = $node_storage->load($template))
              $template_text = $template->getTitle();
            else
              $template_text = t('Set Template');
          }
          else {
            $template_text = t('Set Template');
          }

          if($groups = $campaign->get('field_contactgroups')->getValue()[0]['target_id']) {
            $groups_text = '';
            foreach ($campaign->get('field_contactgroups')->getValue() as $key => $value) {
              if($group = $node_storage->load($value['target_id']))
                $groups_text .= $group->getTitle().'|';
            }
            $groups_text = substr($groups_text,0,-1);
          }
          else {
            $groups_text = t('Set Groups');
          }

          $url_template = Url::fromRoute('traphuman.template_list_cam', array('cam' => $nid));
          $link_template = Link::fromTextAndUrl($template_text, $url_template);
          $url_contactgroup = Url::fromRoute('traphuman.contactgroup_list_cam', array('cam' => $nid));
          $link_contactgroup = Link::fromTextAndUrl($groups_text, $url_contactgroup);
          $rows[] = [$campaign->getTitle(), date('Y-m-d H:i:s', $campaign->getCreatedTime()), $link_template, $link_contactgroup, $link_send, $link_edit, $link_delete];
      }
      $build['traphuman_theming_table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
      ];
      return $build;
  }

  public function deletecampaign($num) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($num);
      $node->delete();
      $url = Url::fromRoute('traphuman.campaign_list');
      return new RedirectResponse($url->toString());
  }

  public function deleteconfirmcampaign($num) {
      $build['traphuman_theming_markup'] = array(
          '#markup' => '<p>Are you sure to delete campaign '.$num.'?</p>',
      );
      $url_delete = Url::fromRoute('traphuman.delete_campaign', array('num' => $num));
      $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
      $url_back = Url::fromRoute('traphuman.campaign_list');
      $link_back = Link::fromTextAndUrl(t('No'), $url_back);
      $build['traphuman_theming_markup2'] = array(
          '#markup' => $link_delete->toString() . ' | '.$link_back->toString(),
      );
      return $build;
  }

}