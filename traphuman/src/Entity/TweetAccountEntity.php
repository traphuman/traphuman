<?php

namespace Drupal\traphuman\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Tweet account entity entity.
 *
 * @ConfigEntityType(
 *   id = "tweet_account_entity",
 *   label = @Translation("Tweet account entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\traphuman\TweetAccountEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\traphuman\Form\TweetAccountEntityForm",
 *       "edit" = "Drupal\traphuman\Form\TweetAccountEntityForm",
 *       "delete" = "Drupal\traphuman\Form\TweetAccountEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\traphuman\TweetAccountEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "tweet_account_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/tweet_account_entity/{tweet_account_entity}",
 *     "add-form" = "/admin/structure/tweet_account_entity/add",
 *     "edit-form" = "/admin/structure/tweet_account_entity/{tweet_account_entity}/edit",
 *     "delete-form" = "/admin/structure/tweet_account_entity/{tweet_account_entity}/delete",
 *     "collection" = "/admin/structure/tweet_account_entity"
 *   }
 * )
 */
class TweetAccountEntity extends ConfigEntityBase implements TweetAccountEntityInterface {

  /**
   * The Tweet account entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Tweet account entity label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Tweet account.
   *
   * @var string
   */
  protected $account;

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

}
