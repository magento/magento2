<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class OrderItemRepositoryTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/orders/items';

    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'salesOrderItemRepositoryV1';

    const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/order_item_with_configurable_and_options.php
     */
    public function testGet()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);
        $orderItem = current($order->getItems());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $orderItem->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/order_item_with_configurable_and_options.php
     */
    public function testGetList()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);

        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = $this->objectManager->create(\Magento\Framework\Api\FilterBuilder::class);

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
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
        $this->assertCount(1, $response['items']);
        $this->assertIsArray($response['items'][0]);
        $this->assertOrderItem(current($order->getItems()), $response['items'][0]);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param array $response
     * @return void
     */
    protected function assertOrderItem(\Magento\Sales\Model\Order\Item $orderItem, array $response)
    {
        $expected = $orderItem->getBuyRequest()->getSuperAttribute();

        $this->assertArrayHasKey('product_option', $response);
        $this->assertArrayHasKey('extension_attributes', $response['product_option']);
        $this->assertArrayHasKey('configurable_item_options', $response['product_option']['extension_attributes']);

        $actualOptions = $response['product_option']['extension_attributes']['configurable_item_options'];

        $this->assertIsArray($actualOptions);
        $this->assertIsArray($actualOptions[0]);
        $this->assertArrayHasKey('option_id', $actualOptions[0]);
        $this->assertArrayHasKey('option_value', $actualOptions[0]);

        $this->assertEquals(key($expected), $actualOptions[0]['option_id']);
        $this->assertEquals(current($expected), $actualOptions[0]['option_value']);
    }
}
