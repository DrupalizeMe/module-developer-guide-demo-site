<?php

declare(strict_types = 1);

namespace Drupal\anytown\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for attending page.
 */
class Attending extends ControllerBase {

  /**
   * Callback to display list of vendors attending this week.
   *
   * @return array
   *   List of vendors attending this week.
   */
  public function build(): array {
    // Build a query to get vendor IDs.
    $query = $this->entityTypeManager()->getStorage('node')->getQuery()
      // Only vendors this user has the permission to view.
      ->accessCheck()
      // Only published entities.
      ->condition('type', 'vendor')
      ->condition('field_vendor_attending', TRUE);

    $node_ids = $query->execute();
    if (count($node_ids) > 0) {
      // Load the actual vendor node entities.
      $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($node_ids);

      $view_builder = $this->entityTypeManager()->getViewBuilder('node');

      // We're going to display each vendor twice. Once in an unordered list
      // that we'll use for a summary at the top of the page. And then again
      // using the configured 'teaser' view mode below that list. This allows us
      // to demonstrate both rendering individual fields and complete entities.
      $vendor_list = [];
      $vendor_teasers = [];

      foreach ($nodes as $vendor) {
        // For the summary list we want their name, which is the label field.
        $vendor_list[$vendor->id()] = [];
        $vendor_list[$vendor->id()]['name'] = [
          '#markup' => $vendor->label(),
        ];
        // And, the email address from the field_vendor_contact_email field
        // rendered using the configured formatter for the 'default' view mode.
        // But we're also going to explicitly hide the field label regardless of
        // what's configured for the view mode.
        // See \Drupal\Core\Field\FieldItemListInterface::view().
        // Calling view() on the field is a wrapper for using the viewBuilder
        // like so
        // $view_builder->viewField($vendor->field_vendor_contact_email);
        // The most common options here are likely 'label', and 'type' which
        // should be the ID of a field formatter plugin to use. If not type is
        // specified the field types `default_formatter` is used.
        $vendor_list[$vendor->id()]['contact'] = $vendor->get('field_vendor_contact_email')->view(['label' => 'hidden']);

        // Add cache tags for the vendor to the render array so that if the
        // vendor node gets edited this content gets invalidated.
        $vendor_list[$vendor->id()]['#cache'] = [
          'tags' => $vendor->getCacheTags(),
        ];

        // Then, we also want to render the entire node, using the 'teaser'
        // view mode. This will return the render array for displaying the node
        // content.
        $vendor_teasers[$vendor->id()] = $view_builder->view($vendor, 'teaser');
      }

      // Alternatively, we could render teasers for all vendors at once using
      // $vendor_teasers = $view_builder->viewMultiple($nodes, 'teaser');.
    }

    return [
      'vendor_list' => [
        '#theme' => 'item_list',
        '#items' => $vendor_list,
      ],
      'vendor_teasers' => $vendor_teasers,
    ];
  }

}
