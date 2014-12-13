<?php
/**
 * Validator Constraint Option interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Validator\Constraint;

interface OptionInterface
{
    /**
     * Get option value
     *
     * @return mixed
     */
    public function getValue();
}
