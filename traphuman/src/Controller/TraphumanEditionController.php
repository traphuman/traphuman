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
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\Access\AccessInterface;

/**
 * Controlador para devolver el contenido de las páginas definidas
 */
class TraphumanEditionController extends ControllerBase {

  public function editionlist($cam) {

      $build['traphuman_theming_header'] = array(
          '#markup' => '<h1>' . $this->t('List of editions.') . '</h1>',
      );
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $editions = $node_storage->loadByProperties([
              'type' => 'edition',
              'status' => 1,
              'field_campaign' => $cam,
          ]);
      $header = ['Name', 'Start', 'Sends', 'Clicks', 'Finish'];
      foreach($editions as $nid => $edition) {
          if($edition->get('field_finish_date')->getValue()[0]['value']) {
              $fechafin = date('Y/m/d H:i:s', $edition->get('field_finish_date')->getValue()[0]['value']);
          }
          else {
              $url = Url::fromRoute('traphuman.campaign_edition_stop', array('cam' => $cam, 'edi' => $nid));
              $fechafin = Link::fromTextAndUrl('Not finished (STOP)', $url);
          }

          $mails_edition = $node_storage->loadByProperties([
              'type' => 'mail',
              'status' => 1,
              'field_edition' => $nid,
          ]);
          $total_mails = count($mails_edition);
          $mails_edition = $node_storage->loadByProperties([
              'type' => 'mail',
              'status' => 1,
              'field_edition' => $nid,
              'field_sent_date' => 0,
          ]);
          $total_nosents_mails = count($mails_edition);
          $total_sents_mails = $total_mails-$total_nosents_mails ;

          $mails_edition = $node_storage->loadByProperties([
              'type' => 'mail',
              'status' => 1,
              'field_edition' => $nid,
              'field_link_click_date' => 0,
          ]);
          $total_noclick_mails = count($mails_edition);
          $total_click_mails = $total_mails-$total_noclick_mails ;

          $rows[] = [
              $edition->getTitle(),
              date('Y/m/d H:i:s',$edition->get('field_start_date')->getValue()[0]['value']),
              $total_sents_mails.'/'.$total_mails,
              $total_click_mails.'/'.$total_mails,
              $fechafin,
          ];
      }
      $build['traphuman_theming_table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
      ];

      $build['traphuman_theming_header2'] = array(
        '#markup' => '<p>' . $this->t('Create new edition of campaign.') . '</p>',
      );
      $build['traphuman_theming_markup'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanEditionForm', $cam);
      return $build;
  }

  public function editionstop($cam, $edi) {
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $edition = $node_storage->load($edi);
    if($edition->get('field_finish_date')->getValue()[0]['value'] == 0) {
      $edition->set('field_finish_date', strtotime(date('Y/m/d H:i:s')));
      $edition->save();
    }
    $url = Url::fromRoute('traphuman.campaign_editions', array('cam' => $cam));
    return new RedirectResponse($url->toString());
  }

  public function maillinktrack($mail) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $mail = $node_storage->load($mail);
      if($mail->get('field_link_click_date')->getValue()[0]['value'] == 0) {
          $mail->set('field_link_click_date', strtotime(date('Y/m/d H:i:s')));
          $mail->save();
      }
      $build['traphuman_theming_header'] = array(
          '#markup' => '<h1>Tracked!!</h1>',
      );
      return $build;
  }

    public function landingtrack($mail) {
      // TODO: track landing
      /*
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $mail = $node_storage->load($mail);
        if($mail->get('field_link_click_date')->getValue()[0]['value'] == 0) {
            $mail->set('field_link_click_date', strtotime(date('Y/m/d H:i:s')));
            $mail->save();
        } */
        $build['traphuman_theming_header'] = array(
            '#markup' => '<h1>Landing Tracked!!</h1>',
        );
        return $build;
    }

  public function maillinktrackaccess(AccountInterface $account) {
      return \Drupal\Core\Access\AccessResult::allowed();
  }

}