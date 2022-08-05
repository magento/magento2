<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

class QuoteIdToMaskedQuoteIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    protected function setUp(): void
    {
        $objectManager = BootstrapHelper::getObjectManager();
        $this->quoteIdToMaskedQuoteId = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
        $this->quoteFactory = $objectManager->create(QuoteFactory::class);
        $this->quoteResource = $objectManager->create(QuoteResource::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testMaskedQuoteId()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test01', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int) $quote->getId());

        self::assertNotEmpty($maskedQuoteId);
    }

    public function testMaskedQuoteIdWithNonExistentQuoteId()
    {
        self::expectException(NoSuchEntityException::class);

        $this->quoteIdToMaskedQuoteId->execute(0);
    }
}
