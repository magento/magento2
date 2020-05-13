<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Model\QuoteRepository;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->cleanExpiredQuotes = $objectManager->get(CleanExpiredQuotes::class);
        $this->quoteRepository = $objectManager->get(QuoteRepository::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
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
}
