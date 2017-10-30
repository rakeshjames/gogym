<?php

namespace Drupal\gogym_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Link;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'GenderReportSummaryBlock' block.
 *
 * @Block(
 *  id = "gender_report_summary_block",
 *  admin_label = @Translation("Gender report summary block"),
 * )
 */
class GenderReportSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Datetime\DateFormatter definition
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Constructs a new GenderReportSummaryBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        EntityTypeManager $entity_type_manager,
        DateFormatter $date_formatter
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('date.formatter')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    // Get the options of promotion types.
    $promotion_field_config = FieldStorageConfig::loadByName('node', 'field_promotion_type');
    $promo_options = $promotion_field_config->getSettings()['allowed_values'];
    $date_format = FieldStorageConfig::loadByName('node', 'field_reviewed_on');
    // Loading all gender_reports node type are in the system.
    $gender_reports = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'gender_reports']);
    foreach ($gender_reports as $report) {
      $data[] = $this->processGenderReport($report, $promo_options);
    }
    $header = ['Gym Name', 'Promotion Type(s)', 'Reviewed By', 'Reviewed On'];
    $build['gender_report_summary_block'] =  [
      '#theme' => 'table',
      //'#caption' => 'Gender Report Summary',
      '#header' => $header,
      '#rows' => $data,
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

  /**
   * Helper function to process the data-rows for the table.
   *
   * @param $report object
   *   The individual gender report node object.
   *
   */
  public function processGenderReport($report, $promo_options) {
    // Not yet reviewed text.
    $not_reviewed = '---';
    // Gym Name.
    $gym_name = $report->getTitle();
    // Applied promotions.
    if (isset($report->get('field_promotion_type')->getValue()[0]['value'])) {

      $promotions = $report->get('field_promotion_type')->getValue();
      foreach ($promotions as $promotion) {
        $promotion_value[] = $promo_options[$promotion['value']];
      }
      $promotions = implode(', ', $promotion_value);
    }
    else {
      $promotions = $not_reviewed;
    }

    // Get the Reviewed by user
    if (isset($report->get('field_reviewed_by')->getValue()[0]['target_id'])) {
      $reviewed_by_id = $report->get('field_reviewed_by')->getValue()[0]['target_id'];
      $reviewer = $this->entityTypeManager->getStorage('user')->load($reviewed_by_id)->getUsername();
      $url = Url::fromUserInput('/user/' . $reviewed_by_id);
      $reviewed_by = Link::fromTextAndUrl($reviewer, $url);
    }
    else {
      $reviewed_by = $not_reviewed;
    }

    // Reviewed time.
    if (isset($report->get('field_reviewed_on')->getValue()[0]['value'])) {
      $reviewed_on_date = $report->get('field_reviewed_on')->getValue()[0]['value'];
      $reviewed_on = $this->dateFormatter->format(strtotime($reviewed_on_date), "long");
    }
    else {
      $reviewed_on = $not_reviewed;
    }
    // Preparing each row on the table.
    $row = [$gym_name, $promotions , $reviewed_by, $reviewed_on];

    return $row;
  }

}
