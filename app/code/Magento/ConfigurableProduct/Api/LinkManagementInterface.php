<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

interface LinkManagementInterface
{
    /**
     * Get all children for Bundle product
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getChildren($sku);

    /**
     * @param  string $sku
     * @param  string $childSku
     * @return bool
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
     */
    public function removeChild($sku, $childSku);
}
