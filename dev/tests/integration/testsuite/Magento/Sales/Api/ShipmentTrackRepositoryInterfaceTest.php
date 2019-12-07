<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\TestFramework\Helper\Bootstrap;

class ShipmentTrackRepositoryInterfaceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShipmentTrackRepositoryInterface
     */
    private $repository;

    protected function setUp()
    {
        $this->repository = Bootstrap::getObjectManager()->create(ShipmentTrackRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/shipment_tracks_for_search.php
     */
    public function testGetList()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        $filter1 = $filterBuilder->setField(ShipmentTrackInterface::TITLE)
            ->setValue('title 2')
            ->create();
        $filter2 = $filterBuilder->setField(ShipmentTrackInterface::DESCRIPTION)
            ->setValue('description 3')
            ->create();
        $filter3 = $filterBuilder->setField(ShipmentTrackInterface::TRACK_NUMBER)
            ->setValue('track number 4')
            ->create();
        $filter4 = $filterBuilder->setField(ShipmentTrackInterface::CARRIER_CODE)
            ->setValue('carrier code 5')
            ->create();
        $filter5 = $filterBuilder->setField(ShipmentTrackInterface::QTY)
            ->setConditionType('lt')
            ->setValue(5)
            ->create();
        $filter6 = $filterBuilder->setField(ShipmentTrackInterface::WEIGHT)
            ->setValue(1)
            ->create();

        /**@var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField(ShipmentTrackInterface::DESCRIPTION)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder =  Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);

        $searchCriteriaBuilder->addFilters([$filter1, $filter2, $filter3, $filter4]);
        $searchCriteriaBuilder->addFilters([$filter5]);
        $searchCriteriaBuilder->addFilters([$filter6]);
        $searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $searchCriteriaBuilder->setPageSize(2);
        $searchCriteriaBuilder->setCurrentPage(2);

        $searchCriteria = $searchCriteriaBuilder->create();

        $searchResult = $this->repository->getList($searchCriteria);

        $items = array_values($searchResult->getItems());
        $this->assertEquals(1, count($items));
        $this->assertEquals('title 2', $items[0][ShipmentTrackInterface::TITLE]);
    }
}
