<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

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
     * @throws NoSuchEntityException
     * @return ProductLinkInterface[]
     */
    public function getLinkedItemsByType($sku, $type);

    /**
     * Assign a product link to another product
     *
     * @param string $sku
     * @param ProductLinkInterface[] $items
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @return bool
     */
    public function setProductLinks($sku, array $items);
}
