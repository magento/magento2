<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit\Test;

/**
 * Test validator that always returns TRUE
 */
class IsTrue extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * Validate value
     *
     * @param mixed $value
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValid($value)
    {
        return true;
    }
}
