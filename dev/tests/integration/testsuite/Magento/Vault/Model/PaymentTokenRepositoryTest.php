<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Class PaymentTokenRepositoryTest
 * @package Magento\Vault\Model
 * @magentoDbIsolation enabled
 */
class PaymentTokenRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  PaymentTokenRepository */
    private $repository;

    /** @var  SortOrderBuilder */
    private $sortOrderBuilder;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->repository = $objectManager->create(PaymentTokenRepository::class);
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
     * @magentoDataFixture Magento/Vault/_files/payment_tokens.php
     */
    public function testGetListWithMultipleFiltersAndSorting()
    {
        $filter1 = $this->filterBuilder
            ->setField('type')
            ->setValue('simple')
            ->create();
        $filter2 = $this->filterBuilder
            ->setField('is_active')
            ->setValue(1)
            ->create();
        $filter3 = $this->filterBuilder
            ->setField('expires_at')
            ->setConditionType('lt')
            ->setValue('2016-11-04 10:18:15')
            ->create();
        $sortOrder = $this->sortOrderBuilder
            ->setField('public_hash')
            ->setDirection('DESC')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $this->searchCriteriaBuilder->addFilters([$filter3]);
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        /** @var \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface $result */
        $result = $this->repository->getList($searchCriteria);
        $items = $result->getItems();
        $this->assertCount(2, $items);
        $this->assertEquals('second', array_shift($items)->getPaymentMethodCode());
        $this->assertEquals('first', array_shift($items)->getPaymentMethodCode());
    }
}
