<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * @security-private
 */
class AssertThumbnailAltTextEscaped extends AbstractConstraint
{
    /**
     * Assert that alt text in image element is escaped
     *
     * @param CatalogProductIndex $productGrid
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $productGrid,
        InjectableFixture $product
    ) {
        $filter = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->search($filter);
        $imageAltText = $productGrid->getProductGrid()->getBaseImageAttribute('alt');
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($imageAltText, '"') === false,
            'Double quote is not escaped, XSS vulnerability present'
        );
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Alt text is properly escaped in thumbnail image';
    }
}
