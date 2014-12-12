<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Api;

/**
 * Interface for custom attribute value.
 */
interface AttributeInterface
{
    /**#@+
     * Constant used as key into $_data
     */
    const ATTRIBUTE_CODE = 'attribute_code';
    const VALUE = 'value';
    /**#@-*/

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Get attribute value
     *
     * @return mixed
     */
    public function getValue();
}
