<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that video is not displayed in admin product grid
 */
class AssertNoVideoAdminhtmlGrid extends AbstractConstraint
{
    /**
     * Assert that video is not displayed in admin panel
     *
     * @param CatalogProductIndex $productGrid
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $productGrid,
        BrowserInterface $browser,
        InjectableFixture $product
    ) {

        $filter = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->search($filter);
        $photo = $browser->find('.data-grid-thumbnail-cell img');
        $src = $photo->getAttribute('src');
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($src, '/placeholder/') !== false,
            'Product image is displayed in product grid when it should not'
        );

    }


    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product image is not displayed in product grid.';
    }
}
