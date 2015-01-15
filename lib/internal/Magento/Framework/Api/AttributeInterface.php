<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
