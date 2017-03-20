<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Assert items represented in order's entity view page.
 */
abstract class AbstractAssertItems extends AbstractAssertForm
{
    /**
     * Key for sort data.
     *
     * @var string
     */
    protected $sortKey = "::sku";

    /**
     * List compare fields.
     *
     * @var array
     */
    protected $compareFields = [
        'product',
        'sku',
        'qty',
    ];

    /**
     * Prepare order products.
     *
     * @param OrderInjectable $order
     * @param array|null $data [optional]
     * @param Cart|null $cart [optional]
     * @return array
     */
    protected function prepareOrderProducts(OrderInjectable $order, array $data = null, Cart $cart = null)
    {
        $productsData = [];
        if ($cart === null) {
            $cart['data']['items'] = ['products' => $order->getEntityId()['products']];
            $fixtureFactory = $this->objectManager->create(FixtureFactory::class);
            $cart = $fixtureFactory->createByCode('cart', $cart);
        }
        /** @var \Magento\Catalog\Test\Fixture\Cart\Item $item */
        foreach ($cart->getItems() as $key => $item) {
            if (isset($data[$key]['qty']) && $data[$key]['qty'] == 0) {
                continue;
            }
            $productsData[] = [
                'product' => $item->getData()['name'],
                'sku' => $item->getData()['sku'],
                'qty' => (isset($data[$key]['qty']))
                    ? $data[$key]['qty']
                    : $item->getData()['qty'],
            ];
        }

        return $this->sortDataByPath($productsData, $this->sortKey);
    }

    /**
     * Prepare invoice data.
     *
     * @param array $itemsData
     * @return array
     */
    protected function preparePageItems(array $itemsData)
    {
        foreach ($itemsData as $key => $itemData) {
            $itemsData[$key] = array_intersect_key($itemData, array_flip($this->compareFields));
        }
        return $this->sortDataByPath($itemsData, $this->sortKey);
    }
}
