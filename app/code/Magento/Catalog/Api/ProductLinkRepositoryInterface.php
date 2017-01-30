<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
