<?php

namespace Drupal\traphuman\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Tweet alarm entity entity.
 *
 * @ConfigEntityType(
 *   id = "tweet_alarm_entity",
 *   label = @Translation("Tweet alarm entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\traphuman\TweetAlarmEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\traphuman\Form\TweetAlarmEntityForm",
 *       "edit" = "Drupal\traphuman\Form\TweetAlarmEntityForm",
 *       "delete" = "Drupal\traphuman\Form\TweetAlarmEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\traphuman\TweetAlarmEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "tweet_alarm_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/tweet_alarm_entity/{tweet_alarm_entity}",
 *     "add-form" = "/admin/structure/tweet_alarm_entity/add",
 *     "edit-form" = "/admin/structure/tweet_alarm_entity/{tweet_alarm_entity}/edit",
 *     "delete-form" = "/admin/structure/tweet_alarm_entity/{tweet_alarm_entity}/delete",
 *     "collection" = "/admin/structure/tweet_alarm_entity"
 *   }
 * )
 */
class TweetAlarmEntity extends ConfigEntityBase implements TweetAlarmEntityInterface {

  /**
   * The Tweet alarm entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Tweet alarm entity label.
   *
   * @var string
   */
  protected $label;

  /**
   * Mail to send alerts
   *
   * @var string
   */
  protected $mail;

  /**
   * The Twitter account
   *
   * @var string
   */
  protected $account;

  /**
   * The last send time
   *
   * @var string
   */
  protected $lastsend;

  /**
   * {@inheritdoc}
   */
  public function getMail() {
    return $this->mail;
  }

  /**
   * {@inheritdoc}
   */
  public function setMail($mail) {
    $this->mail = $mail;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccount() {
    return $this->account;
  }

  /**
   * {@inheritdoc}
   */
  public function setAccount($account) {
    $this->account = $account;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastsend() {
    return $this->lastsend;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastsend($lastsend) {
    $this->lastsend = $lastsend;
    return $this;
  }

}
