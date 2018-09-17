<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model;

// @codingStandardsIgnoreFile

use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Model\ItemRepository;
use Magento\GiftMessage\Model\GuestItemRepository;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestCartRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GuestItemRepository
     */
    protected $model;

    /**
     * @var ItemRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var QuoteIdMaskFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    protected function setUp()
    {
        $this->repositoryMock = $this->getMockBuilder('Magento\GiftMessage\Model\ItemRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteIdMaskFactoryMock = $this->getMockBuilder('Magento\Quote\Model\QuoteIdMaskFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
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

        /** @var QuoteIdMask|\PHPUnit_Framework_MockObject_MockObject $quoteIdMaskMock */
        $quoteIdMaskMock = $this->getMockBuilder('Magento\Quote\Model\QuoteIdMask')
            ->setMethods(['getQuoteId', 'load'])
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

        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $messageMock */
        $messageMock = $this->getMockBuilder('Magento\GiftMessage\Api\Data\MessageInterface')
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

        /** @var QuoteIdMask|\PHPUnit_Framework_MockObject_MockObject $quoteIdMaskMock */
        $quoteIdMaskMock = $this->getMockBuilder('Magento\Quote\Model\QuoteIdMask')
            ->setMethods(['getQuoteId', 'load'])
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

        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $messageMock */
        $messageMock = $this->getMockBuilder('Magento\GiftMessage\Api\Data\MessageInterface')
            ->getMockForAbstractClass();

        $this->repositoryMock->expects($this->once())
            ->method('save')
            ->with($quoteId, $messageMock, $itemId)
            ->willReturn(true);

        $this->assertTrue($this->model->save($cartId, $messageMock, $itemId));
    }
}
