<?php

namespace Drupal\traphuman;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Tweet account entity entities.
 */
class TweetAccountEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Tweet account entity');
    $header['id'] = $this->t('Machine name');
    $header['account'] = $this->t('Twiter Account');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['account'] = $entity->getAccount();
    return $row + parent::buildRow($entity);
  }

}
