<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Api\FilterBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\Data\CartInterface;

class QuoteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetList()
    {
        $searchCriteria = $this->getSearchCriteria('test01');
            /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
        $quoteRepository = Bootstrap::getObjectManager()->create(CartRepositoryInterface::class);
        $searchResult = $quoteRepository->getList($searchCriteria);
        $this->performAssertions($searchResult);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetListDoubleCall()
    {
        $searchCriteria1 = $this->getSearchCriteria('test01');
        $searchCriteria2 = $this->getSearchCriteria('test02');

        /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
        $quoteRepository = Bootstrap::getObjectManager()->create(CartRepositoryInterface::class);
        $searchResult = $quoteRepository->getList($searchCriteria1);
        $this->performAssertions($searchResult);
        $searchResult = $quoteRepository->getList($searchCriteria2);
        $items = $searchResult->getItems();
        $this->assertEmpty($items);
    }

    /**
     * @param string $filterValue
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function getSearchCriteria($filterValue)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $filters = [];
        $filters[] = $filterBuilder
            ->setField('reserved_order_id')
            ->setConditionType('=')
            ->setValue($filterValue)
            ->create();
        $searchCriteriaBuilder->addFilters($filters);

        return $searchCriteriaBuilder->create();
    }

    /**
     * @param object $searchResult
     */
    protected function performAssertions($searchResult)
    {
        $expectedExtensionAttributes = [
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'admin@example.com'
        ];

        $items = $searchResult->getItems();
        /** @var \Magento\Quote\Api\Data\CartInterface $actualQuote */
        $actualQuote = array_pop($items);
        $this->assertInstanceOf(CartInterface::class, $actualQuote);
        $this->assertEquals('test01', $actualQuote->getReservedOrderId());
        /** @var \Magento\User\Api\Data\UserInterface $testAttribute */
        $testAttribute = $actualQuote->getExtensionAttributes()->getQuoteTestAttribute();
        $this->assertEquals($expectedExtensionAttributes['firstname'], $testAttribute->getFirstName());
        $this->assertEquals($expectedExtensionAttributes['lastname'], $testAttribute->getLastName());
        $this->assertEquals($expectedExtensionAttributes['email'], $testAttribute->getEmail());
    }
}
