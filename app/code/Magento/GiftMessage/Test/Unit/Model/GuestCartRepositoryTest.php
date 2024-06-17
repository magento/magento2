<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Model\GuestItemRepository;
use Magento\GiftMessage\Model\ItemRepository;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestCartRepositoryTest extends TestCase
{
    /**
     * @var GuestItemRepository
     */
    protected $model;

    /**
     * @var ItemRepository|MockObject
     */
    protected $repositoryMock;

    /**
     * @var QuoteIdMaskFactory|MockObject
     */
    protected $quoteIdMaskFactoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->getMockBuilder(ItemRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteIdMaskFactoryMock = $this->getMockBuilder(QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();

        $this->model = new GuestItemRepository(
            $this->repositoryMock,
            $this->quoteIdMaskFactoryMock
        );
    }

    public function testGet()
    {
        $cartId = 'jIUggbo76';
        $quoteId = 123;
        $itemId = 234;

        /** @var QuoteIdMask|MockObject $quoteIdMaskMock */
        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteIdMaskFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($quoteIdMaskMock);

        $quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($cartId, 'masked_id')
            ->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->getMockForAbstractClass();

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($quoteId, $itemId)
            ->willReturn($messageMock);

        $this->assertEquals($messageMock, $this->model->get($cartId, $itemId));
    }

    public function testSave()
    {
        $cartId = 'jIUggbo76';
        $quoteId = 123;
        $itemId = 234;

        /** @var QuoteIdMask|MockObject $quoteIdMaskMock */
        $quoteIdMaskMock = $this->getMockBuilder(QuoteIdMask::class)
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteIdMaskFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($quoteIdMaskMock);

        $quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($cartId, 'masked_id')
            ->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->getMockBuilder(MessageInterface::class)
            ->getMockForAbstractClass();

        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteId, $messageMock, $itemId)
            ->willReturn(true);

        $this->assertTrue($this->model->save($cartId, $messageMock, $itemId));
    }
}
