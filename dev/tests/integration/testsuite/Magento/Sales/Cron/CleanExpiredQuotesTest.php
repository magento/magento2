<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Cron;

use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->cleanExpiredQuotes = $objectManager->create(
            CleanExpiredQuotes::class,
            ['batchSize' => 220]
        );
    }

    /**
     * Check if outdated quotes are deleted.
     *
     * @magentoConfigFixture default_store checkout/cart/delete_quote_after -365
     * @magentoDataFixture Magento/Sales/_files/quotes.php
     */
    public function testExecute()
    {
        //Initial count - should be equal to stores number.
        $this->assertQuotesCount(2);

        //Deleting expired quotes
        $this->cleanExpiredQuotes->execute();

        //Only 1 will be deleted for the store that has all of them expired by config (default_store)
        $this->assertQuotesCount(1);
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
        $this->assertQuotesCount(1000);

        //Deleting expired quotes
        $this->cleanExpiredQuotes->execute();

        //There should be no quotes anymore
        $this->assertQuotesCount(0);
    }

    /**
     * Optimized assert quotes count
     * Uses collection getSize in order to get quick result
     *
     * @param int $expected
     */
    private function assertQuotesCount(int $expected): void
    {
        $quoteCollection = Bootstrap::getObjectManager()->create(QuoteCollection::class);
        $totalCount = $quoteCollection->getSize();
        $this->assertEquals($expected, $totalCount);
    }
}
