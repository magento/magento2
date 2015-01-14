<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

interface ConfigurableProductManagementInterface
{
    /**
     * Generate variation based on same product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\ConfigurableProduct\Api\Data\OptionInterface[] $options
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function generateVariation(\Magento\Catalog\Api\Data\ProductInterface $product, $options);
}
