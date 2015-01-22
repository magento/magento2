<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

interface ProductLinkManagementInterface
{
    /**
     * Provide the list of links for a specific product
     *
     * @param string $productSku
     * @param string $type
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     */
    public function getLinkedItemsByType($productSku, $type);

    /**
     * Assign a product link to another product
     *
     * @param string $productSku
     * @param string $type
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $items
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function setProductLinks($productSku, $type, array $items);
}
