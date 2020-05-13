<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentListTest
 */
class ShipmentListTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/shipments';

    const SERVICE_READ_NAME = 'salesShipmentRepositoryV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/shipment_list.php
     */
    public function testShipmentList()
    {
        /** @var \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = $this->objectManager->get(
            \Magento\Framework\Api\SortOrderBuilder::class
        );
        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );

        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = $this->objectManager->create(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $filter1 = $filterBuilder
            ->setField('shipment_status')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();
        $filter2 = $filterBuilder
            ->setField('store_id')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();
        $filter3 = $filterBuilder
            ->setField('shipping_address_id')
            ->setValue(3)
            ->setConditionType('eq')
            ->create();
        $sortOrder = $sortOrderBuilder->setField('increment_id')
            ->setDirection('ASC')
            ->create();
        $searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $searchCriteriaBuilder->addFilters([$filter3]);
        $searchCriteriaBuilder->addSortOrder($sortOrder);

        $searchData = $searchCriteriaBuilder->create()->__toArray();

        $requestData = ['searchCriteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'getList',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        // TODO Test fails, due to the inability of the framework API to handle data collection
        $this->assertArrayHasKey('items', $result);
        $this->assertCount(2, $result['items']);
        $this->assertArrayHasKey('search_criteria', $result);
        $this->assertEquals($searchData, $result['search_criteria']);
        $this->assertEquals('100000002', $result['items'][0]['increment_id']);
        $this->assertEquals('100000003', $result['items'][1]['increment_id']);
        $this->assertEquals(base64_encode('shipping_label_100000002'), $result['items'][0]['shipping_label']);
        $this->assertEquals(base64_encode('shipping_label_100000003'), $result['items'][1]['shipping_label']);
    }
}
