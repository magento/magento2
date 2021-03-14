<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProduct\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Item;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test for get order item
 */
class OrderItemRepositoryTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/orders/items';

    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'salesOrderItemRepositoryV1';

    const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->orderFactory = $this->objectManager->get(OrderFactory::class);
    }

    /**
     * Test get order item
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/order_with_one_configurable_product_for_customer.php
     *
     * @return void
     */
    public function testGet(): void
    {
        $order = $this->orderFactory->create();
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);
        $orderItem = current($order->getItems());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $orderItem->getId(),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'get',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['id' => $orderItem->getId()]);

        $this->assertIsArray($response);
        $this->assertOrderItem($orderItem, $response);
    }

    /**
     * Test get order item list
     *
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/order_with_one_configurable_product_for_customer.php
     *
     * @return void
     */
    public function testGetList(): void
    {
        $order = $this->orderFactory->create();
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);

        /** @var $searchCriteriaBuilder  SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        /** @var $filterBuilder  FilterBuilder */
        $filterBuilder = $this->objectManager->create(FilterBuilder::class);

        $searchCriteriaBuilder->addFilters(
            [
                $filterBuilder->setField('order_id')
                    ->setValue($order->getId())
                    ->create(),
            ]
        );

        $requestData = ['searchCriteria' => $searchCriteriaBuilder->create()->__toArray()];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('items', $response);
        $this->assertCount(2, $response['items']);
        $this->assertIsArray($response['items'][0]);
        $this->assertOrderItem(current($order->getItems()), $response['items'][0]);
    }

    /**
     * Order item assert
     *
     * @param Item $orderItem
     * @param array $response
     * @return void
     */
    private function assertOrderItem(Item $orderItem, array $response): void
    {
        $expected = $orderItem->getBuyRequest()->getSuperAttribute();

        $this->assertEquals($orderItem->getProductId(), $this->productResource->getIdBySku($response['sku']));
        $this->assertArrayHasKey('product_option', $response);
        $this->assertArrayHasKey('extension_attributes', $response['product_option']);
        $this->assertArrayHasKey('configurable_item_options', $response['product_option']['extension_attributes']);

        $this->assertArrayHasKey('sku', $response);
        $this->assertArrayHasKey('product_id', $response);
        $this->assertEquals($response['product_id'], $this->productResource->getIdBySku($response['sku']));

        $actualOptions = $response['product_option']['extension_attributes']['configurable_item_options'];

        $this->assertIsArray($actualOptions);
        $this->assertIsArray($actualOptions[0]);
        $this->assertArrayHasKey('option_id', $actualOptions[0]);
        $this->assertArrayHasKey('option_value', $actualOptions[0]);

        $this->assertEquals(key($expected), $actualOptions[0]['option_id']);
        $this->assertEquals(current($expected), $actualOptions[0]['option_value']);
    }
}
