<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class OrderStatusHistoryRepositoryInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $repository;

    protected function setUp()
    {
        $this->repository = Bootstrap::getObjectManager()->create(OrderStatusHistoryRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_status_history_for_search.php
     */
    public function testGetList()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        $filter1 = $filterBuilder->setField(OrderStatusHistoryInterface::COMMENT)
            ->setValue('comment 2')
            ->create();
        $filter2 = $filterBuilder->setField(OrderStatusHistoryInterface::COMMENT)
            ->setValue('comment 3')
            ->create();
        $filter3 = $filterBuilder->setField(OrderStatusHistoryInterface::COMMENT)
            ->setValue('comment 4')
            ->create();
        $filter4 = $filterBuilder->setField(OrderStatusHistoryInterface::COMMENT)
            ->setValue('comment 5')
            ->create();
        $filter5 = $filterBuilder->setField(OrderStatusHistoryInterface::IS_VISIBLE_ON_FRONT)
            ->setValue(1)
            ->create();

        /**@var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField(OrderStatusHistoryInterface::COMMENT)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder =  Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);

        $searchCriteriaBuilder->addFilters([$filter1, $filter2, $filter3, $filter4]);
        $searchCriteriaBuilder->addFilters([$filter5]);
        $searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $searchCriteriaBuilder->setPageSize(2);
        $searchCriteriaBuilder->setCurrentPage(2);

        $searchCriteria = $searchCriteriaBuilder->create();

        $searchResult = $this->repository->getList($searchCriteria);

        $items = array_values($searchResult->getItems());
        $this->assertEquals(1, count($items));
        $this->assertEquals('comment 2', $items[0][OrderStatusHistoryInterface::COMMENT]);
    }
}
