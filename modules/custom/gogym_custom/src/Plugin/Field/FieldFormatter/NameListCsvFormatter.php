<?php

namespace Drupal\gogym_custom\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'name_list_csv_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "name_list_csv_formatter",
 *   label = @Translation("Name list csv formatter"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class NameListCsvFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      // Getting the field id.
      $file_id = $item->getValue()['target_id'];
      // Loading file entity for getting the file uri.
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($file_id);
      // Opening the csv file in read mode.
      $handler = fopen($file->getFileUri(), "r");
      while (($csv_content = fgetcsv($handler))) {
        $first_name[] = $csv_content[0];
      }
      // Skipping the header from the array.
      unset($first_name[0]);

      // Closing the file.
      fclose($handler);

      $elements[$delta] = [
        // Using custom theme and templating,
        // Because core-theme 'item_list' is not looking nice for me.
        '#theme' => 'name_lists_formatter',
        '#names' => $first_name,
      ];
    }

    return $elements;
  }

}
