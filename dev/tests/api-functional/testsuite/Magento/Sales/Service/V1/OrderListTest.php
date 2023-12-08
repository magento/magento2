<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

class OrderListTest extends WebapiAbstract
{
    private const RESOURCE_PATH = '/V1/orders';

    private const SERVICE_READ_NAME = 'salesOrderRepositoryV1';

    private const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_list.php
     */
    public function testOrderList()
    {
        $searchData = $this->getSearchData();

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
        $this->assertArrayHasKey('items', $result);
        $this->assertCount(2, $result['items']);
        $this->assertArrayHasKey('search_criteria', $result);
        $this->assertEquals($searchData, $result['search_criteria']);
        $this->assertEquals('100000002', $result['items'][0]['increment_id']);
        $this->assertEquals('100000001', $result['items'][1]['increment_id']);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_list_with_tax.php
     */
    public function testOrderListExtensionAttributes()
    {
        $searchData = $this->getSearchData();

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

        $expectedTax = [
            'code' => 'US-NY-*-Rate 1',
            'type' => 'shipping'
        ];
        $appliedTaxes = $result['items'][0]['extension_attributes']['applied_taxes'];
        $this->assertEquals($expectedTax['code'], $appliedTaxes[0]['code']);
        $appliedTaxes = $result['items'][0]['extension_attributes']['item_applied_taxes'];
        $this->assertEquals($expectedTax['type'], $appliedTaxes[0]['type']);
        $this->assertNotEmpty($appliedTaxes[0]['applied_taxes']);
        $this->assertFalse($result['items'][0]['extension_attributes']['converting_from_quote']);
        $this->assertArrayHasKey('payment_additional_info', $result['items'][0]['extension_attributes']);
        $this->assertNotEmpty($result['items'][0]['extension_attributes']['payment_additional_info']);
        $taxes = $result['items'][0]['extension_attributes']['taxes'];
        $this->assertCount(1, $taxes);
        $this->assertEquals('US-NY-*-Rate 1', $taxes[0]['code']);
        $this->assertEquals(8.37, $taxes[0]['percent']);
        $this->assertCount(1, $result['items'][0]['extension_attributes']['additional_itemized_taxes']);
        $shippingTaxItem = $result['items'][0]['extension_attributes']['additional_itemized_taxes'][0];
        $this->assertEquals(8.37, $shippingTaxItem['tax_percent']);
        $this->assertEquals(45, $shippingTaxItem['amount']);
        $this->assertEquals(45, $shippingTaxItem['base_amount']);
        $this->assertEquals(45, $shippingTaxItem['real_amount']);
        $this->assertEquals('shipping', $shippingTaxItem['taxable_item_type']);
        $this->assertEquals('US-NY-*-Rate 1', $shippingTaxItem['tax_code']);
    }

    /**
     * Get search data for request.
     *
     * @return array
     */
    private function getSearchData() : array
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
            ->setField('status')
            ->setValue('processing')
            ->setConditionType('eq')
            ->create();
        $filter2 = $filterBuilder
            ->setField('state')
            ->setValue(\Magento\Sales\Model\Order::STATE_NEW)
            ->setConditionType('eq')
            ->create();
        $filter3 = $filterBuilder
            ->setField('increment_id')
            ->setValue('100000001')
            ->setConditionType('eq')
            ->create();
        $sortOrder = $sortOrderBuilder->setField('grand_total')
            ->setDirection('DESC')
            ->create();
        $searchCriteriaBuilder->addFilters([$filter1]);
        $searchCriteriaBuilder->addFilters([$filter2, $filter3]);
        $searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteriaBuilder->setPageSize(20);
        $searchData = $searchCriteriaBuilder->create()->__toArray();

        return $searchData;
    }
}
