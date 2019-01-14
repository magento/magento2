<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\Quote\Item\Updater;

/**
 * Tests \Magento\Quote\Model\Quote\Item\Updater
 */
class UpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Updater
     */
    private $updater;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->updater = Bootstrap::getObjectManager()->create(Updater::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_custom_price.php
     * @return void
     */
    public function testUpdate()
    {
        /** @var CartItemRepositoryInterface $quoteItemRepository */
        $quoteItemRepository = Bootstrap::getObjectManager()->create(CartItemRepositoryInterface::class);
        /** @var Quote $quote */
        $quote = Bootstrap::getObjectManager()->create(Quote::class);
        $quoteId = $quote->load('test01', 'reserved_order_id')->getId();
        /** @var CartItemInterface[] $quoteItems */
        $quoteItems = $quoteItemRepository->getList($quoteId);
        /** @var CartItemInterface $actualQuoteItem */
        $actualQuoteItem = array_pop($quoteItems);
        $this->assertInstanceOf(CartItemInterface::class, $actualQuoteItem);

        $info = [
            'qty' => 1,
        ];
        $this->updater->update($actualQuoteItem, $info);

        $this->assertNull(
            $actualQuoteItem->getCustomPrice(),
            'Item custom price has to be null'
        );
    }
}
