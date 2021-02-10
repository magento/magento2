<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Product\Plugin;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Tests for update quote items plugin
 *
 * @magentoAppArea adminhtml
 */
class UpdateQuoteItemsTest extends TestCase
{
    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->getQuoteByReservedOrderId = $objectManager->get(GetQuoteByReservedOrderId::class);
        $this->productRepository = $objectManager->get(ProductRepository::class);
    }

    /**
     * Test to mark the quote as need to recollect and doesn't update the field "updated_at" after change product price
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @return void
     */
    public function testMarkQuoteRecollectAfterChangeProductPrice(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $this->assertNotNull($quote);
        $this->assertFalse((bool)$quote->getTriggerRecollect());
        $this->assertNotEmpty($quote->getItems());
        $quoteItem = current($quote->getItems());
        $product = $quoteItem->getProduct();

        $product->setPrice((float)$product->getPrice() + 10);
        $this->productRepository->save($product);

        /** @var AdapterInterface $connection */
        $connection = $quote->getResource()->getConnection();
        $select = $connection->select()
            ->from(
                $connection->getTableName('quote'),
                ['updated_at', 'trigger_recollect']
            )->where(
                "reserved_order_id = 'test_order_with_simple_product_without_address'"
            );

        $quoteRow = $connection->fetchRow($select);
        $this->assertNotEmpty($quoteRow);
        $this->assertTrue((bool)$quoteRow['trigger_recollect']);
        $this->assertEquals($quote->getUpdatedAt(), $quoteRow['updated_at']);
    }
}
