<?php

namespace Drupal\traphuman\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Tweet account entity entities.
 */
interface TweetAccountEntityInterface extends ConfigEntityInterface {

  /**
   * Returns the Twitter account.
   *
   * @return string
   *  Twitter account
   */
  public function getAccount();

  /**
   * Sets the Twitter account
   *
   * @param string $account
   *  The Twitter account
   *
   * @return $this
   */
  public function setAccount($account);

}
