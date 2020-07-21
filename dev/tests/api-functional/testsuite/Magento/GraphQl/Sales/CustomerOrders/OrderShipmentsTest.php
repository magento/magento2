<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

class OrderShipmentsTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $getCustomerAuthHeader;

    private $orderRepository;

    private $registry;

    protected function setUp(): void
    {
        $this->getCustomerAuthHeader = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
    }

    protected function tearDown(): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        /** @var $order \Magento\Sales\Model\Order */
        $orderCollection = Bootstrap::getObjectManager()->create(OrderCollection::class);
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/customer_order_with_simple_shipment.php
     */
    public function testGetOrderShipment()
    {
        $query = $this->getQuery('100000555');
        $authHeader = $this->getCustomerAuthHeader->execute('customer_uk_address@test.com', 'password');
        $orderModel = $this->fetchOrderModel('100000555');

        $result = $this->graphQlQuery($query, [], '', $authHeader);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(1, $result['customer']['orders']['items']);

        $order = $result['customer']['orders']['items'][0];
        $this->assertEquals('Flat Rate', $order['carrier']);
        $this->assertEquals('Flat Rate - Fixed', $order['shipping_method']);
        $this->assertArrayHasKey('shipments', $order);
        /** @var Shipment $orderShipmentModel */
        $orderShipmentModel = $orderModel->getShipmentsCollection()->getFirstItem();
        $shipment = $order['shipments'][0];
        $this->assertEquals(base64_encode($orderShipmentModel->getIncrementId()), $shipment['id']);
        $this->assertEquals($orderShipmentModel->getIncrementId(), $shipment['number']);
        //Check Tracking
        $this->assertCount(1, $shipment['tracking']);
        $tracking = $shipment['tracking'][0];
        $this->assertEquals('ups', $tracking['carrier']);
        $this->assertEquals('United Parcel Service', $tracking['title']);
        $this->assertEquals('1234567890', $tracking['number']);
        //Check Items
        $this->assertCount(2, $shipment['items']);
        foreach ($orderShipmentModel->getItems() as $expectedItem) {
            $sku = $expectedItem->getSku();
            $findItem = array_filter($shipment['items'], function ($item) use ($sku) {
                return $item['product_sku'] === $sku;
            });
            $this->assertCount(1, $findItem);
            $actualItem = reset($findItem);
            $expectedEncodedId = base64_encode($expectedItem->getEntityId());
            $this->assertEquals($expectedEncodedId, $actualItem['id']);
            $this->assertEquals($expectedItem->getSku(), $actualItem['product_sku']);
            $this->assertEquals($expectedItem->getName(), $actualItem['product_name']);
            $this->assertEquals($expectedItem->getPrice(), $actualItem['product_sale_price']['value']);
            $this->assertEquals('USD', $actualItem['product_sale_price']['currency']);
            $this->assertEquals('1', $actualItem['quantity_shipped']);
            //Check correct order_item
            $this->assertNotEmpty($actualItem['order_item']);
            $this->assertEquals($expectedItem->getSku(), $actualItem['order_item']['product_sku']);
        }
        //Check comments
        $this->assertCount(1, $shipment['comments']);
        $this->assertEquals('This comment is visible to the customer', $shipment['comments'][0]['message']);
        $this->assertNotEmpty($shipment['comments'][0]['timestamp']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/customer_order_with_multiple_shipments.php
     */
    public function testGetOrderShipmentsMultiple()
    {
        $query = $this->getQuery('100000555');
        $authHeader = $this->getCustomerAuthHeader->execute('customer_uk_address@test.com', 'password');

        $result = $this->graphQlQuery($query, [], '', $authHeader);
        $this->assertArrayNotHasKey('errors', $result);
        $order = $result['customer']['orders']['items'][0];
        $shipments = $order['shipments'];
        $this->assertCount(2, $shipments);
        $this->assertEquals('0000000098', $shipments[0]['number']);
        $this->assertCount(1, $shipments[0]['items']);
        $this->assertEquals('0000000099', $shipments[1]['number']);
        $this->assertCount(1, $shipments[1]['items']);
    }

    /**
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/customer_order_with_ups_shipping.php
     */
    public function testOrderShipmentWithUpsCarrier()
    {
        $query = $this->getQuery('100000001');
        $authHeader = $this->getCustomerAuthHeader->execute('customer@example.com', 'password');

        $result = $this->graphQlQuery($query, [], '', $authHeader);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertEquals('UPS Next Day Air', $result['customer']['orders']['items'][0]['shipping_method']);
        $this->assertEquals('United Parcel Service', $result['customer']['orders']['items'][0]['carrier']);

        $shipments = $result['customer']['orders']['items'][0]['shipments'];
        $this->assertCount(2, $shipments);
    }

    /**
     * @magentoConfigFixture default_store carriers/ups/active 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_two_dropdown_options.php
     */
    public function testOrderShipmentDifferentProductTypes()
    {
        //Place order with bundled product
        require __DIR__ . '/../_files/customer_place_order_with_bundle_product.php';
        $result = $this->graphQlQuery(
            $this->getQuery(),
            [],
            '',
            $this->getCustomerAuthHeader->execute('customer@example.com', 'password')
        );

        $this->assertArrayNotHasKey('errors', $result);

        $shipments = $result['customer']['orders']['items'][0]['shipments'];
    }


    private function getQuery(string $orderId = null)
    {
        $filter = $orderId ? "(filter:{number:{eq:\"$orderId\"}})" : "";
        $query = <<<QUERY
{
  customer $filter{
    orders {
      items {
        number
        status
        items {
          product_sku
        }
        carrier
        shipping_method
        shipments {
          id
          number
          tracking {
            title
            carrier
            number
          }
          items {
            id
            order_item {
              id
              product_sku
            }
            product_name
            product_sku
            product_sale_price {
              value
              currency
            }
            ... on BundleShipmentItem {
                bundle_options {
                    id
                    label
                    values {
                        id
                        product_name
                        product_sku
                        quantity
                        price {
                            value
                        }
                    }
                }
            }
            quantity_shipped
          }
          comments {
            timestamp
            message
          }
        }
      }
    }
  }
}
QUERY;

        return $query;
    }

    private function fetchOrderModel(string $orderNumber): Order
    {
        /** @var Order $order */
        $order = Bootstrap::getObjectManager()->get(Order::class);
        $order->loadByIncrementId($orderNumber);
        return $order;
    }
}
