<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Assert that video data received from external service is correct.
 */
class AssertGetVideoInfoDataIsCorrect extends AbstractAssertForm
{
    /**
     * Assert that video data received from external service is correct.
     *
     * @param CatalogProductEdit $editProductPage
     * @param InjectableFixture $initialProduct
     * @param array $video
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $editProductPage,
        InjectableFixture $initialProduct,
        array $video
    ) {

        $editProductPage->open(['id' => $initialProduct->getId()]);
        $editProductPage->getProductForm()->openTab('images-and-videos');
        $imagesTab = $editProductPage->getProductForm()->getTab('images-and-videos');
        $result = $imagesTab->clickFirstVideo()->getVideoDialog()->validate($video);

        \PHPUnit_Framework_Assert::assertTrue(
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
