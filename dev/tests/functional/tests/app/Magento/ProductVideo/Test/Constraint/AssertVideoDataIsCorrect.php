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
 * Assert that video data is correct
 */
class AssertVideoDataIsCorrect extends AbstractConstraint
{
    /**
     * Assert that video data is correct
     *
     * @param CatalogProductEdit $editProductPage
     * @param array $video
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $editProductPage,
        array $video,
        InjectableFixture $product
    ) {
        $editProductPage->open(['id' => $product->getId()]);
        $editProductPage->getProductForm()->openTab('images');

        $imagesTab = $editProductPage->getProductForm()->getTab('images');
        $imagesTab->clickFirstVideo();

        $videoDialog = $imagesTab->getVideoDialog();


        \PHPUnit_Framework_Assert::assertTrue(
            $videoDialog->validate($video),
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
        return 'Video data is correct.';
    }
}
