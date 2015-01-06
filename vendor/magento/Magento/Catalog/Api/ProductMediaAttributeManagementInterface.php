<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Api;

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
