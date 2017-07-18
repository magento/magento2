<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemDataFactoryMock;

    /** @var \Magento\Catalog\Model\CustomOptions\CustomOptionProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $customOptionProcessor;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $shippingAddressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsProcessorMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepositoryMock = $this->getMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->itemDataFactoryMock =
            $this->getMock(\Magento\Quote\Api\Data\CartItemInterfaceFactory::class, ['create'], [], '', false);
        $this->itemMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
        $this->quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $methods = ['getId', 'getSku', 'getQty', 'setData', '__wakeUp', 'getProduct', 'addProduct'];
        $this->quoteItemMock =
            $this->getMock(\Magento\Quote\Model\Quote\Item::class, $methods, [], '', false);
        $this->customOptionProcessor = $this->getMock(
            \Magento\Catalog\Model\CustomOptions\CustomOptionProcessor::class,
            [],
            [],
            '',
            false
        );
        $this->shippingAddressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['setCollectShippingRates'],
            [],
            '',
            false
        );

        $this->optionsProcessorMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor::class,
            [],
            [],
            '',
            false
        );

        $this->repository = new \Magento\Quote\Model\Quote\Item\Repository(
            $this->quoteRepositoryMock,
            $this->productRepositoryMock,
            $this->itemDataFactoryMock,
            ['custom_options' => $this->customOptionProcessor]
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->repository,
            'cartItemOptionsProcessor',
            $this->optionsProcessorMock
        );
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $cartId = 13;
        $itemId = 20;

        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getItems', 'setItems', 'collectTotals', 'getLastAddedItem'],
            [],
            '',
            false
        );

        $this->itemMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())->method('getItems')->willReturn([]);
        $quoteMock->expects($this->once())
            ->method('setItems')
            ->with([$this->itemMock])
            ->willReturnSelf();

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quoteMock->expects($this->once())->method('getLastAddedItem')->willReturn($itemId);

        $this->assertEquals($itemId, $this->repository->save($this->itemMock));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Cart 11 doesn't contain item  5
     */
    public function testDeleteWithInvalidQuoteItem()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->will($this->returnValue($this->quoteMock));
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->will($this->returnValue(false));
        $this->quoteMock->expects($this->never())->method('removeItem');

        $this->repository->deleteById($cartId, $itemId);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not remove item from quote
     */
    public function testDeleteWithCouldNotSaveException()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($this->quoteItemMock);
        $this->quoteMock->expects($this->once())
            ->method('removeItem')
            ->with($itemId)
            ->willReturn($this->quoteMock);
        $exceptionMessage = 'Could not remove item from quote';
        $exception = new \Magento\Framework\Exception\CouldNotSaveException(__($exceptionMessage));
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->repository->deleteById($cartId, $itemId);
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $productType = 'type';
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with(33)
            ->will($this->returnValue($quoteMock));
        $itemMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
        $quoteMock->expects($this->once())->method('getAllVisibleItems')->will($this->returnValue([$itemMock]));
        $itemMock->expects($this->once())->method('getProductType')->willReturn($productType);

        $this->optionsProcessorMock->expects($this->once())
            ->method('addProductOptions')
            ->with($productType, $itemMock)
            ->willReturn($itemMock);
        $this->optionsProcessorMock->expects($this->once())
            ->method('applyCustomOptions')
            ->with($itemMock)
            ->willReturn($itemMock);

        $this->assertEquals([$itemMock], $this->repository->getList(33));
    }

    /**
     * @return void
     */
    public function testDeleteById()
    {
        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($this->quoteItemMock);
        $this->quoteMock->expects($this->once())->method('removeItem');
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);

        $this->assertTrue($this->repository->deleteById($cartId, $itemId));
    }
}
