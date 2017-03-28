<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Payment;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Class RepositoryTest
 * @package Magento\Sales\Model\Order\Payment\
 * @magentoDbIsolation enabled
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var Repository */
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
        $this->repository = $objectManager->create(Repository::class);
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
     * @magentoDataFixture Magento/Sales/_files/order_payment_list.php
     */
    public function testGetListWithMultipleFiltersAndSorting()
    {
        $filter1 = $this->filterBuilder
            ->setField('cc_ss_start_year')
            ->setValue('2014')
            ->create();
        $filter2 = $this->filterBuilder
            ->setField('cc_exp_month')
            ->setValue('09')
            ->create();
        $filter3 = $this->filterBuilder
            ->setField('method')
            ->setValue('checkmo')
            ->create();
        $sortOrder = $this->sortOrderBuilder
            ->setField('cc_exp_month')
            ->setDirection('DESC')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $this->searchCriteriaBuilder->addFilters([$filter3]);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        /** @var \Magento\Sales\Api\Data\OrderPaymentSearchResultInterface $result */
        $result = $this->repository->getList($searchCriteria);
        $items = $result->getItems();
        $this->assertCount(2, $items);
        $this->assertEquals('456', array_shift($items)->getCcLast4());
        $this->assertEquals('123', array_shift($items)->getCcLast4());
    }
}
