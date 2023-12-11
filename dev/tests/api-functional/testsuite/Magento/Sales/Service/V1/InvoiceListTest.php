<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\Framework\Api\SortOrderBuilder;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class InvoiceListTest
 */
class InvoiceListTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/invoices';

    const SERVICE_READ_NAME = 'salesInvoiceRepositoryV1';

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
     * @magentoApiDataFixture Magento/Sales/_files/invoice_list.php
     */
    public function testInvoiceList()
    {
        /** @var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = $this->objectManager->get(
            SortOrderBuilder::class
        );
        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );

        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = $this->objectManager->create(
            \Magento\Framework\Api\FilterBuilder::class
        );

        $stateFilter = $filterBuilder
            ->setField('state')
            ->setValue((string)\Magento\Sales\Model\Order\Creditmemo::STATE_OPEN)
            ->setConditionType('eq')
            ->create();
        $incrementFilter = $filterBuilder
            ->setField('increment_id')
            ->setValue('456')
            ->setConditionType('eq')
            ->create();
        $zeroStatusFilter = $filterBuilder
            ->setField('can_void_flag')
            ->setValue('0')
            ->setConditionType('eq')
            ->create();
        $sortOrder = $sortOrderBuilder
            ->setField('grand_total')
            ->setDirection('ASC')
            ->create();

        $searchCriteriaBuilder->addFilters([$stateFilter]);
        $searchCriteriaBuilder->addFilters([$incrementFilter, $zeroStatusFilter]);
        $searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteriaBuilder->setPageSize(20);
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
        $this->assertEquals('789', $result['items'][0]['increment_id']);
        $this->assertEquals('456', $result['items'][1]['increment_id']);
        $this->assertEquals($searchData, $result['search_criteria']);
    }
}
