<?php

declare(strict_types = 1);

namespace Drupal\anytown_status\Form;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a Anytown Status form.
 */
class StatusUpdateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'anytown_status_status_update';
  }

  /**
   * Form building callback.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param \Drupal\node\NodeInterface|null $node
   *   ID of the node to edit the status for.
   *
   * @return array
   *   The form array.
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL): array {
    // Verify that it is a vendor node.
    if ($node->bundle() !== 'vendor') {
      throw new NotFoundHttpException();
    }

    $form['title'] = [
      '#type' => 'item',
      '#markup' => $this->t('Updating status for vendor: <strong>@vendor</strong>', ['@vendor' => $node->getTitle()]),
    ];

    // Save the $node object into the form state, temporary storage, so that we
    // can use it later without having to load it again.
    $form_state->set('node', $node);

    $form['attending'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Attending'),
      '#description' => $this->t('Check this box if you plan to attend this weekends market.'),
      // We intentionally leave off the #default_value because we always want
      // to zero this out and require them to check the box (or not) but not
      // assume that we can save the same status from last week.
    ];

    $form['contact_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact name'),
      '#required' => TRUE,
      '#default_value' => $node->get('field_vendor_contact_name')->value,
    ];

    $form['contact_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Contact email'),
      '#required' => TRUE,
      // Same effect as using $node->get('field_vendor_contact_email') but uses
      // magic property getter.
      '#default_value' => $node->field_vendor_contact_email->value,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Update status'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Get the node object we saved in the buildForm method.
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form_state->get('node');

    // Read the values from our form fields, and use them to update the fields
    // on the vendor node.
    $node->set('field_vendor_attending', $form_state->getValue('attending'));
    $node->set('field_vendor_contact_name', $form_state->getValue('contact_name'));
    $node->set('field_vendor_contact_email', $form_state->getValue('contact_email'));

    try {
      // Persist the changes to the database.
      $node->save();

      // Set a success message and redirect to the node view page.
      $this->messenger()->addStatus($this->t('Thank you for updating your attendance status.'));
      $form_state->setRedirectUrl($node->toUrl());
    }
    catch (EntityStorageException $exception) {
      // Log the error.
      $this->logger('anytown_status')->error($exception->getMessage());
      // And display a message to the user.
      $this->messenger()->addError($this->t('An error occurred while saving. Please try again.'));
    }
  }

}
