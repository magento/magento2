<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Test\Api;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ShipOrderTest extends WebapiAbstract
{
    const SERVICE_READ_NAME = 'salesShipOrderV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $this->shipmentRepository = $this->objectManager->get(ShipmentRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoApiDataFixture Magento/Checkout/_files/simple_product.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_simple_on_multi_source.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/create_quote_on_eu_website.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_simple_product.php
     */
    public function testShipOrderWithSimpleProduct()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', 'created_order_for_test')
            ->create();
        /** @var OrderInterface $order */
        $createdOrder = current($this->orderRepository->getList($searchCriteria)->getItems());

        $requestData = [
            'arguments' => [
                'extension_attributes' => [
                    'source_code' => 'eu-2'
                ]
            ],
            'orderId' => $createdOrder->getId(),
        ];
        $result = $this->_webApiCall(
            $this->buildServiceInfo((int)$createdOrder->getEntityId()),
            $requestData
        );

        $this->assertNotEmpty($result);

        try {
            $shipping = $this->shipmentRepository->get($result);
            $this->assertNotNull($shipping->getEntityId());
            $this->assertEquals('3.0000', $shipping->getTotalQty());
            $shipmentExtension = $shipping->getExtensionAttributes();
            $this->assertEquals('eu-2', $shipmentExtension->getSourceCode());
        } catch (NoSuchEntityException $e) {
            $this->fail('Failed asserting that Shipment was created');
        }

        /** @var OrderInterface $order */
        $shippedOrder = current($this->orderRepository->getList($searchCriteria)->getItems());

        $this->assertNotEquals(
            $createdOrder->getStatus(),
            $shippedOrder->getStatus(),
            'Failed asserting that Order status was changed'
        );
    }

    /**
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/create_quote_on_us_website.php
     * @magentoApiDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_configurable_product.php
     */
    public function testShipOrderWithConfigurableProduct()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', 'created_order_for_test')
            ->create();
        /** @var OrderInterface $order */
        $createdOrder = current($this->orderRepository->getList($searchCriteria)->getItems());

        $requestData = [
            'arguments' => [
                'extension_attributes' => [
                    'source_code' => 'us-1'
                ]
            ],
            'orderId' => $createdOrder->getId(),
        ];
        $result = $this->_webApiCall(
            $this->buildServiceInfo((int)$createdOrder->getEntityId()),
            $requestData
        );

        $this->assertNotEmpty($result);

        try {
            $shipping = $this->shipmentRepository->get($result);
            $this->assertNotNull($shipping->getEntityId());
            $this->assertEquals('3.0000', $shipping->getTotalQty());
            $shipmentExtension = $shipping->getExtensionAttributes();
            $this->assertEquals('us-1', $shipmentExtension->getSourceCode());
        } catch (NoSuchEntityException $e) {
            $this->fail('Failed asserting that Shipment was created');
        }

        /** @var OrderInterface $order */
        $shippedOrder = current($this->orderRepository->getList($searchCriteria)->getItems());

        $this->assertNotEquals(
            $createdOrder->getStatus(),
            $shippedOrder->getStatus(),
            'Failed asserting that Order status was changed'
        );
    }

    /**
     * Build request body
     *
     * @param int $orderId
     * @return array
     */
    private function buildServiceInfo(int $orderId): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $orderId . '/ship',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'execute',
            ],
        ];

        return $serviceInfo;
    }
}
