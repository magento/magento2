<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

/**
 * Class AssertProductCanUpdate
 */
class AssertProductCanUpdate extends \Magento\Mtf\Constraint\AbstractConstraint
{
    /**
     * Assert that product with image can update without errors.
     *
     * @param \Magento\Mtf\Fixture\FixtureInterface $product
     * @param \Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit $catalogProductEdit
     * @param \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex $catalogProductIndex
     * @return void
     */
    public function processAssert(
        \Magento\Mtf\Fixture\FixtureInterface $product,
        \Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit $catalogProductEdit,
        \Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex $catalogProductIndex
    ) {
        $filter = ['sku' => $product->getSku()];
        $catalogProductIndex->open()->getProductGrid()->searchAndOpen($filter);
        $catalogProductEdit->getFormPageActions()->save();

        \PHPUnit_Framework_Assert::assertNotEmpty(
            $catalogProductEdit->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product with image was updated without errors.';
    }
}
