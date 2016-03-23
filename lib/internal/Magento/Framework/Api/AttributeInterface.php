<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface for custom attribute value.
 *
 * @api
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
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode);

    /**
     * Get attribute value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set attribute value
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value);
}
