<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

/**
 * Manage children products of configurable product
 *
 * @api
 * @since 2.0.0
 */
interface LinkManagementInterface
{
    /**
     * Get all children for Configurable product
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     * @since 2.0.0
     */
    public function getChildren($sku);

    /**
     * @param  string $sku
     * @param  string $childSku
     * @return bool
     * @since 2.0.0
     */
    public function addChild($sku, $childSku);

    /**
     * Remove configurable product option
     *
     * @param string $sku
     * @param string $childSku
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     * @since 2.0.0
     */
    public function removeChild($sku, $childSku);
}
