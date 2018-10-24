<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Assert that video data received from external service is correct.
 */
class AssertGetVideoInfoDataIsCorrect extends AbstractAssertForm
{
    /**
     * Assert that video data received from external service is correct.
     *
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @param InjectableFixture $product
     * @param array $video
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage,
        InjectableFixture $product,
        array $video
    ) {
        $productGrid->open();
        $productGrid->getProductGrid()->searchAndOpen(['sku' => $product->getSku()]);

        $editProductPage->getProductForm()->openSection('images-and-videos');
        $imagesTab = $editProductPage->getProductForm()->getSection('images-and-videos');
        $result = $imagesTab->clickFirstVideo()->getVideoDialog()->validate($video);

        \PHPUnit\Framework\Assert::assertTrue(
            $result,
            'Video data received from external service is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Video data received from external service is correct.';
    }
}
