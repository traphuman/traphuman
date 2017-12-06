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
use \Drupal\node\Entity\Node;
// use Drupal\Core\Form;

/**
 * Controlador para devolver el contenido de las páginas definidas
 */
class TraphumanTemplateController extends ControllerBase {
  public function templatelist() {
      $build['traphuman_theming_markup'] = array(
        '#markup' => '<p>' . $this->t('Create your own templates.') . '</p>',
      );
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $templates = $node_storage->loadByProperties(['type' => 'template', 'status' => 1]);
      $header = ['Name', 'Sender', 'Created', 'Edit', 'Delete'];
      foreach($templates as $nid => $template) {
          $url_delete = Url::fromRoute('traphuman.delete_confirm_template', array('num' => $nid));
          $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
          $url_edit = Url::fromRoute('traphuman.edit_template', array('num' => $nid));
          $link_edit = Link::fromTextAndUrl(t('Edit'), $url_edit);
          $rows[] = [$template->getTitle(), $template->get('field_sender')->getValue()[0]['value'], date('Y-m-d H:i:s', $template->getCreatedTime()), $link_edit, $link_delete];
      }
      $build['traphuman_theming_table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
      ];
      $build['traphuman_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanTemplateForm');
      return $build;
  }

  public function templatelistcam($cam) {
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
    $select_form = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanTemplateSelectForm', $cam);
    $build['traphuman_form_template_select'] = $select_form;
    $build['traphuman_theming_markup_2'] = [
        '#markup' => '</div><div><h2>...or you can create one new:</h2>',
    ];
    $create_form = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanTemplateForm', $cam);
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

  public function deletetemplate($num, $cam = NULL) {

      $query = \Drupal::entityQuery('node')
          ->condition('status', 1)
          ->condition('type', 'campaign')
          ->condition('field_template',$num);
      $entity_ids = $query->execute();

      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $campaigns = $node_storage->loadMultiple($entity_ids);

      foreach ($campaigns as $nid => $campaign) {
          $campaign->set('field_template', NULL);
          $campaign->save();
      }

      $node = $node_storage->load($num);
      $node->delete();
      if($cam == NULL) {
        $url = Url::fromRoute('traphuman.template_list');
      }
      else {
        $url = Url::fromRoute('traphuman.template_list_cam', array('cam' => $cam));
      }
      return new RedirectResponse($url->toString());
  }

    public function deleteconfirmtemplate($num, $cam = NULL) {
        $build['traphuman_theming_markup'] = array(
            '#markup' => '<p>Are you sure to delete template '.$num.'?</p>',
        );

        $query = \Drupal::entityQuery('node')
            ->condition('status', 1)
            ->condition('type', 'campaign')
            ->condition('field_template',$num);
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

        $url_delete = Url::fromRoute('traphuman.delete_template', array('num' => $num, 'cam' => $cam));
        if($cam == NULL) {
            $url_back = Url::fromRoute('traphuman.template_list');
        }
        else {
            $url_back = Url::fromRoute('traphuman.template_list_cam', array('cam' => $cam));
        }
        $link_back = Link::fromTextAndUrl(t('No'), $url_back);
        $link_delete = Link::fromTextAndUrl(t('Delete'), $url_delete);
        $build['traphuman_theming_markup2'] = array(
            '#markup' => $link_delete->toString() . ' | '.$link_back->toString(),
        );

        return $build;
    }

  public function edittemplate($num) {
    $build['traphuman_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanTemplateForm');
    return $build;
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($num);
    $node->delete();
    $url = Url::fromRoute('traphuman.template_list');
    return new RedirectResponse($url->toString());
  }

  public function edittemplatecam($num, $cam) {
    $build['traphuman_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanTemplateForm', $cam, $num);
    return $build;
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($num);
    $node->delete();
    $url = Url::fromRoute('traphuman.template_list');
    return new RedirectResponse($url->toString());
  }

  public function templateimport() {
    $wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri("public://");
    $url_to_files = $wrapper->getExternalUrl()."traphuman/templates/custom";
    $filename = \Drupal::service('file_system')->realpath(file_default_scheme() . "://")."/traphuman/templates/custom";
    if (!file_exists($filename)) {
      mkdir($filename, 0777, true);
    }

    $this->importer($filename, $url_to_files);

    $url = Url::fromRoute('traphuman.template_list');
    return new RedirectResponse($url->toString());
  }

  public function templategitimport() {
    $wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri("public://");
    $url_to_files = $wrapper->getExternalUrl()."traphuman/templates/git";
    $filename = \Drupal::service('file_system')->realpath(file_default_scheme() . "://")."/traphuman/templates/git";
    if (!file_exists($filename)) {
      mkdir($filename, 0777, true);
    }

    chdir($filename);
    exec("git fetch origin master");
    exec("git pull origin master");

    $this->importer($filename, $url_to_files);

    $url = Url::fromRoute('traphuman.template_list');
    return new RedirectResponse($url->toString());
  }

  public function importer($filename, $url_to_files) {
    $items = array('subject','sender mail','sender name');

    $templates = array_diff(scandir($filename), array('.', '..'));
    foreach ($templates as $template) {
      dpm($template);
      $template_url = $filename.'/'.$template;
      $info_file = $template_url.'/INFO.txt';
      $saved_file = $template_url.'/SAVED.txt';
      $mail_file = $template_url.'/mail.html';
      $landing_file = $template_url.'/landing.html';
      if ((file_exists($info_file))&&(!file_exists($saved_file))) {
        // dpm($template.' has a Info file');
        $fp = fopen($info_file, "r");
        $template_data = array();
        while (!feof($fp)) {
          $data = fgets($fp);
          foreach ($items as $item) {
            if ((strpos($data, $item.':')) === 0) {
              $template_data[$item] = trim(str_replace($item.':', '', $data));
            }
          }
        }
        fclose($fp);
        if (file_exists($mail_file)) {
          $body = file_get_contents($mail_file);
          $body = str_replace('{PATH}',$url_to_files.'/'.$template, $body);
          $body = str_replace('{LANDING}',$url_to_files.'/'.$template.'/index.html?token={ID}', $body);
          $template_data['mail'] = $body;
        }
        if (file_exists($landing_file)) {
          $landing_file_to = $template_url.'/index.html';
          $landing_content = '<script> var parseQueryString = function() { var str = window.location.search; var objURL = {}; str.replace(new RegExp( "([^?=&]+)(=([^&]*))?", "g" ),function( $0, $1, $2, $3 ){ objURL[ $1 ] = $3; } ); return objURL; }; </script>' . file_get_contents($landing_file);
          $landing_content = str_replace('{TRACK_URL}', ' action="/" onsubmit="data = parseQueryString(); document.forms[0].action = \'http://'.\Drupal::request()->getHost().'/traphuman/mail/landing/\'+data[\'token\'];"', $landing_content);
          $landing_content = str_replace('{PATH}', $url_to_files.'/'.$template, $landing_content);
          file_put_contents($landing_file_to, $landing_content);
          if(chmod($landing_file_to, 0777)) {
            dpm('bien '.$landing_file_to);
          }
          else {
            dpm('mal '.$landing_file_to);
          }
        }
        if((file_put_contents($saved_file, 'Template uploaded to TrapHuman')) === FALSE) {
          drupal_set_message("Put all permission to folder in ".$template);
        }
        else {
          $node = Node::create([
              'type' => 'template',
              'title' => $template_data['subject'],
              'body' => $template_data['mail'],
              'field_sender' => $template_data['sender mail'],
              'field_company_suplantted' => $template_data['sender name'],
          ]);
          $node->save();
          drupal_set_message($template." uploaded!");
        }
      }
    }
  }

  public function wizardfinish($tem) {
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($tem);
    $template_body = $node->get('body')->getValue()[0]['value'];

    $url_pre = Url::fromRoute('traphuman.template_list');
    $link_pre = Link::fromTextAndUrl(t('Go to template list'), $url_pre);

    $build['preview'] = [
        '#markup' => '<h1>Preview</h1><hr /><hr />'.$template_body.'<hr /><hr /><h1>END Preview</h1>'.$link_pre->toString(),
    ];

    return $build;
  }

}