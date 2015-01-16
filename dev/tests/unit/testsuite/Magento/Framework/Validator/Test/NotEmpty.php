<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test;

/**
 * Not empty test validator
 */
class NotEmpty extends \Zend_Validate_NotEmpty implements \Magento\Framework\Validator\ValidatorInterface
{
    /**
     * Custom constructor.
     * Needed because parent Zend class has the bug - when default value NULL is passed to the constructor,
     * then it throws the exception.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
    }
}
