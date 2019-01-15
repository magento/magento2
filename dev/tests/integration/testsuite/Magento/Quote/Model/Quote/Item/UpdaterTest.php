<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->updater = $this->objectManager->create(Updater::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_custom_price.php
     * @return void
     */
    public function testUpdate(): void
    {
        /** @var CartItemRepositoryInterface $quoteItemRepository */
        $quoteItemRepository = $this->objectManager->create(CartItemRepositoryInterface::class);
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quoteId = $quote->load('test01', 'reserved_order_id')->getId();
        /** @var CartItemInterface[] $quoteItems */
        $quoteItems = $quoteItemRepository->getList($quoteId);
        /** @var CartItemInterface $actualQuoteItem */
        $actualQuoteItem = array_pop($quoteItems);
        $this->assertInstanceOf(CartItemInterface::class, $actualQuoteItem);

        $this->updater->update($actualQuoteItem, ['qty' => 1]);

        $this->assertNull(
            $actualQuoteItem->getCustomPrice(),
            'Item custom price has to be null'
        );
    }
}
