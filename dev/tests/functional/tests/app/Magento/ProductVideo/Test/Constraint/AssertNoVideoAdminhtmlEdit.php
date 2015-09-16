<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that video is not displayed in admin product edit
 */
class AssertNoVideoAdminhtmlEdit extends AbstractConstraint
{
    /**
     * Assert that video is not displayed in admin panel
     *
     * @param CatalogProductEdit $editProductPage
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $editProductPage,
        BrowserInterface $browser,
        InjectableFixture $product
    ) {
        $editProductPage->open(['id' => $product->getId()]);
        $editProductPage->getProductForm()->openTab('images');
        $image = $browser->find('#media_gallery_content img.product-image.video-item');
        \PHPUnit_Framework_Assert::assertFalse(
            $image->isVisible(),
            'Product image is displayed in product edit when it should not'
        );
    }


    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product image is not displayed in product edit.';
    }
}
