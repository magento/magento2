<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Api\Service\V1;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API test for return items to stock
 */
class ReturnItemsAfterRefundOrderTest extends WebapiAbstract
{
    private const SERVICE_REFUND_ORDER_NAME = 'salesRefundOrderV1';
    private const SERVICE_STOCK_ITEMS_NAME = 'stockItems';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @magentoApiDataFixture Magento\Catalog\Test\Fixture\Product as:product
     * @magentoApiDataFixture Magento\Quote\Test\Fixture\GuestCart as:cart
     * @magentoApiDataFixture Magento\Quote\Test\Fixture\AddProductToCart as:item1
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetShippingAddress with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetBillingAddress with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetGuestEmail with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetDeliveryMethod with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetPaymentMethod with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\PlaceOrder with:{"cart_id":"$cart.id$"} as:order
     * @magentoApiDataFixture Magento\Sales\Test\Fixture\Invoice with:{"order_id":"$order.id$"}
     * @magentoApiDataFixture Magento\Sales\Test\Fixture\Shipment with:{"order_id":"$order.id$"}
     * @magentoDataFixtureDataProvider {"item1":{"cart_id":"$cart.id$","product_id":"$product.id$","qty":2}}
     * @dataProvider dataProvider
     */
    public function testRefundWithReturnItemsToStock($qtyRefund)
    {
        $product = $this->fixtures->get('product');
        $order = $this->fixtures->get('order');
        $productSku = $product->getSku();
        $orderItems = $order->getItems();
        $orderItem = array_shift($orderItems);
        $expectedItems = [['order_item_id' => $orderItem->getItemId(), 'qty' => $qtyRefund]];
        $qtyBeforeRefund = $this->getQtyInStockBySku($productSku);
        $this->_webApiCall(
            $this->getServiceData($order),
            [
                'orderId' => $order->getEntityId(),
                'items' => $expectedItems,
                'arguments' => [
                    'extension_attributes' => [
                        'return_to_stock_items' => [
                            (int) $orderItem->getItemId()
                        ],
                    ],
                ],
            ]
        );

        $qtyAfterRefund = $this->getQtyInStockBySku($productSku);

        try {
            $this->assertEquals(
                $qtyBeforeRefund + $expectedItems[0]['qty'],
                $qtyAfterRefund,
                'Failed asserting qty of returned items incorrect.'
            );
        } catch (NoSuchEntityException $e) {
            $this->fail('Failed asserting that Creditmemo was created');
        }
    }

    /**
     * @magentoApiDataFixture Magento\Catalog\Test\Fixture\Product as:product
     * @magentoApiDataFixture Magento\Quote\Test\Fixture\GuestCart as:cart
     * @magentoApiDataFixture Magento\Quote\Test\Fixture\AddProductToCart as:item1
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetShippingAddress with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetBillingAddress with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetGuestEmail with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetDeliveryMethod with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\SetPaymentMethod with:{"cart_id":"$cart.id$"}
     * @magentoApiDataFixture Magento\Checkout\Test\Fixture\PlaceOrder with:{"cart_id":"$cart.id$"} as:order
     * @magentoApiDataFixture Magento\Sales\Test\Fixture\Invoice with:{"order_id":"$order.id$"}
     * @magentoDataFixtureDataProvider {"item1":{"cart_id":"$cart.id$","product_id":"$product.id$","qty":1}}
     * @dataProvider refundWithReturnItemsToStockUnshippedOrderDataProvider
     */
    public function testRefundWithReturnItemsToStockUnshippedOrder(
        bool $returnBackToStock,
        int $qtyInStockAfter
    ): void {
        $order = $this->fixtures->get('order');
        $product = $this->fixtures->get('product');
        $productSku = $product->getSku();
        $orderItems = $order->getItems();
        $orderItem = array_shift($orderItems);
        $result = $this->_webApiCall(
            $this->getServiceData($order),
            [
                'orderId' => $order->getEntityId(),
                'items' => [['order_item_id' => $orderItem->getItemId(), 'qty' => 1]],
                'arguments' => [
                    'extension_attributes' => [
                        'return_to_stock_items' => $returnBackToStock ? [(int) $orderItem->getItemId()] : [],
                    ],
                ],
            ]
        );
        $this->assertIsNumeric(
            $result,
            'Failed asserting that creditmemo was created'
        );
        $this->assertEquals($qtyInStockAfter, $this->getQtyInStockBySku($productSku));
    }

    /**
     * @return array
     */
    public function refundWithReturnItemsToStockUnshippedOrderDataProvider()
    {
        return [
            [false, 99],
            [true, 100]
        ];
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'refundAllOrderItems' => [2],
            'refundPartition' => [1],
        ];
    }

    /**
     * @param string $sku
     * @return int
     */
    private function getQtyInStockBySku($sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/' . self::SERVICE_STOCK_ITEMS_NAME . "/$sku",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'catalogInventoryStockRegistryV1',
                'serviceVersion' => 'V1',
                'operation' => 'catalogInventoryStockRegistryV1GetStockItemBySku',
            ],
        ];
        $arguments = ['productSku' => $sku];
        $apiResult = $this->_webApiCall($serviceInfo, $arguments);
        return $apiResult['qty'];
    }

    /**
     * Prepares and returns info for API service.
     *
     * @param OrderInterface $order
     *
     * @return array
     */
    private function getServiceData(OrderInterface $order)
    {
        return [
            'rest' => [
                'resourcePath' => '/V1/order/' . $order->getEntityId() . '/refund',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_REFUND_ORDER_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_REFUND_ORDER_NAME . 'execute',
            ]
        ];
    }
}
