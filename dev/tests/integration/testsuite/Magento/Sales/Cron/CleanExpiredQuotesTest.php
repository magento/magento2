<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Sales\Cron\CleanExpiredQuotes class.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CleanExpiredQuotesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CleanExpiredQuotes
     */
    private $cleanExpiredQuotes;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->cleanExpiredQuotes = $objectManager->get(CleanExpiredQuotes::class);
        $this->quoteRepository = $objectManager->get(QuoteRepository::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
    }

    /**
     * Check if outdated quotes are deleted.
     *
     * @magentoConfigFixture default_store checkout/cart/delete_quote_after -365
     * @magentoDataFixture Magento/Sales/_files/quotes.php
     */
    public function testExecute()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        //Initial count - should be equal to stores number.
        $this->assertEquals(2, $this->quoteRepository->getList($searchCriteria)->getTotalCount());

        //Deleting expired quotes
        $this->cleanExpiredQuotes->execute();
        $totalCount = $this->quoteRepository->getList($searchCriteria)->getTotalCount();
        //Only 1 will be deleted for the store that has all of them expired by config (default_store)
        $this->assertEquals(
            1,
            $totalCount
        );
    }

    /**
     * Check if outdated quotes are deleted.
     *
     * @magentoConfigFixture default_store checkout/cart/delete_quote_after -365
     * @magentoDataFixture Magento/Sales/_files/quotes_big_amount.php
     */
    public function testExecuteWithBigAmountOfQuotes()
    {
        //Initial count - should be equal to 1000
        //Use collection getSize in order to get quick result
        $this->assertEquals(1000, $this->quoteCollectionFactory->create()->getSize());

        //Deleting expired quotes
        $this->cleanExpiredQuotes->execute();
        $totalCount = $this->quoteCollectionFactory->create()->getSize();

        //There should be no quotes anymore
        $this->assertEquals(0, $totalCount);
    }
}
