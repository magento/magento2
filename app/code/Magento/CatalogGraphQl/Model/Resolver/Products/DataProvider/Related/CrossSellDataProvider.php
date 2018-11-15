<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Related;


/**
 * Class CrossSellDataProvider
 * @package Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Related
 */
class CrossSellDataProvider extends AbstractDataProvider
{
    /**
     * @param $product
     */
    protected function prepareCollection($product): void
    {
        $this->collection = $product->getCrossSellProductCollection();
        $this->collection->addAttributeToSelect($this->getFields());
    }

}