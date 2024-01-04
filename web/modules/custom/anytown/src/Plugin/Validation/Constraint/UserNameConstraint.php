<?php

declare(strict_types = 1);

namespace Drupal\anytown\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a UserNameConstraint constraint.
 *
 * @Constraint(
 *   id = "AnytownUserNameConstraint",
 *   label = @Translation("User name can not be anytown", context = "Validation"),
 * )
 *
 * @see https://www.drupal.org/node/2015723.
 */
final class UserNameConstraint extends Constraint {

  /**
   * Message to display for invalid username.
   *
   * @var string
   */
  public string $message = 'Invalid user name. Can not use "anytown" as the user name.';

}
