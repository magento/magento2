<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 */
interface CategoryLinkRepositoryInterface
{
    /**
     * Assign a product to the required category
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink
     * @return bool will returned True if assigned
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function save(\Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink);

    /**
     * Remove the product assignment from the category
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink
     * @return bool will returned True if products successfully deleted
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Magento\Catalog\Api\Data\CategoryProductLinkInterface $productLink);

    /**
     * Remove the product assignment from the category by category id and sku
     *
     * @param string $sku
     * @param string $sku
     * @return bool will returned True if products successfully deleted
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteByIds($categoryId, $sku);
}
