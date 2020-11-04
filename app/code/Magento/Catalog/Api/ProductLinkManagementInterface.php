<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ProductLinkManagementInterface
{
    /**
     * Provide the list of links for a specific product
     *
     * @param string $sku
     * @param string $type
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     */
    public function getLinkedItemsByType($sku, $type);

    /**
     * Assign a product link to another product
     *
     * @param string $sku
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $items
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function setProductLinks($sku, array $items);
}
