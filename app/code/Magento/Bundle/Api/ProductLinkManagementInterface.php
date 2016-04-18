<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

/**
 * Interface for Management of ProductLink
 * @api
 */
interface ProductLinkManagementInterface
{
    /**
     * Get all children for Bundle product
     *
     * @param string $productSku
     * @param int $optionId
     * @return \Magento\Bundle\Api\Data\LinkInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getChildren($productSku, $optionId = null);

    /**
     * Add child product to specified Bundle option by product sku
     *
     * @param string $sku
     * @param int $optionId
     * @param \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return int
     */
    public function addChildByProductSku($sku, $optionId, \Magento\Bundle\Api\Data\LinkInterface $linkedProduct);

    /**
     * @param string $sku
     * @param \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function saveChild(
        $sku,
        \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
    );

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $optionId
     * @param \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @return int
     */
    public function addChild(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $optionId,
        \Magento\Bundle\Api\Data\LinkInterface $linkedProduct
    );

    /**
     * Remove product from Bundle product option
     *
     * @param string $sku
     * @param int $optionId
     * @param string $childSku
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function removeChild($sku, $optionId, $childSku);
}
