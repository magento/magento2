<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface for entities which can be extended with custom attributes.
 *
 * @api
 * @since 100.0.2
 */
interface CustomAttributesDataInterface extends ExtensibleDataInterface
{
    /**
     * Array key for custom attributes
     */
    const CUSTOM_ATTRIBUTES = 'custom_attributes';

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null
     */
    public function getCustomAttribute($attributeCode);

    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue);

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes();

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function setCustomAttributes(array $attributes);
}
