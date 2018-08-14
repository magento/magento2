<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Catalog\Test\TestStep\ConfigureProductOnProductPageStep;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Assert calculated price after configure bundle product on product page.
 */
class AssertBundlePriceCalculatedOnProductPage extends AbstractConstraint
{
    /**
     * Assert calculated price after configure bundle product on product page.
     *
     * @param TestStepFactory $stepFactory
     * @param BundleProduct $product
     * @param CatalogProductView $catalogProductView
     */
    public function processAssert(
        TestStepFactory $stepFactory,
        BundleProduct $product,
        CatalogProductView $catalogProductView
    ) {
        $stepFactory->create(ConfigureProductOnProductPageStep::class, ['product' => $product])->run();

        //Process assertions
        $this->assertPrice($product, $catalogProductView);
    }

    /**
     * Assert prices on the product view Page.
     *
     * @param BundleProduct $product
     * @param CatalogProductView $productView
     * @return void
     */
    protected function assertPrice(BundleProduct $product, CatalogProductView $productView)
    {
        $checkoutData = $product->getCheckoutData();
        \PHPUnit_Framework_Assert::assertEquals(
            $checkoutData['cartItem']['configuredPrice'],
            $productView->getBundleViewBlock()->getBundleSummaryBlock()->getConfiguredPriceBlock()->getPrice(),
            'Bundle price calculated is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle price calculates right on product view page.';
    }
}
