<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Interface ProductWebsiteLinkRepositoryInterface
 * @api
 * @since 2.0.0
 */
interface ProductWebsiteLinkRepositoryInterface
{
    /**
     * Assign a product to the website
     *
     * @param \Magento\Catalog\Api\Data\ProductWebsiteLinkInterface $productWebsiteLink
     * @return bool will returned True if website successfully assigned to product
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @since 2.0.0
     */
    public function save(Data\ProductWebsiteLinkInterface $productWebsiteLink);

    /**
     * Remove the website assignment from the product
     *
     * @param \Magento\Catalog\Api\Data\ProductWebsiteLinkInterface $productWebsiteLink
     * @return bool will returned True if website successfully unassigned from product
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @since 2.0.0
     */
    public function delete(Data\ProductWebsiteLinkInterface $productWebsiteLink);

    /**
     * Remove the website assignment from the product by product sku
     *
     * @param string $sku
     * @param int $websiteId
     * @return bool will returned True if website successfully unassigned from product
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @since 2.0.0
     */
    public function deleteById($sku, $websiteId);
}
