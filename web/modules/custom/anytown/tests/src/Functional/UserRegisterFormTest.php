<?php

namespace Drupal\Tests\anytown\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the user registration form alteration for Terms of Service.
 *
 * @group anytown
 * @covers anytown_form_user_register_form_alter()
 */
class UserRegisterFormTest extends BrowserTestBase {

  /**
   * Theme to use for our test.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['user', 'anytown'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Change site configuration to allow user registration by visitors
    // without administrator approval.
    $this->config('user.settings')
      ->set('register', 'visitors')
      ->set('verify_mail', FALSE)
      ->save();
  }

  /**
   * Tests the user registration form for Terms of Service field.
   */
  public function testUserRegistrationForm() {
    // Visit the user registration page.
    $this->drupalGet('user/register');

    // Verify the terms of service text is on the page.
    $this->assertSession()->pageTextContains('Anytown Terms and Conditions of Use');
    // And the checkbox is present.
    $this->assertSession()->fieldExists('terms_of_use_checkbox');
    // And that it is not checked.
    $this->assertSession()->checkboxNotChecked('terms_of_use_checkbox');

    // Attempt to submit the form without agreeing to the Terms of Service.
    $edit = [
      'name' => 'testuser',
      'mail' => 'testuser@example.com',
      'pass[pass1]' => 'password',
      'pass[pass2]' => 'password',
      // Do not check 'terms_of_service'.
    ];
    $this->submitForm($edit, 'Create new account');

    // Check that the form cannot be submitted and an error message is
    // displayed.
    $this->assertSession()->pageTextContains('I agree with the terms above field is required.');

    // Submit the form with the 'terms_of_service' checked.
    $edit['terms_of_use_checkbox'] = TRUE;
    $this->submitForm($edit, 'Create new account');

    // Verify the user registration is successful.
    $this->assertSession()->pageTextContains('Registration successful. You are now logged in.');
  }

}
