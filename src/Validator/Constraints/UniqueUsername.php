<?php
/**
 * Unique Username constraint.
 */
namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueUsername.
 *
 * @package Validator\Constraints
 */

class UniqueUsername extends Constraint
{
    /**
     * Message.
     *
     * @var string $message
     */
    public $message = '"{{ login }}" is not unique username.';

    /**
     * Element id.
     *
     * @var int|string|null $elementId
     */
    public $elementId = null;

    /**
     * Tag repository.
     *
     * @var null|\Repository\TagRepository $repository
     */
    public $repository = null;
}
