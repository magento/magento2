<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Constraint\AssertProductInCategory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Check bundle product on the category page.
 */
class AssertBundleInCategory extends AssertProductInCategory
{
    /**
     * Verify product price on category view page.
     *
     * @param FixtureInterface $bundle
     * @param CatalogCategoryView $catalogCategoryView
     * @return void
     */
    protected function assertPrice(FixtureInterface $bundle, CatalogCategoryView $catalogCategoryView)
    {
        /** @var BundleProduct $bundle */
        $priceData = $bundle->getDataFieldConfig('price')['source']->getPriceData();
        //Price from/to verification
        $priceBlock = $catalogCategoryView->getListProductBlock()->getProductItem($bundle)->getPriceBlock();

        if ($bundle->hasData('special_price')) {
            $priceLow = $priceBlock->getPrice();
        } else {
            $priceLow = ($bundle->getPriceView() == 'Price Range')
                ? $priceBlock->getPriceFrom()
                : $priceBlock->getPrice();
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $priceData['price_from'],
            $priceLow,
            'Bundle price From on category page is not correct.'
        );
        if ($bundle->getPriceView() == 'Price Range') {
            \PHPUnit_Framework_Assert::assertEquals(
                $priceData['price_to'],
                $priceBlock->getPriceTo(),
                'Bundle price To on category page is not correct.'
            );
        }
    }

    /**
     * Text of Visible in category assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle price on category page is not correct.';
    }
}
