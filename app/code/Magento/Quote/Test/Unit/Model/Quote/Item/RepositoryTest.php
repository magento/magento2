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
class RepositoryTest extends \PHPUnit\Framework\TestCase
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
        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->itemDataFactoryMock =
            $this->createPartialMock(\Magento\Quote\Api\Data\CartItemInterfaceFactory::class, ['create']);
        $this->itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $methods = ['getId', 'getSku', 'getQty', 'setData', '__wakeUp', 'getProduct', 'addProduct'];
        $this->quoteItemMock =
            $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, $methods);
        $this->customOptionProcessor = $this->createMock(
            \Magento\Catalog\Model\CustomOptions\CustomOptionProcessor::class
        );
        $this->shippingAddressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['setCollectShippingRates']
        );

        $this->optionsProcessorMock = $this->createMock(
            \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor::class
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

        $quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getItems', 'setItems', 'collectTotals', 'getLastAddedItem']
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
     * @expectedExceptionMessage The 11 Cart doesn't contain the 5 item.
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
     * @expectedExceptionMessage The item couldn't be removed from the quote.
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
        $exceptionMessage = "The item couldn't be removed from the quote.";
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
        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with(33)
            ->will($this->returnValue($quoteMock));
        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
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
