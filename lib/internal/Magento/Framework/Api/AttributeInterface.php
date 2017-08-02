<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface for custom attribute value.
 *
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAttributeCode();

    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeCode($attributeCode);

    /**
     * Get attribute value
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getValue();

    /**
     * Set attribute value
     *
     * @param mixed $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value);
}
