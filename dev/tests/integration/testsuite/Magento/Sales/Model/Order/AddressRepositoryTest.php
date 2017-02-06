<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Class AddressRepositoryTest
 * @package Magento\Sales\Model\Order]
 * @magentoDbIsolation enabled
 */
class AddressRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressRepository */
    protected $repository;

    /** @var  SortOrderBuilder */
    private $sortOrderBuilder;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->repository = $objectManager->create(AddressRepository::class);
        $this->searchCriteriaBuilder = $objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->filterBuilder = $objectManager->get(
            \Magento\Framework\Api\FilterBuilder::class
        );
        $this->sortOrderBuilder = $objectManager->get(
            \Magento\Framework\Api\SortOrderBuilder::class
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/address_list.php
     */
    public function testGetListWithMultipleFiltersAndSorting()
    {
        $filter1 = $this->filterBuilder
            ->setField('postcode')
            ->setConditionType('neq')
            ->setValue('ZX0789A')
            ->create();
        $filter2 = $this->filterBuilder
            ->setField('address_type')
            ->setValue('billing')
            ->create();
        $filter3 = $this->filterBuilder
            ->setField('city')
            ->setValue('Ena4ka')
            ->create();
        $sortOrder = $this->sortOrderBuilder
            ->setField('region_id')
            ->setDirection('DESC')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1]);
        $this->searchCriteriaBuilder->addFilters([$filter2, $filter3]);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        /** @var \Magento\Sales\Api\Data\OrderAddressSearchResultInterface $result */
        $result = $this->repository->getList($searchCriteria);
        $items = $result->getItems();
        $this->assertCount(2, $items);
        $this->assertEquals('ZX0789', array_shift($items)->getPostcode());
        $this->assertEquals('47676', array_shift($items)->getPostcode());
    }
}
