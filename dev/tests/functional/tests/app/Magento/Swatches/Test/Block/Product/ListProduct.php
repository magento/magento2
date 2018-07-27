<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Block\Product;

use Magento\Catalog\Test\Block\Product\ListProduct as CatalogListProduct;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Swatches\Test\Block\Product\ProductList\ProductItem;

/**
 * @inheritdoc
 */
class ListProduct extends CatalogListProduct
{
    /**
     * @inheritdoc
     */
    public function getProductItem(FixtureInterface $product)
    {
        $locator = sprintf($this->productItem, $product->getName());

        return $this->blockFactory->create(
            ProductItem::class,
            ['element' => $this->_rootElement->find($locator, Locator::SELECTOR_XPATH)]
        );
    }
}
