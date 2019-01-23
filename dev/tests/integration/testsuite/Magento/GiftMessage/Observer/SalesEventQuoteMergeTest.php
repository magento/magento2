<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Observer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\Quote;

class SalesEventQuoteMergeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea frontend
     */
    public function testQuoteMerge()
    {
        $giftMessageId = 6;
        $objectManager = Bootstrap::getObjectManager();
        $eventManager = $objectManager->get(ManagerInterface::class);
        /** @var Quote $sourceQuote */
        $sourceQuote = $objectManager->create(QuoteFactory::class)->create();
        $targetQuote = clone($sourceQuote);
        $sourceQuote->setGiftMessageId($giftMessageId);

        $eventManager->dispatch(
            'sales_quote_merge_after',
            [
                'quote' => $targetQuote,
                'source' => $sourceQuote
            ]
        );

        self::assertEquals($giftMessageId, $targetQuote->getGiftMessageId());
    }
}
