<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Plugin;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\SalesRule\Model\Plugin\QuoteItemCollection;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\SalesRule\Model\ReadRequestFlag;
use Magento\Quote\Model\Quote;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Test for QuoteItemCollection plugin
 */
class QuoteItemCollectionTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ReadRequestFlag|MockObject
     */
    private $readRequestFlag;

    /**
     * @var QuoteItemCollectionPlugin
     */
    private $plugin;

    /**
     * @var QuoteItemCollection|MockObject
     */
    private $quoteItemCollection;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->readRequestFlag = $this->createMock(ReadRequestFlag::class);
        $this->plugin = new QuoteItemCollection($this->readRequestFlag);
        $this->quoteItemCollection = $this->getMockBuilder(Collection::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->quote = $this->createPartialMockWithReflection(
            Quote::class,
            ['getTriggerRecollect']
        );
    }

    /**
     * Test beforeSetQuote when triggerRecollect is 1 and request is GET/Query
     */
    public function testBeforeSetQuoteWithTriggerRecollectAndGetRequest(): void
    {
        // Set up the quote mock to return 1 for getTriggerRecollect
        $this->quote->expects($this->once())
            ->method('getTriggerRecollect')
            ->willReturn(1);

        // Set up the request type flag to indicate this is a GET request
        $this->readRequestFlag->expects($this->once())
            ->method('isReadRequest')
            ->willReturn(true);

        // Expect setIsReadRequest to be called with false
        $this->readRequestFlag->expects($this->once())
            ->method('setIsReadRequest')
            ->with(false);

        // Execute the method
        $this->plugin->beforeSetQuote($this->quoteItemCollection, $this->quote);
    }

    /**
     * Test beforeSetQuote when triggerRecollect is 0 and request is GET/Query
     */
    public function testBeforeSetQuoteWithNoTriggerRecollectAndGetRequest(): void
    {
        // Set up the quote mock to return 0 for getTriggerRecollect
        $this->quote->expects($this->once())
            ->method('getTriggerRecollect')
            ->willReturn(0);

        // Expect setIsReadRequest NOT to be called
        $this->readRequestFlag->expects($this->never())
            ->method('setIsReadRequest');

        // Execute the method
        $this->plugin->beforeSetQuote($this->quoteItemCollection, $this->quote);
    }

    /**
     * Test beforeSetQuote when triggerRecollect is 1 but request is not GET/Query
     */
    public function testBeforeSetQuoteWithTriggerRecollectAndNonGetRequest(): void
    {
        // Set up the quote mock to return 1 for getTriggerRecollect
        $this->quote->expects($this->once())
            ->method('getTriggerRecollect')
            ->willReturn(1);

        // Set up the request type registry to indicate this is NOT a GET request
        $this->readRequestFlag->expects($this->once())
            ->method('isReadRequest')
            ->willReturn(false);

        // Expect setIsReadRequest NOT to be called
        $this->readRequestFlag->expects($this->never())
            ->method('setIsReadRequest');

        // Execute the method
        $this->plugin->beforeSetQuote($this->quoteItemCollection, $this->quote);
    }
}
