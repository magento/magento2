<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Wishlist\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\GroupedProduct\Model\Product\Type\Grouped as TypeGrouped;
use Magento\GroupedProduct\Model\Wishlist\Product\Item;
use Magento\Wishlist\Model\Item\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Wishlist Item Plugin.
 */
class ItemTest extends TestCase
{
    /**
     * @var Item
     */
    protected $model;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Wishlist\Model\Item|MockObject
     */
    protected $subjectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->subjectMock = $this->createPartialMock(
            \Magento\Wishlist\Model\Item::class,
            [
                'getOptionsByCode',
                'getBuyRequest',
                'setOptions',
                'mergeBuyRequest',
                'getProduct'
            ]
        );

        $this->productMock = $this->createPartialMock(
            Product::class,
            [
                'getId',
                'getTypeId',
                'getCustomOptions'
            ]
        );

        $this->model = new Item();
    }

    /**
     * Test Before Represent Product method
     *
     * @return void
     */
    public function testBeforeRepresentProduct(): void
    {
        $testSimpleProdId = 34;
        $prodInitQty = 2;
        $prodQtyInWishlist = 3;
        $resWishlistQty = $prodInitQty + $prodQtyInWishlist;
        $superGroup = [
            'super_group' => [
                33 => "0",
                34 => 3,
                35 => "0"
            ]
        ];

        $superGroupObj = new DataObject($superGroup);

        $this->productMock->expects($this->once())->method('getId')->willReturn($testSimpleProdId);
        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(TypeGrouped::TYPE_CODE);
        $this->productMock->expects($this->once())->method('getCustomOptions')
            ->willReturn(
                $this->getProductAssocOption($prodInitQty, $testSimpleProdId)
            );

        $wishlistItemProductMock = $this->createPartialMock(
            Product::class,
            ['getId']
        );
        $wishlistItemProductMock->expects($this->once())->method('getId')->willReturn($testSimpleProdId);

        $this->subjectMock->expects($this->once())->method('getProduct')
            ->willReturn($wishlistItemProductMock);
        $this->subjectMock->expects($this->once())->method('getOptionsByCode')
            ->willReturn(
                $this->getWishlistAssocOption($prodQtyInWishlist, $resWishlistQty, $testSimpleProdId)
            );
        $this->subjectMock->expects($this->once())->method('getBuyRequest')->willReturn($superGroupObj);

        $this->model->beforeRepresentProduct($this->subjectMock, $this->productMock);
    }

    /**
     * Test Before Compare Options method with same keys
     *
     * @return void
     */
    public function testBeforeCompareOptionsSameKeys(): void
    {
        $infoBuyRequestMock = $this->createPartialMock(
            Product\Configuration\Item\Option::class,
            ['getValue']
        );

        $infoBuyRequestMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('{"product":"3","action":"add"}');
        $options1 = [
            'associated_product_34' => 3,
            'info_buyRequest' => $infoBuyRequestMock,
        ];
        $options2 = [
            'associated_product_34' => 3,
            'info_buyRequest' => $infoBuyRequestMock,
        ];

        $res = $this->model->beforeCompareOptions($this->subjectMock, $options1, $options2);

        $this->assertEquals(['info_buyRequest' => $infoBuyRequestMock], $res[0]);
        $this->assertEquals(['info_buyRequest' => $infoBuyRequestMock], $res[1]);
    }

    /**
     * Test Before Compare Options method with diff keys
     *
     * @return void
     */
    public function testBeforeCompareOptionsDiffKeys(): void
    {
        $options1 = ['associated_product_1' => 3];
        $options2 = ['associated_product_34' => 2];

        $res = $this->model->beforeCompareOptions($this->subjectMock, $options1, $options2);

        $this->assertEquals($options1, $res[0]);
        $this->assertEquals($options2, $res[1]);
    }

    /**
     * Return mock array with wishlist options
     *
     * @param int $initVal
     * @param int $resVal
     * @param int $prodId
     *
     * @return array
     */
    private function getWishlistAssocOption(int $initVal, int $resVal, int $prodId): array
    {
        $items = [];

        $optionMock = $this->createPartialMock(
            Option::class,
            ['getValue']
        );
        $optionMock
            ->method('getValue')
            ->willReturnOnConsecutiveCalls($initVal, $resVal);

        $items['associated_product_' . $prodId] = $optionMock;

        return $items;
    }

    /**
     * Return mock array with product options
     *
     * @param int $initVal
     * @param int $prodId
     *
     * @return array
     */
    private function getProductAssocOption(int $initVal, int $prodId): array
    {
        $items = [];

        $associatedProductMock = $this->createPartialMock(
            Product\Configuration\Item\Option::class,
            ['getValue']
        );
        $infoBuyRequestMock = $this->createPartialMock(
            Product\Configuration\Item\Option::class,
            ['getValue']
        );

        $associatedProductMock->expects($this->once())->method('getValue')->willReturn($initVal);
        $infoBuyRequestMock->expects($this->once())
            ->method('getValue')
            ->willReturn('{"product":"'. $prodId . '","action":"add"}');

        $items['associated_product_' . $prodId] = $associatedProductMock;
        $items['info_buyRequest'] = $infoBuyRequestMock;

        return $items;
    }
}
