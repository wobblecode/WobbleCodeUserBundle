<?php

namespace WobbleCode\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotAnonymousEmail extends Constraint
{
    public $message = 'This email address is not allowed.';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
