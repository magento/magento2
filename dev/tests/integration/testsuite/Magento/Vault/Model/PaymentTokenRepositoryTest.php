<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * PaymentTokenRepositoryTest contains tests for Vault token repository
 *
 * @magentoDbIsolation enabled
 */
class PaymentTokenRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentTokenRepository
     */
    private $repository;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->repository = $this->objectManager->create(PaymentTokenRepository::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $this->filterBuilder = $this->objectManager->get(FilterBuilder::class);
        $this->sortOrderBuilder = $this->objectManager->get(SortOrderBuilder::class);
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

    /**
     * @covers \Magento\Vault\Model\PaymentTokenRepository::delete
     * @magentoDataFixture Magento/Vault/_files/token.php
     */
    public function testDelete()
    {
        /** @var PaymentTokenManagement $tokenManagement */
        $tokenManagement = $this->objectManager->get(PaymentTokenManagement::class);

        $token = $tokenManagement->getByPublicHash('public_hash', 0);

        /** @var PaymentTokenRepository $tokenRepository */
        $tokenRepository = $this->objectManager->get(PaymentTokenRepository::class);
        $tokenRepository->delete($token);

        $deletedToken = $tokenRepository->getById($token->getEntityId());

        static::assertEquals('public_hash', $deletedToken->getPublicHash());
        static::assertFalse($deletedToken->getIsActive());
        static::assertFalse($deletedToken->getIsVisible());
    }
}
