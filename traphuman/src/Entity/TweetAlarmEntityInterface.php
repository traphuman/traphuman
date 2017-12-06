<?php

namespace Drupal\traphuman\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Tweet alarm entity entities.
 */
interface TweetAlarmEntityInterface extends ConfigEntityInterface {

  // Add get/set methods for your configuration properties here.
  /**
   * Returns the mail to send alerts.
   *
   * @return string
   *  Mail to send alerts
   */
  public function getMail();

  /**
   * Sets the mail to send alerts.
   *
   * @param string $mail
   *  Mail to send alerts
   *
   * @return $this
   */
  public function setMail($mail);
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
  /**
   * Returns the mail last send time
   *
   * @return string
   *  The last send time
   */
  public function getLastsend();

  /**
   * Sets the mail last send time
   *
   * @param string $lastsend
   *  The last send time
   *
   * @return $this
   */
  public function setLastsend($lastsend);
}
