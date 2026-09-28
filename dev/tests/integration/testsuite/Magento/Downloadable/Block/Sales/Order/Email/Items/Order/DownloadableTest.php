<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Block\Sales\Order\Email\Items\Order;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Test order confirmation email for downloadable product.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class DownloadableTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var TransportBuilderMock
     */
    private $transportBuilder;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
        $this->quoteIdMaskFactory = $this->objectManager->get(QuoteIdMaskFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/quote_with_configurable_downloadable_product.php
     * @return void
     */
    public function testShouldSendDownloadableLinksInTheEmail(): void
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_configurable_downloadable', 'reserved_order_id');

        $checkoutSession = $this->objectManager->get(CheckoutSession::class);
        $checkoutSession->setQuoteId($quote->getId());

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->load($quote->getId(), 'quote_id');
        $cartId = $quoteIdMask->getMaskedId();

        /** @var GuestCartManagementInterface $cartManagement */
        $cartManagement = $this->objectManager->get(GuestCartManagementInterface::class);
        $cartManagement->placeOrder($cartId);
        $message = $this->transportBuilder->getSentMessage();
        $rawMessage = quoted_printable_decode($message->getBody()->bodyToString());
        $this->assertStringContainsString('Configurable Downloadable Product', $rawMessage);
        $this->assertStringContainsString('SKU: downloadable-product', $rawMessage);
        $this->assertStringContainsString('Downloadable Product Link', $rawMessage);
        $this->assertStringContainsString('/downloadable/download/link/id/', $rawMessage);
    }
}
