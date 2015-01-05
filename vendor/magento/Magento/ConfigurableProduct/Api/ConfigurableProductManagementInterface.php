<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
