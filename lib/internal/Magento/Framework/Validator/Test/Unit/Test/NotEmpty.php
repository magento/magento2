<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Test;

use Magento\Framework\Validator\ValidatorInterface;

/**
 * Not empty test validator
 */
class NotEmpty extends \Zend_Validate_NotEmpty implements ValidatorInterface
{
    /**
     * Custom constructor.
     * Needed because parent Zend class has the bug - when default value NULL is passed to the constructor,
     * then it throws the exception.
     *
     * @param array $options
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
    }
}
