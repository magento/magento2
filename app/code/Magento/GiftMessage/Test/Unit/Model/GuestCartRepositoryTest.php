<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Model\GuestItemRepository;
use Magento\GiftMessage\Model\ItemRepository;
use Magento\Quote\Model\GuestCart\GetGuestCart;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestCartRepositoryTest extends TestCase
{
    use MockCreationTrait;

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

    /**
     * @var GetGuestCart|MockObject
     */
    private $getGuestCartMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(ItemRepository::class);

        $this->quoteIdMaskFactoryMock = $this->getMockBuilder(QuoteIdMaskFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->getGuestCartMock = $this->createMock(GetGuestCart::class);
        $this->getGuestCartMock->method('execute')->willReturn($this->createMock(Quote::class));

        $this->model = new GuestItemRepository(
            $this->repositoryMock,
            $this->quoteIdMaskFactoryMock,
            $this->getGuestCartMock
        );
    }

    public function testGet()
    {
        $cartId = 'jIUggbo76';
        $quoteId = 123;
        $itemId = 234;

        /** @var QuoteIdMask|MockObject $quoteIdMaskMock */
        $quoteIdMaskMock = $this->createPartialMockWithReflection(
            QuoteIdMask::class,
            ['getQuoteId', 'load']
        );

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
        $messageMock = $this->createMock(MessageInterface::class);

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
        $quoteIdMaskMock = $this->createPartialMockWithReflection(
            QuoteIdMask::class,
            ['getQuoteId', 'load']
        );

        $this->quoteIdMaskFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($quoteIdMaskMock);

        $quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($cartId, 'masked_id')
            ->willReturnSelf();
        $quoteIdMaskMock->method('getQuoteId')
            ->willReturn($quoteId);

        /** @var MessageInterface|MockObject $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);

        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteId, $messageMock, $itemId)
            ->willReturn(true);

        $this->assertTrue($this->model->save($cartId, $messageMock, $itemId));
    }
}
