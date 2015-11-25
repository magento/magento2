<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/order_item_with_bundle_and_options.php
     */
    public function testGet()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
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

        $this->assertTrue(is_array($response));
        $this->assertOrderItem($orderItem, $response);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/order_item_with_bundle_and_options.php
     */
    public function testGetList()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);

        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = $this->objectManager->create('Magento\Framework\Api\FilterBuilder');

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

        $this->assertTrue(is_array($response));
        $this->assertArrayHasKey('items', $response);
        $this->assertCount(1, $response['items']);
        $this->assertTrue(is_array($response['items'][0]));
        $this->assertOrderItem(current($order->getItems()), $response['items'][0]);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param array $response
     * @return void
     */
    protected function assertOrderItem(\Magento\Sales\Model\Order\Item $orderItem, array $response)
    {
        $bundleOption = $orderItem->getBuyRequest()->getBundleOption();
        $bundleOptionQty = $orderItem->getBuyRequest()->getBundleOptionQty();

        $this->assertArrayHasKey('product_option', $response);
        $this->assertArrayHasKey('extension_attributes', $response['product_option']);
        $this->assertArrayHasKey('bundle_options', $response['product_option']['extension_attributes']);

        $actualOptions = $response['product_option']['extension_attributes']['bundle_options'];

        $this->assertEquals(array_keys($bundleOption), array_column($actualOptions, 'option_id'));
        $this->assertEquals($bundleOptionQty, array_column($actualOptions, 'option_qty', 'option_id'));

        foreach ($actualOptions as $option) {
            $expectedSelections = is_array($bundleOption[$option['option_id']])
                ? $bundleOption[$option['option_id']]
                : [$bundleOption[$option['option_id']]];
            $this->assertEquals($expectedSelections, $option['option_selections']);
        }
    }
}
