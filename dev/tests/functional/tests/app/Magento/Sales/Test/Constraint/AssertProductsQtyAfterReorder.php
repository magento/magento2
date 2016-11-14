<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Catalog\Test\Constraint\AssertProductForm;
use Magento\ConfigurableProduct\Test\Constraint\AssertConfigurableProductForm;

/**
 * Assert that products quantity is correct after reorder.
 */
class AssertProductsQtyAfterReorder extends AbstractConstraint
{
    /**
     * Assert products quantity after placing new order with the same products.
     *
     * @param OrderInjectable $order
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @param FixtureFactory $fixtureFactory
     * @param AssertProductForm $assertProductForm
     * @param AssertConfigurableProductForm $assertConfigurableProductForm
     * @param AssertProductsQtyAfterOrderCancel $assertProductsQty
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage,
        FixtureFactory $fixtureFactory,
        AssertProductForm $assertProductForm,
        AssertConfigurableProductForm $assertConfigurableProductForm,
        AssertProductsQtyAfterOrderCancel $assertProductsQty
    ) {
        $newOrder = $fixtureFactory->createByCode('orderInjectable', [
            'dataset' => 'default',
            'data' => [
                'entity_id' => [
                    'products' => $order->getEntityId()['products'],
                ]
            ]
        ]);
        $newOrder->persist();
        $assertProductsQty->processAssert(
            $newOrder,
            $productGrid,
            $productPage,
            $fixtureFactory,
            $assertProductForm,
            $assertConfigurableProductForm
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products quantity is correct after reorder.';
    }
}
