<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Api\Service\V1;

/**
 * API test for return items to stock
 */
class ReturnItemsAfterRefundOrderTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_REFUND_ORDER_NAME = 'salesRefundOrderV1';
    const SERVICE_STOCK_ITEMS_NAME = 'stockItems';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @dataProvider dataProvider
     * @magentoApiDataFixture Magento/Sales/_files/order_with_shipping_and_invoice.php
     */
    public function testRefundWithReturnItemsToStock($qtyRefund)
    {
        $productSku = 'simple';
        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');
        $orderItems = $existingOrder->getItems();
        $orderItem = array_shift($orderItems);
        $expectedItems = [['order_item_id' => $orderItem->getItemId(), 'qty' => $qtyRefund]];
        $qtyBeforeRefund = $this->getQtyInStockBySku($productSku);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $existingOrder->getEntityId() . '/refund',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_REFUND_ORDER_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_REFUND_ORDER_NAME . 'execute',
            ]
        ];

        $this->_webApiCall(
            $serviceInfo,
            [
                'orderId' => $existingOrder->getEntityId(),
                'items' => $expectedItems,
                'arguments' => [
                    'extension_attributes' => [
                        'return_to_stock_items' => [
                            (int)$orderItem->getItemId()
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
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->fail('Failed asserting that Creditmemo was created');
        }
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
}
