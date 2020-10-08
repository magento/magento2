<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\GiftMessage\Model\Plugin\MergeQuoteItems;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\GiftMessage\Model\Plugin\MergeQuoteItems
 */
class MergeQuoteItemsTest extends TestCase
{
    private const STUB_GIFT_MESSAGE = 'message';

    /**
     * @var MergeQuoteItems
     */
    private $plugin;

    /**
     * @var Processor|MockObject
     */
    private $processorMock;

    /**
     * @var Item|MockObject
     */
    private $resultMock;

    /**
     * @var Item|MockObject
     */
    private $sourceMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->plugin = (new ObjectManagerHelper($this))->getObject(MergeQuoteItems::class);
        $this->processorMock = $this->createMock(Processor::class);
        $this->resultMock = $this->getMockBuilder(Item::class)
            ->addMethods(['setGiftMessageId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sourceMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getGiftMessageId'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test case when a source item has a Gift message.
     */
    public function testAfterMergeExpectsSetGiftMessageIdCalled(): void
    {
        $this->sourceMock->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn(self::STUB_GIFT_MESSAGE);
        $this->resultMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(self::STUB_GIFT_MESSAGE);

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterMerge($this->processorMock, $this->resultMock, $this->sourceMock)
        );
    }

    /**
     * Test case when a source item doesn't have a Gift message.
     */
    public function testAfterMergeWithoutGiftMessageId(): void
    {
        $this->sourceMock->expects($this->once())->method('getGiftMessageId')->willReturn(null);
        $this->resultMock->expects($this->never())->method('setGiftMessageId');

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterMerge($this->processorMock, $this->resultMock, $this->sourceMock)
        );
    }
}
