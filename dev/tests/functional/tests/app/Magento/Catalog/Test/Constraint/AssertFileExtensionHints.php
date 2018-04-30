<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Block\Adminhtml\Product\ProductForm;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Checks file_extension field hint on the product page custom options section.
 */
class AssertFileExtensionHints extends AbstractAssertForm
{
    /**
     * Expected file_extension field hint.
     *
     * @var string
     */
    const EXPECTED_MESSAGE = 'Enter separated extensions, like: png, jpg, gif.';

    /**
     * Assert that file extension message is showed.
     *
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(CatalogProductEdit $productPage)
    {
        /** @var  ProductForm $productForm */
        $productForm = $productPage->getProductForm();
        $productForm->openSection('customer-options');
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Options $options */
        $options = $productForm->getSection('customer-options');
        $fileOptionElements = $options->getFileOptionElements();
        foreach ($fileOptionElements as $fileOptionElement) {
            \PHPUnit\Framework\Assert::assertEquals(
                self::EXPECTED_MESSAGE,
                $fileOptionElement->getText(),
                'Actual message differ from expected.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert correct file extensions hint is showed.';
    }
}
