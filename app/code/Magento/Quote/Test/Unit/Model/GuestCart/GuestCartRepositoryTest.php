<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use \Magento\Quote\Model\QuoteRepository;

class GuestCartRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuoteRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsDataFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->quoteFactoryMock = $this->getMock('Magento\Quote\Model\QuoteFactory', ['create'], [], '', false);
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            ['load', 'getId', 'save', 'delete', 'getCustomerId'],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->searchResultsDataFactory = $this->getMock(
            'Magento\Quote\Api\Data\CartSearchResultsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->quoteCollectionMock = $this->getMock('Magento\Quote\Model\Resource\Quote\Collection', [], [], '', false);
        $this->quoteIdMaskFactoryMock = $this->getMock('Magento\Quote\Model\QuoteIdMaskFactory', [], [], '', false);
        $this->quoteIdMaskMock = $this->getMock('Magento\Quote\Model\QuoteIdMask', [], [], '', false);

        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\GuestCart\GuestCartRepository',
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'searchResultsDataFactory' => $this->searchResultsDataFactory,
                'quoteCollection' => $this->quoteCollectionMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
    }

    public function testGet()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 15;

        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('getId')
            ->willReturn($cartId);

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);

        $this->assertEquals($this->quoteMock, $this->model->get($maskedCartId));
    }

    public function testSaveEdited()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 1;

        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())->method('getId')->willReturn($cartId);


        $this->quoteMock->expects($this->once())
            ->method('save');
        $this->quoteMock->expects($this->exactly(3))->method('getId')->willReturn($maskedCartId);
        $this->quoteMock->expects($this->exactly(1))->method('getCustomerId')->willReturn(2);

        $this->model->save($this->quoteMock);
    }

    public function testSaveNew()
    {
        $cartId = 1;

        $this->quoteIdMaskFactoryMock->expects($this->never())->method('create');
        $this->quoteMock->expects($this->at(0))->method('getId')->willReturn(false);

        $this->quoteMock->expects($this->once())
            ->method('save');
        $this->quoteMock->expects($this->at(1))->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->exactly(1))->method('getCustomerId')->willReturn(2);

        $this->model->save($this->quoteMock);
    }

    public function testDelete()
    {
        $maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $cartId = 1;

        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())->method('getId')->willReturn($cartId);

        $this->quoteMock->expects($this->once())
            ->method('delete');
        $this->quoteMock->expects($this->exactly(3))->method('getId')->willReturn($maskedCartId);
        $this->quoteMock->expects($this->exactly(1))->method('getCustomerId')->willReturn(2);

        $this->model->delete($this->quoteMock);
    }
}
