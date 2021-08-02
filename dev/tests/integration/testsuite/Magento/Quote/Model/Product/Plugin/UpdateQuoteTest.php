<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Product\Plugin;

use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Tests for update quote plugin.
 *
 * @magentoAppArea adminhtml
 */
class UpdateQuoteTest extends TestCase
{
    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
    }

    /**
     * Test to update the column trigger_recollect is 1 from quote table.
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @return void
     */
    public function testUpdateQuoteRecollectAfterChangeProductPrice(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $this->assertNotNull($quote);
        $this->assertFalse((bool)$quote->getTriggerRecollect());
        $this->assertNotEmpty($quote->getItems());
        $quoteItem = current($quote->getItems());
        $product = $quoteItem->getProduct();

        /** @var QuoteResource $quoteResource */
        $quoteResource = $quote->getResource();
        $quoteResource->markQuotesRecollect($product->getId());
        /** @var AdapterInterface $connection */
        $connection = $quoteResource->getConnection();
        $select = $connection->select()
            ->from(
                $quoteResource->getTable('quote'),
                ['trigger_recollect']
            )->where(
                "reserved_order_id = 'test_order_with_simple_product_without_address'"
            );

        $quoteRow = $connection->fetchRow($select);
        $this->assertNotEmpty($quoteRow);
        $this->assertTrue((bool)$quoteRow['trigger_recollect']);
    }
}
