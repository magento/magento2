<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Sales\Model\Order;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Test order updating via webapi
 */
class OrderUpdateTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/orders';

    private const SERVICE_NAME = 'salesOrderRepositoryV1';

    private const SERVICE_VERSION = 'V1';

    private const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Check order increment id after updating via webapi
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderUpdate()
    {
        /** @var Order $order */
        $order = $this->objectManager->get(Order::class)->loadByIncrementId(self::ORDER_INCREMENT_ID);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'save',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['entity' => $this->getOrderData($order)]);
        $this->assertGreaterThan(1, count($result));
        /** @var Order $actualOrder */
        $actualOrder = $this->objectManager->get(Order::class)->load($order->getId());
        $this->assertEquals(
            $order->getData(OrderInterface::INCREMENT_ID),
            $actualOrder->getData(OrderInterface::INCREMENT_ID)
        );

        //Ship the order and check increment id.
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $order->getId() . '/ship',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'salesShipOrderV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'salesShipOrderV1' . 'execute',
            ],
        ];
        $shipmentId = $this->_webApiCall($serviceInfo, $this->getDataForShipment($order));
        $this->assertNotEmpty($shipmentId);
        $actualOrder = $this->objectManager->get(Order::class)->load($order->getId());
        $this->assertEquals(
            $order->getData(OrderInterface::INCREMENT_ID),
            $actualOrder->getData(OrderInterface::INCREMENT_ID)
        );

        //Invoice the order and check increment id.
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/invoices',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'salesInvoiceRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'salesInvoiceRepositoryV1' . 'save',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, ['entity' => $this->getDataForInvoice($order)]);
        $this->assertNotEmpty($result);
        $actualOrder = $this->objectManager->get(Order::class)->load($order->getId());
        $this->assertEquals(
            $order->getData(OrderInterface::INCREMENT_ID),
            $actualOrder->getData(OrderInterface::INCREMENT_ID)
        );

        //Create creditmemo for the order and check increment id.
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/creditmemo',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'salesCreditmemoRepositoryV1',
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'salesCreditmemoRepositoryV1' . 'save',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['entity' => $this->getDataForCreditmemo($order)]);
        $this->assertNotEmpty($result);
        $actualOrder = $this->objectManager->get(Order::class)->load($order->getId());
        $this->assertEquals(
            $order->getData(OrderInterface::INCREMENT_ID),
            $actualOrder->getData(OrderInterface::INCREMENT_ID)
        );
    }

    /**
     * Check order increment id after updating via webapi
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderStatusUpdate()
    {
        /** @var Order $order */
        $order = $this->objectManager->get(Order::class)
            ->loadByIncrementId(self::ORDER_INCREMENT_ID);

        $entityData = $this->getOrderData($order);
        $entityData[OrderInterface::STATE] = 'complete';
        $entityData[OrderInterface::STATUS] = 'complete';

        $requestData = ['entity' => $entityData];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'save',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertGreaterThan(1, count($result));

        /** @var Order $actualOrder */
        $actualOrder = $this->objectManager->get(Order::class)->load($order->getId());
        $this->assertEquals(
            $order->getData(OrderInterface::INCREMENT_ID),
            $actualOrder->getData(OrderInterface::INCREMENT_ID)
        );
    }

    /**
     * Check order increment id after updating via webapi
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderNoAttributesProvidedUpdate()
    {
        /** @var Order $order */
        $order = $this->objectManager->get(Order::class)
            ->loadByIncrementId(self::ORDER_INCREMENT_ID);

        $entityData = $this->getOrderData($order);
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $this->markTestSkipped("Soap calls are more strict and contains attributes.");
            return;
        }

        $requestData = ['entity' => ['entity_id' => $entityData['entity_id']]];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'save',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertGreaterThan(1, count($result));

        /** @var Order $actualOrder */
        $actualOrder = $this->objectManager->get(Order::class)->load($order->getId());
        $this->assertEquals(
            $order->getData(OrderInterface::INCREMENT_ID),
            $actualOrder->getData(OrderInterface::INCREMENT_ID)
        );
    }

    /**
     * Check that changes to taxes extension attribute are saved
     *
     * @magentoApiDataFixture Magento/Sales/_files/order_with_tax.php
     */
    public function testUpdateTaxesExtensionAttributes()
    {
        /** @var Order $order */
        $order = $this->objectManager->get(Order::class)
            ->loadByIncrementId(self::ORDER_INCREMENT_ID);

        $getServiceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $order->getEntityId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'get',
            ],
        ];
        $postServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'save',
            ],
        ];
        $data = $this->_webApiCall($getServiceInfo, ['id' => $order->getEntityId()]);
        $data['extension_attributes']['taxes'][0]['code'] = 'US-NY-*-Rate';
        $data['extension_attributes']['taxes'][0]['percent'] = 5;
        $data['extension_attributes']['additional_itemized_taxes'][0]['tax_percent'] = 5;
        $data['extension_attributes']['additional_itemized_taxes'][0]['amount'] = 0.25;
        $data['extension_attributes']['additional_itemized_taxes'][0]['base_amount'] = 0.25;
        $data['extension_attributes']['additional_itemized_taxes'][0]['real_amount'] = 0.25;
        $data['items'][0]['extension_attributes']['itemized_taxes'] = [
            [
                'tax_percent' => 5,
                'tax_code' => 'US-NY-*-Rate',
                'amount' => 0.5,
                'base_amount' => 0.5,
                'real_amount' => 0.5,
                'real_base_amount' => 0.5,
                'taxable_item_type' => 'product',
            ]
        ];
        $this->_webApiCall($postServiceInfo, ['entity' => $data]);
        $result = $this->_webApiCall($getServiceInfo, ['id' => $order->getEntityId()]);
        $taxes = $result['extension_attributes']['taxes'];
        $this->assertCount(1, $taxes);
        $this->assertEquals('US-NY-*-Rate', $taxes[0]['code']);
        $this->assertEquals(5, $taxes[0]['percent']);
        // check that amount is not automatically calculated
        $this->assertEquals($data['extension_attributes']['taxes'][0]['amount'], $taxes[0]['amount']);

        $this->assertCount(1, $result['extension_attributes']['additional_itemized_taxes']);
        $shippingTaxItem = $result['extension_attributes']['additional_itemized_taxes'][0];
        $this->assertEquals('shipping', $shippingTaxItem['taxable_item_type']);
        $this->assertEquals(5, $shippingTaxItem['tax_percent']);
        $this->assertEquals(0.25, $shippingTaxItem['amount']);
        $this->assertEquals(0.25, $shippingTaxItem['base_amount']);
        $this->assertEquals(0.25, $shippingTaxItem['real_amount']);
        $this->assertCount(1, $result['items'][0]['extension_attributes']['itemized_taxes']);
        $orderItemTaxItem = $result['items'][0]['extension_attributes']['itemized_taxes'][0];
        $this->assertEquals('product', $orderItemTaxItem['taxable_item_type']);
        $this->assertEquals(5, $orderItemTaxItem['tax_percent']);
        $this->assertEquals(0.50, $orderItemTaxItem['amount']);
        $this->assertEquals(0.50, $orderItemTaxItem['base_amount']);
        $this->assertEquals(0.50, $orderItemTaxItem['real_amount']);
        $this->assertEquals($result['items'][0]['item_id'], $orderItemTaxItem['item_id']);

        // test when additional_itemized_taxes and taxes are not provided
        $data = $result;
        $additionalItemizedTaxes = $data['extension_attributes']['additional_itemized_taxes'];
        $orderItemAssociatedItemizedTaxes = $data['items'][0]['extension_attributes']['itemized_taxes'];
        unset($data['extension_attributes']['taxes']);
        unset($data['extension_attributes']['additional_itemized_taxes']);
        unset($data['items'][0]['extension_attributes']['additional_itemized_taxes']);
        $this->_webApiCall($postServiceInfo, ['entity' => $data]);
        $result = $this->_webApiCall($getServiceInfo, ['id' => $order->getEntityId()]);
        $this->assertEquals(
            $taxes,
            $result['extension_attributes']['taxes']
        );
        $this->assertEquals(
            $additionalItemizedTaxes,
            $result['extension_attributes']['additional_itemized_taxes']
        );
        $this->assertEquals(
            $orderItemAssociatedItemizedTaxes,
            $result['items'][0]['extension_attributes']['itemized_taxes']
        );
    }

    /**
     * Prepare order data for request
     *
     * @param Order $order
     * @return array
     */
    private function getOrderData(Order $order)
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $entityData = $order->getData();
            unset($entityData[OrderInterface::INCREMENT_ID]);
            $entityData[OrderInterface::STATE] = 'processing';
            $entityData[OrderInterface::STATUS] = 'processing';

            $orderData = $order->getData();
            $orderData['billing_address'] = $order->getBillingAddress()->getData();
            $orderData['billing_address']['street'] = ['Street'];

            $orderItems = [];
            foreach ($order->getItems() as $item) {
                $orderItems[] = $item->getData();
            }
            $orderData['items'] = $orderItems;

            $shippingAddress = $order->getShippingAddress()->getData();
            $orderData['extension_attributes']['shipping_assignments'] =
                [
                    [
                        'shipping' => [
                            'address' => $shippingAddress,
                            'method' => 'flatrate_flatrate'
                        ],
                        'items' => $order->getItems(),
                        'stock_id' => null,
                    ]
                ];
        } else {
            $orderData = [
                OrderInterface::ENTITY_ID => $order->getId(),
                OrderInterface::STATE => 'processing',
                OrderInterface::STATUS => 'processing'
            ];
        }
        return $orderData;
    }

    /**
     * Get data for invoice from order.
     *
     * @param Order $order
     * @return array
     */
    private function getDataForInvoice(Order $order): array
    {
        $orderItems = $order->getAllItems();
        return [
            'order_id' => $order->getId(),
            'base_currency_code' => null,
            'base_discount_amount' => null,
            'base_grand_total' => null,
            'base_discount_tax_compensation_amount' => null,
            'base_shipping_amount' => null,
            'base_shipping_discount_tax_compensation_amnt' => null,
            'base_shipping_incl_tax' => null,
            'base_shipping_tax_amount' => null,
            'base_subtotal' => null,
            'base_subtotal_incl_tax' => null,
            'base_tax_amount' => null,
            'base_total_refunded' => null,
            'base_to_global_rate' => null,
            'base_to_order_rate' => null,
            'billing_address_id' => null,
            'can_void_flag' => null,
            'created_at' => null,
            'discount_amount' => null,
            'discount_description' => null,
            'email_sent' => null,
            'entity_id' => null,
            'global_currency_code' => null,
            'grand_total' => null,
            'discount_tax_compensation_amount' => null,
            'increment_id' => null,
            'is_used_for_refund' => null,
            'order_currency_code' => null,
            'shipping_address_id' => null,
            'shipping_amount' => null,
            'shipping_discount_tax_compensation_amount' => null,
            'shipping_incl_tax' => null,
            'shipping_tax_amount' => null,
            'state' => null,
            'store_currency_code' => null,
            'store_id' => null,
            'store_to_base_rate' => null,
            'store_to_order_rate' => null,
            'subtotal' => null,
            'subtotal_incl_tax' => null,
            'tax_amount' => null,
            'total_qty' => '1',
            'transaction_id' => null,
            'updated_at' => null,
            'items' => [
                [
                    'orderItemId' => $orderItems[0]->getId(),
                    'qty' => 2,
                    'additionalData' => null,
                    'baseCost' => null,
                    'baseDiscountAmount' => null,
                    'baseDiscountTaxCompensationAmount' => null,
                    'basePrice' => null,
                    'basePriceInclTax' => null,
                    'baseRowTotal' => null,
                    'baseRowTotalInclTax' => null,
                    'baseTaxAmount' => null,
                    'description' => null,
                    'discountAmount' => null,
                    'discountTaxCompensationAmount' => null,
                    'name' => null,
                    'entity_id' => null,
                    'parentId' => null,
                    'price' => null,
                    'priceInclTax' => null,
                    'productId' => null,
                    'rowTotal' => null,
                    'rowTotalInclTax' => null,
                    'sku' => 'sku' . uniqid(),
                    'taxAmount' => null,
                ],
            ],
        ];
    }

    /**
     * Get data for creditmemo.
     *
     * @param Order $order
     * @return array
     */
    private function getDataForCreditmemo(Order $order): array
    {
        $orderItem = current($order->getAllItems());
        $items = [
            $orderItem->getId() => ['order_item_id' => $orderItem->getId(), 'qty' => $orderItem->getQtyInvoiced()],
        ];
        return [
            'adjustment' => null,
            'adjustment_negative' => null,
            'adjustment_positive' => null,
            'base_adjustment' => null,
            'base_adjustment_negative' => null,
            'base_adjustment_positive' => null,
            'base_currency_code' => null,
            'base_discount_amount' => null,
            'base_grand_total' => null,
            'base_discount_tax_compensation_amount' => null,
            'base_shipping_amount' => null,
            'base_shipping_discount_tax_compensation_amnt' => null,
            'base_shipping_incl_tax' => null,
            'base_shipping_tax_amount' => null,
            'base_subtotal' => null,
            'base_subtotal_incl_tax' => null,
            'base_tax_amount' => null,
            'base_to_global_rate' => null,
            'base_to_order_rate' => null,
            'billing_address_id' => null,
            'created_at' => null,
            'creditmemo_status' => null,
            'discount_amount' => null,
            'discount_description' => null,
            'email_sent' => null,
            'entity_id' => null,
            'global_currency_code' => null,
            'grand_total' => null,
            'discount_tax_compensation_amount' => null,
            'increment_id' => null,
            'invoice_id' => null,
            'order_currency_code' => null,
            'order_id' => $order->getId(),
            'shipping_address_id' => null,
            'shipping_amount' => null,
            'shipping_discount_tax_compensation_amount' => null,
            'shipping_incl_tax' => null,
            'shipping_tax_amount' => null,
            'state' => null,
            'store_currency_code' => null,
            'store_id' => null,
            'store_to_base_rate' => null,
            'store_to_order_rate' => null,
            'subtotal' => null,
            'subtotal_incl_tax' => null,
            'tax_amount' => null,
            'transaction_id' => null,
            'updated_at' => null,
            'items' => $items,
        ];
    }

    /**
     * Get data for shipment.
     *
     * @param Order $order
     * @return array
     */
    private function getDataForShipment(Order $order): array
    {
        $requestShipData = [
            'orderId' => $order->getId(),
            'items' => [],
            'comment' => [
                'comment' => 'Test Comment',
                'is_visible_on_front' => 1,
            ],
            'tracks' => [
                [
                    'track_number' => 'TEST_TRACK_0001',
                    'title' => 'Simple shipment track',
                    'carrier_code' => 'UPS'
                ]
            ]
        ];

        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $requestShipData['items'][] = [
                    'order_item_id' => $item->getItemId(),
                    'qty' => $item->getQtyOrdered(),
                ];
                break;
            }
        }

        return $requestShipData;
    }
}
