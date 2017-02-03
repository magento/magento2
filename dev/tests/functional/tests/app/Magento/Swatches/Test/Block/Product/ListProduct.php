<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Product;

use Magento\Catalog\Test\Block\Product\ListProduct as CatalogListProduct;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Swatches\Test\Block\Product\ProductList\ProductItem;

/**
 * Product list block.
 */
class ListProduct extends CatalogListProduct
{
    /**
     * @inheritdoc
     */
    public function getProductItem(FixtureInterface $product)
    {
        /** @var string $locator */
        $locator = sprintf($this->productItem, $product->getName());

        return $this->blockFactory->create(
            ProductItem::class,
            [
                'element' => $this->_rootElement->find($locator, Locator::SELECTOR_XPATH),
            ]
        );
    }
}
