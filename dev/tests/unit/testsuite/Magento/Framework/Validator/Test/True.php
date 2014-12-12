<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Validator\Test;

/**
 * Test validator that always returns TRUE
 */
class True extends \Magento\Framework\Validator\AbstractValidator
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
