<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Attribute metadata object builder interface.
 */
interface AttributeMetadataBuilderInterface
{
    /**
     * Set code of the attribute.
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode);

    /**
     * Build the attribute data object.
     *
     * @return AbstractSimpleObject
     */
    public function create();
}
