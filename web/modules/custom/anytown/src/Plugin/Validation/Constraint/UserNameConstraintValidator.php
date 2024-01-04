<?php

declare(strict_types = 1);

namespace Drupal\anytown\Plugin\Validation\Constraint;

use Drupal\user\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UserNameConstraint constraint.
 */
final class UserNameConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$value instanceof UserInterface) {
      throw new \InvalidArgumentException(
        sprintf('The validated value must be an instance of \Drupal\user\UserInterface, %s was given.', get_debug_type($value))
      );
    }

    /** @var \Drupal\user\UserInterface $value */
    if ($value->getAccountName() === 'anytown') {
      $this->context->buildViolation($constraint->message)
        ->atPath('name')
        ->addViolation();
    }
  }

}
