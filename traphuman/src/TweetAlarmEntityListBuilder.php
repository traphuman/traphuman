<?php

namespace Drupal\traphuman;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Tweet alarm entity entities.
 */
class TweetAlarmEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Tweet alarm entity');
    $header['id'] = $this->t('Machine name');
    $header['mail'] = $this->t('Mail');
    $header['account'] = $this->t('Tweet account');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['mail'] = $entity->getMail();
    $row['account'] = $entity->getAccount();
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
