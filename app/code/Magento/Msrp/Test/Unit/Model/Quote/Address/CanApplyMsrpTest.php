<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Msrp\Test\Unit\Model\Quote\Address;

use Magento\Catalog\Model\Product;
use Magento\Msrp\Helper\Data;
use Magento\Msrp\Model\Quote\Address\CanApplyMsrp;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanApplyMsrpTest extends TestCase
{
    /**
     * @var CanApplyMsrp|null
     */
    private $canApplyMsrp;

    /**
     * @var Data|MockObject|null
     */
    private $msrpHelper;

    /**
     * @var Address|MockObject|null
     */
    private $address;

    protected function setUp(): void
    {
        $this->msrpHelper = $this->createMock(Data::class);
        $this->address = $this->createMock(Address::class);
        $this->canApplyMsrp = new CanApplyMsrp($this->msrpHelper);
    }

    public function testIsCanApplyMsrpWhenIsShowBeforeOrderConfirmAndIsMinimalPriceLessMsrpReturnTrue(): void
    {
        $item = $this->createPartialMock(Item::class, ['getProduct']);
        $product = $this->createMock(Product::class);
        $item->expects($this->exactly(2))->method('getProduct')->willReturn($product);
        $this->msrpHelper->expects($this->once())->method('isShowBeforeOrderConfirm')->with($product)->willReturn(true);
        $this->msrpHelper->expects($this->once())->method('isMinimalPriceLessMsrp')->with($product)->willReturn(true);
        $this->address->expects($this->once())->method('getAllItems')->willReturn([$item]);
        $this->assertTrue($this->canApplyMsrp->isCanApplyMsrp($this->address));
    }

    public function testIsCanApplyMsrpWhenIsMinimalPriceLessMsrpReturnsFalse(): void
    {
        $item = $this->createPartialMock(Item::class, ['getProduct']);
        $product = $this->createMock(Product::class);
        $item->expects($this->exactly(2))->method('getProduct')->willReturn($product);
        $this->msrpHelper->expects($this->once())->method('isShowBeforeOrderConfirm')->with($product)->willReturn(true);
        $this->msrpHelper->expects($this->once())->method('isMinimalPriceLessMsrp')->with($product)->willReturn(false);
        $this->address->expects($this->once())->method('getAllItems')->willReturn([$item]);
        $this->assertFalse($this->canApplyMsrp->isCanApplyMsrp($this->address));
    }

    public function testIsCanApplyMsrpWhenIsShowBeforeOrderConfirmReturnsFalse(): void
    {
        $item = $this->createPartialMock(Item::class, ['getProduct']);
        $product = $this->createMock(Product::class);
        $item->expects($this->exactly(1))->method('getProduct')->willReturn($product);
        $this->msrpHelper->expects($this->once())
            ->method('isShowBeforeOrderConfirm')
            ->with($product)
            ->willReturn(false);
        $this->msrpHelper->expects($this->never())->method('isMinimalPriceLessMsrp')->with($product);
        $this->address->expects($this->once())->method('getAllItems')->willReturn([$item]);
        $this->assertFalse($this->canApplyMsrp->isCanApplyMsrp($this->address));
    }
}
