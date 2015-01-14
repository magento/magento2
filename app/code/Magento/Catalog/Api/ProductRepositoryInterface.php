<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

interface ProductRepositoryInterface
{
    /**
     * Create product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $saveOptions
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Magento\Catalog\Api\Data\ProductInterface $product, $saveOptions = false);

    /**
     * Get info about product by product SKU
     *
     * @param string $productSku
     * @param bool $editMode
     * @param null|int $storeId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($productSku, $editMode = false, $storeId = null);

    /**
     * Get info about product by product id
     *
     * @param int $productId
     * @param bool $editMode
     * @param null|int $storeId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($productId, $editMode = false, $storeId = null);

    /**
     * Delete product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Magento\Catalog\Api\Data\ProductInterface $product);

    /**
     * @param string $productSku
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($productSku);

    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
