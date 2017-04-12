<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert notice that existing sku automatically changed when saving product with same sku.
 */
class AssertProductAutoincrementedSkuNoticeMessage extends AbstractConstraint
{
    /**
     * Assert notice that existing sku automatically changed when saving product with same sku.
     *
     * @param CatalogProductEdit $productPage
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CatalogProductEdit $productPage, FixtureInterface $product)
    {
        $actualMessage = $productPage->getMessagesBlock()->getNoticeMessage();
        $reg = '/(SKU for product ' . $product->getName() . ' has been changed to ' . $product->getSku() . '-)(\d+.$)/';
        \PHPUnit_Framework_Assert::assertTrue(
            preg_match($reg, $actualMessage) == 1,
            'Incorrect notice that existing sku automatically changed when saving product with same sku.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Notice that existing sku automatically changed when saving product with same sku is correct.';
    }
}
