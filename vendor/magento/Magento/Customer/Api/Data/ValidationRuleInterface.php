<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Api\Data;

/**
 * Validation rule interface.
 */
interface ValidationRuleInterface
{
    /**#@+
     * Constants for keys of data array
     */
    const NAME = 'name';
    const VALUE = 'value';
    /**#@-*/

    /**
     * Get validation rule name
     *
     * @return string
     */
    public function getName();

    /**
     * Get validation rule value
     *
     * @return string
     */
    public function getValue();
}
