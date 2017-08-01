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
interface ProductCustomOptionRepositoryInterface
{
    /**
     * Get the list of custom options for a specific product
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]
     * @since 2.0.0
     */
    public function getList($sku);

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $requiredOnly
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]
     * @since 2.1.0
     */
    public function getProductOptions(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $requiredOnly = false
    );

    /**
     * Get custom option for a specific product
     *
     * @param string $sku
     * @param int $optionId
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface
     * @since 2.0.0
     */
    public function get($sku, $optionId);

    /**
     * Delete custom option from product
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
     * @return bool
     * @since 2.0.0
     */
    public function delete(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option);

    /**
     * Duplicate product options
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Catalog\Api\Data\ProductInterface $duplicate
     * @return mixed
     * @since 2.1.0
     */
    public function duplicate(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Catalog\Api\Data\ProductInterface $duplicate
    );

    /**
     * Save Custom Option
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface
     * @since 2.0.0
     */
    public function save(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option);

    /**
     * @param string $sku
     * @param int $optionId
     * @return bool
     * @since 2.0.0
     */
    public function deleteByIdentifier($sku, $optionId);
}
