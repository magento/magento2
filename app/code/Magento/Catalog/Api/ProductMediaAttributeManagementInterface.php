<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @api
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
