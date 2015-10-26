<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

interface ProductWebsiteLinkRepositoryInterface
{
    /**
     * Assign a product to the websites
     *
     * @param \Magento\Catalog\Api\Data\ProductWebsiteLinkInterface $productWebsiteLink
     * @return bool will returned True if assigned
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function save(Data\ProductWebsiteLinkInterface $productWebsiteLink);

    /**
     * Remove the websites assignment from the product by product sku
     *
     * @param string $sku
     * @param int $websiteId
     * @return bool will returned True if websites successfully unassigned from product
     *
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($sku, $websiteId);
}