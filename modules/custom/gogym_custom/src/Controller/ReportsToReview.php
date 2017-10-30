<?php

namespace Drupal\gogym_custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ReportsToReview.
 */
class ReportsToReview extends ControllerBase {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Constructs a new DefaultController object.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Review lists.
   *
   * @return array
   *   Renderabale array.
   */
  public function reviewlists() {
    // Get all  non reviewed gender reports.
    $query = $this->database->select('node_field_data', 'nfd');
    $query->fields('nfd', ['nid', 'title']);
    $query->leftJoin('node__field_reviewed_by', 'nfrb', 'nfd.nid = nfrb.entity_id');
    $query->fields('nfrb', ['field_reviewed_by_target_id']);
    $query->condition('nfd.type', 'gender_reports', '=');
    $query->isNull('nfrb.field_reviewed_by_target_id');
    $results = $query->execute()->fetchAll();
    foreach ($results as $result) {
      $url = Url::fromUserInput('/node/' . $result->nid);
      $reports[] = Link::fromTextAndUrl($result->title, $url);
    }

    // If there are no gender reports to review.
    if (empty($reports)) {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No Gender Reports are there to Review.'),
      ];
    }

    return [
      '#theme' => 'item_list',
      '#items' => $reports,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
