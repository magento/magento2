<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

class OrderItemGetListTest extends WebapiAbstract
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
     * @magentoApiDataFixture Magento/Sales/_files/order_item_list.php
     */
    public function testGetList()
    {
        $expectedRowTotals = [112, 102, 92];
        /** @var \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = $this->objectManager->get(
            \Magento\Framework\Api\SortOrderBuilder::class
        );
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);
        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = $this->objectManager->create(\Magento\Framework\Api\FilterBuilder::class);

        $filter2 = $filterBuilder->setField('product_type')
            ->setValue('configurable')
            ->create();
        $filter3 = $filterBuilder->setField('base_price')
            ->setValue(110)
            ->setConditionType('gteq')
            ->create();
        $filter4 = $filterBuilder->setField('product_type')
            ->setValue('simple')
            ->setConditionType('neq')
            ->create();
        $sortOrder = $sortOrderBuilder->setField('row_total')
            ->setDirection('DESC')
            ->create();
        $searchCriteriaBuilder->addFilters([$filter4]);
        $searchCriteriaBuilder->addFilters([$filter2, $filter3]);
        $searchCriteriaBuilder->addSortOrder($sortOrder);

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
        $this->assertCount(3, $response['items']);
        $this->assertTrue(is_array($response['items'][0]));
        $rowTotals = [];

        foreach ($response['items'] as $item) {
            $rowTotals[] = $item['row_total'];
        }

        $this->assertEquals($expectedRowTotals, $rowTotals);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param array $response
     * @return void
     */
    protected function assertOrderItem(\Magento\Sales\Model\Order\Item $orderItem, array $response)
    {
        $this->assertEquals($orderItem->getId(), $response['item_id']);
        $this->assertEquals($orderItem->getOrderId(), $response['order_id']);
        $this->assertEquals($orderItem->getProductId(), $response['product_id']);
        $this->assertEquals($orderItem->getProductType(), $response['product_type']);
        $this->assertEquals($orderItem->getBasePrice(), $response['base_price']);
        $this->assertEquals($orderItem->getRowTotal(), $response['row_total']);
    }
}
