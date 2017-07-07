<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Assert that product quantity is decreased after placing an order.
 */
class AssertProductQtyDecreased extends AbstractConstraint
{
    /**
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Skip fields for create product fixture.
     *
     * @var array
     */
    protected $skipFields = [
        'attribute_set_id',
        'website_ids',
        'checkout_data',
        'type_id',
        'price',
    ];

    /**
     * AssertProductQtyDecreased constructor.
     *
     * @param ObjectManager $objectManager
     * @param EventManagerInterface $eventManager
     * @param FixtureFactory $fixtureFactory
     */
    public function __construct(
        ObjectManager $objectManager,
        EventManagerInterface $eventManager,
        FixtureFactory $fixtureFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
        parent::__construct($objectManager, $eventManager);
    }

    /**
     * Assert form data equals fixture data.
     *
     * @param OrderInjectable $order
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage
    ) {
        $product = $this->getProduct($order);
        $this->objectManager->get(\Magento\Catalog\Test\Constraint\AssertProductForm::class)->processAssert(
            $product,
            $productGrid,
            $productPage
        );
    }

    /**
     * Get product's fixture.
     *
     * @param OrderInjectable $order
     * @param int $index [optional]
     * @return FixtureInterface
     */
    protected function getProduct(OrderInjectable $order, $index = 0)
    {
        $product = $order->getEntityId()['products'][$index];
        $productData = $product->getData();
        $checkoutDataQty = isset($productData['checkout_data']['qty']) ? $productData['checkout_data']['qty'] : 1;
        $productData['quantity_and_stock_status']['qty'] -= $checkoutDataQty;

        $productData = array_diff_key($productData, array_flip($this->skipFields));

        return $this->fixtureFactory->create(get_class($product), ['data' => $productData]);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product qty was decreased after placing an order.';
    }
}
