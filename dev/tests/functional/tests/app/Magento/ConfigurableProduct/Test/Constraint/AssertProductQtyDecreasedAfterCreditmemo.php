<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Class AssertProductQtyDecreasedAfterCreditmemo
 */
class AssertProductQtyDecreasedAfterCreditmemo extends AbstractConstraint
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
     * AssertFirstProductForm constructor.
     * @param ObjectManager $objectManager
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
     * Assert form data equals fixture data
     *
     * @param OrderInjectable $order
     * @param array $data
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        array $data,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage
    ) {
        $product = $this->getProduct($order, $data);
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
     * @param array $data
     * @param int $index [optional]
     * @return FixtureInterface
     */
    protected function getProduct(OrderInjectable $order, array $data, $index = 0)
    {
        if (!isset($data['items_data'][$index]['back_to_stock'])
            || $data['items_data'][$index]['back_to_stock'] != 'Yes'
        ) {
            return $order->getEntityId()['products'][$index];
        }
        $product = $order->getEntityId()['products'][$index];
        $productData = $product->getData();
        $checkoutDataQty = $productData['checkout_data']['qty'];

        $productKey = '';
        foreach ($productData['checkout_data']['options']['configurable_options'] as $option) {
            $productKey .= ' ' . $option['title'] . ':' . $option['value'];
        }
        $productKey = trim($productKey);
        $optionProduct = $productData['configurable_attributes_data']['matrix'][$productKey];
        $optionProduct['qty'] -= ($checkoutDataQty - $data['items_data'][$index]['qty']);
        $productData = $optionProduct;

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
        return 'Product qty was decreased after creditmemo creation.';
    }
}
