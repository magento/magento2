<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\ConfigurableProduct\Model;

/**
 * Interface to retrieve options for attribute
 * @api
 * @since 100.1.11
 */
interface AttributeOptionProviderInterface
{
    /**
     * Retrieve options for attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param int $productId
     * @return array
     * @since 100.1.11
     */
    public function getAttributeOptions(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, $productId);
}
