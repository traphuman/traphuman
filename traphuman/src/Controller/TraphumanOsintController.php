<?php
/**
 * @file
 * Contains \Drupal\traphuman\Controller\TraphumanCampaignController.
 */

namespace Drupal\traphuman\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Xss;

/**
 * Controlador para devolver el contenido de las páginas definidas
 */
class TraphumanOsintController extends ControllerBase {

  public function tweetconsult($page = 0) {

    $amount = 5;
    $start = $page*$amount;

    $header = ['Tweet', 'Created'];

    $con = \Drupal\Core\Database\Database::getConnection('default','osint');
    $query = $con->select('tweets', 't');
    $query->fields('t', ['tweet_text', 'created_at']);
    $query->range($start,$amount);
    $result = $query->execute()->fetchAll();
    foreach($result as $key => $value) {
      $rows[] = [$value->tweet_text, $value->created_at];
    }
    $build['traphuman_theming_table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
    ];

    $page_next = $page+1;
    $page_previous = $page-1;

    $paging = '';
    if($page_previous >= 0) {
      $url_pre = Url::fromRoute('traphuman.osint_tweetconsult_page', ['page' => $page_previous]);
      $link_pre = Link::fromTextAndUrl(t('< Previous Page'), $url_pre);
      $paging = $link_pre->toString() . ' | ';
    }

    $url_next = Url::fromRoute('traphuman.osint_tweetconsult_page', ['page' => $page_next]);
    $link_next = Link::fromTextAndUrl(t('Next Page >'), $url_next);

    $paging .= $link_next->toString();


    $build['traphuman_next'] = [
        '#markup' => $paging,
    ];
    return $build;
  }

  public function tweetmorebyday() {

    $number = Xss::filter(\Drupal::request()->get('number'));
    if((!isset($number))||($number == '')) {
      $number = 1;
    }
    $date = Xss::filter(\Drupal::request()->get('date'));
    if((!isset($date))||($date == '')) {
      $date = date('Y-m-d');
    }

    $renderer = \Drupal::service('renderer');
    $header = ['Times', 'Data'];

    $con = \Drupal\Core\Database\Database::getConnection('default','osint');

    $query = "SELECT COUNT(t.tweet_id) as numero, SUBSTRING(t.tweet_text, 1, 30) as cadena
	FROM osint.tweets t";

    if($date != NULL) {
      $query .= " WHERE t.created_at >= '".$date." 00:00:00' AND t.created_at <= '".$date." 23:59:59'";
    }

    $query .= " GROUP BY cadena
    HAVING COUNT(t.tweet_id) >= $number
    ORDER BY numero DESC LIMIT 10;";

    $query = $con->query($query);
    $result = $query->fetchAll();
    foreach($result as $key => $value) {
      $output = array();
      $query = "SELECT * FROM osint.tweets t WHERE SUBSTRING(t.tweet_text, 1, 30) LIKE '".str_replace("'","\'",$value->cadena)."'";
      if($date != NULL) {
        $query .= " AND t.created_at >= '".$date." 00:00:00' AND t.created_at <= '".$date." 23:59:59'";
      }
      $query = $con->prepare($query);
      $query2 = $con->query($query);
      $result2 = $query2->fetchAll();
      $rows2 = array();
      foreach($result2 as $key2 => $value2) {
        $query_tags_sql = 'SELECT * FROM osint.tweet_tags WHERE tweet_id = '.$value2->tweet_id;
        $query_tags = $con->query($query_tags_sql);
        $result_tags = $query_tags->fetchAll();
        $tags = '';
        foreach($result_tags as $id_tag => $tag) {
          $tags .= $tag->tag.' ';
        }
        $query_urls_sql = 'SELECT * FROM osint.tweet_urls WHERE tweet_id = '.$value2->tweet_id;
        $query_urls = $con->query($query_urls_sql);
        $result_urls = $query_urls->fetchAll();
        $urls = '';
        foreach($result_urls as $id_url => $url) {
          $urls .= $url->url.' ';
        }
        $query_media_sql = 'SELECT * FROM osint.tweet_media_urls WHERE tweet_id = '.$value2->tweet_id;
        $query_media = $con->query($query_media_sql);
        $result_media = $query_media->fetchAll();
        $medias = '';
        foreach($result_media as $id_media => $media) {
          $medias .= $media->media_url.' ';
        }
        $rows2[] = [$value2->tweet_text, $value2->created_at, $value2->screen_name, $tags, $urls, $medias];
      }
      $output['traphuman_theming_table_detail_'.$key2] = [
          '#type' => 'table',
          '#header' => ['Text', 'Created', 'User', 'Tags', 'URLs', 'Media'],
          '#rows' => $rows2,
      ];
      $html = $renderer->render($output);
      $rows[] = [$value->numero, new FormattableMarkup('Start string: <b>'.$value->cadena.'</b>'.$html, [])];
    }
    $build['traphuman_filter_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanOsintTweetDayFilterForm');
    $build['traphuman_theming_table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
    ];

    return $build;
  }

  public function tweetfamousauthor() {

    $query = \Drupal::entityQuery('tweet_account_entity');
    $accounts = $query->execute();

    if((count($accounts))>0) {

      $famous = Xss::filter(\Drupal::request()->get('famous'));
      if ((!isset($famous)) || ($famous == '')) {
        $famous = $accounts[0];
      }

      $build['traphuman_filter_form'] = \Drupal::formBuilder()->getForm('Drupal\traphuman\Form\TraphumanOsintTweetFamousFilterForm');

      $con = \Drupal\Core\Database\Database::getConnection('default', 'osint');

      $header = ['Text Policia Tweet', 'Media'];

      $query_sql = "SELECT t1.tweet_text, t2.media_url FROM tweets t1 LEFT JOIN tweet_media_urls t2 ON t1.tweet_id = t2.tweet_id where (screen_name = '" . $famous . "');";
      $query = $con->query($query_sql);
      $result = $query->fetchAll();
      foreach ($result as $key => $tweet) {
        $rows[] = [$tweet->tweet_text, $tweet->media_url];
      }

      $build['traphuman_theming_table'] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
      ];
    }

    return $build;
  }



}