<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Interface Product links handling interface
 * @api
 */
interface ProductLinkRepositoryInterface
{
    /**
     * Get product links list
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]
     * @since 2.1.0
     */
    public function getList(\Magento\Catalog\Api\Data\ProductInterface $product);

    /**
     * Save product link
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface $entity
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function save(\Magento\Catalog\Api\Data\ProductLinkInterface $entity);

    /**
     * Delete product link
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface $entity
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function delete(\Magento\Catalog\Api\Data\ProductLinkInterface $entity);

    /**
     * @param string $sku
     * @param string $type
     * @param string $linkedProductSku
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function deleteById($sku, $type, $linkedProductSku);
}
