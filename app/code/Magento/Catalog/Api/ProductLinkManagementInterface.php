<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 2.0.0
 */
interface ProductLinkManagementInterface
{
    /**
     * Provide the list of links for a specific product
     *
     * @param string $sku
     * @param string $type
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     * @since 2.0.0
     */
    public function getLinkedItemsByType($sku, $type);

    /**
     * Assign a product link to another product
     *
     * @param string $sku
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $items
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     * @since 2.0.0
     */
    public function setProductLinks($sku, array $items);
}
