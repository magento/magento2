<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ProductMediaAttributeManagementInterface
{
    /**
     * Retrieve the list of media attributes (fronted input type is media_image) assigned to the given attribute set.
     *
     * @param string $attributeSetName
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[] list of media attributes
     */
    public function getList($attributeSetName);
}
