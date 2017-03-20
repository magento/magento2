<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert advanced price is absent on product page in form.
 */
class AssertAdvancedPriceAbsentOnProductForm extends AbstractConstraint
{
    /**
     * Assert advanced price is absent on product page in form.
     *
     * @param FixtureInterface[] $products
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(array $products, CatalogProductEdit $productPage)
    {
        foreach ($products as $product) {
            $productPage->open(['id' => $product->getData('id')]);
            /** @var AdvancedPricing $advancedPricing */
            $advancedPricing = $productPage->getProductForm()
                ->openSection('advanced-pricing')
                ->getSection('advanced-pricing');

            \PHPUnit_Framework_Assert::assertFalse(
                $advancedPricing->getTierPriceForm()->hasGroupPriceOptions(),
                'Customer group price options is present in grid.'
            );
        }
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Advanced price is absent on product page in form.';
    }
}
