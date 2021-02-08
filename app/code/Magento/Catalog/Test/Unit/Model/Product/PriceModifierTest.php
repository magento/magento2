<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product;

class PriceModifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\PriceModifier
     */
    protected $priceModifier;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var array
     */
    protected $prices = [];

    protected function setUp(): void
    {
        $this->productMock =
            $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getData', 'setData', '__wakeup']);
        $this->productRepositoryMock = $this->createMock(\Magento\Catalog\Model\ProductRepository::class);
        $this->priceModifier = new \Magento\Catalog\Model\Product\PriceModifier(
            $this->productRepositoryMock
        );
        $this->prices = [
            0 => [
                'all_groups' => 0,
                'cust_group' => 1,
                'price_qty' => 15,
                'website_id' => 1,
            ],
            1 => [
                'all_groups' => 1,
                'cust_group' => 0,
                'price_qty' => 10,
                'website_id' => 1,
            ],
        ];
    }

    /**
     */
    public function testRemoveWhenTierPricesNotExists()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('Product hasn\'t group price with such data: customerGroupId = \'1\', website = 1, qty = 3');

        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn([]);
        $this->productMock->expects($this->never())->method('setData');
        $this->productRepositoryMock->expects($this->never())->method('save');
        $this->priceModifier->removeTierPrice($this->productMock, 1, 3, 1);
    }

    /**
     */
    public function testRemoveTierPriceForNonExistingCustomerGroup()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('Product hasn\'t group price with such data: customerGroupId = \'10\', website = 1, qty = 5');

        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn($this->prices);
        $this->productMock->expects($this->never())->method('setData');
        $this->productRepositoryMock->expects($this->never())->method('save');
        $this->priceModifier->removeTierPrice($this->productMock, 10, 5, 1);
    }

    public function testSuccessfullyRemoveTierPriceSpecifiedForAllGroups()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn($this->prices);
        $expectedPrices = [$this->prices[0]];
        $this->productMock->expects($this->once())->method('setData')->with('tier_price', $expectedPrices);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->priceModifier->removeTierPrice($this->productMock, 'all', 10, 1);
    }

    public function testSuccessfullyRemoveTierPriceSpecifiedForSpecificGroups()
    {
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('tier_price')
            ->willReturn($this->prices);
        $expectedPrices = [1 => $this->prices[1]];
        $this->productMock->expects($this->once())->method('setData')->with('tier_price', $expectedPrices);
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->productMock);
        $this->priceModifier->removeTierPrice($this->productMock, 1, 15, 1);
    }
}
