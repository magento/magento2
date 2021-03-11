<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CustomOptions\CustomOptionProcessor;
use Magento\Catalog\Model\Product;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor;
use Magento\Quote\Model\Quote\Item\Repository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var MockObject
     */
    private $itemMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * @var MockObject
     */
    private $productMock;

    /**
     * @var MockObject
     */
    private $quoteItemMock;

    /**
     * @var CartItemInterfaceFactory|MockObject
     */
    private $itemDataFactoryMock;

    /**
     * @var CustomOptionProcessor|MockObject
     */
    private $customOptionProcessor;

    /**
     * @var Address|MockObject
     */
    private $shippingAddressMock;

    /**
     * @var CartItemOptionsProcessor|MockObject
     */
    private $optionsProcessorMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->itemDataFactoryMock = $this->createPartialMock(CartItemInterfaceFactory::class, ['create']);
        $this->itemMock = $this->createMock(Item::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->productMock = $this->createMock(Product::class);
        $methods = ['getId', 'getSku', 'getQty', 'setData', '__wakeUp', 'getProduct', 'addProduct'];
        $this->quoteItemMock =
            $this->createPartialMock(Item::class, $methods);
        $this->customOptionProcessor = $this->createMock(CustomOptionProcessor::class);
        $this->shippingAddressMock = $this->createPartialMock(
            Address::class,
            ['setCollectShippingRates']
        );
        $this->optionsProcessorMock = $this->createMock(CartItemOptionsProcessor::class);

        $this->repository = new Repository(
            $this->quoteRepositoryMock,
            $this->productRepositoryMock,
            $this->itemDataFactoryMock,
            $this->optionsProcessorMock,
            ['custom_options' => $this->customOptionProcessor]
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
            Quote::class,
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
     */
    public function testDeleteWithInvalidQuoteItem()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('The 11 Cart doesn\'t contain the 5 item.');

        $cartId = 11;
        $itemId = 5;
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())
            ->method('getItemById')->with($itemId)->willReturn(false);
        $this->quoteMock->expects($this->never())->method('removeItem');

        $this->repository->deleteById($cartId, $itemId);
    }

    /**
     * @return void
     */
    public function testDeleteWithCouldNotSaveException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('The item couldn\'t be removed from the quote.');

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
        $quoteMock = $this->createMock(Quote::class);
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')
            ->with(33)
            ->willReturn($quoteMock);
        $itemMock = $this->createMock(Item::class);
        $quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn([$itemMock]);
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
