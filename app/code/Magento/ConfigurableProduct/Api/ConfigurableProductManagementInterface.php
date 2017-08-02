<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

/**
 * Interface ConfigurableProductManagementInterface
 * @api
 * @since 2.0.0
 */
interface ConfigurableProductManagementInterface
{
    /**
     * Generate variation based on same product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\ConfigurableProduct\Api\Data\OptionInterface[] $options
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     * @since 2.0.0
     */
    public function generateVariation(\Magento\Catalog\Api\Data\ProductInterface $product, $options);

    /**
     * Provide the number of product count
     *
     * @param int|null $status
     * @return int
     * @since 2.0.0
     */
    public function getCount($status = null);
}
