<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

/**
 * Interface ConfigurableProductManagementInterface
 * @api
 */
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

    /**
     * Provide the number of product count
     *
     * @param null|int $status
     * @return int
     */
    public function getCount($status = null);
}
