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
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CreditmemoItemRepositoryInterfaceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CreditmemoItemRepositoryInterface
     */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = Bootstrap::getObjectManager()->create(CreditmemoItemRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/creditmemo_items_for_search.php
     */
    public function testGetList()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        $filter1 = $filterBuilder->setField(CreditmemoItemInterface::NAME)
            ->setValue('item 2')
            ->create();
        $filter2 = $filterBuilder->setField(CreditmemoItemInterface::NAME)
            ->setValue('item 3')
            ->create();
        $filter3 = $filterBuilder->setField(CreditmemoItemInterface::NAME)
            ->setValue('item 4')
            ->create();
        $filter4 = $filterBuilder->setField(CreditmemoItemInterface::NAME)
            ->setValue('item 5')
            ->create();
        $filter5 = $filterBuilder->setField(CreditmemoItemInterface::PRICE)
            ->setValue(45)
            ->setConditionType('lt')
            ->create();

        /**@var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField(CreditmemoItemInterface::NAME)
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
        $this->assertCount(1, $items);
        $this->assertEquals('item 2', $items[0][CreditmemoItemInterface::NAME]);
    }
}
