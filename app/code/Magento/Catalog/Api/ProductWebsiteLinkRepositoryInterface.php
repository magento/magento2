<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Interface ProductWebsiteLinkRepositoryInterface
 * @api
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
     */
    public function save(Data\ProductWebsiteLinkInterface $productWebsiteLink);

    /**
     * Remove the website assignment from the product
     *
     * @param \Magento\Catalog\Api\Data\ProductWebsiteLinkInterface $productWebsiteLink
     * @return bool will returned True if website successfully unassigned from product
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
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
     */
    public function deleteById($sku, $websiteId);
}
