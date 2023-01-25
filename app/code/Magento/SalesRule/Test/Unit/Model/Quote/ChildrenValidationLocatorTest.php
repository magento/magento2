<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Quote;

use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\SalesRule\Model\Quote\ChildrenValidationLocator
 */
class ChildrenValidationLocatorTest extends TestCase
{
    /**
     * @var array
     */
    private $productTypeChildrenValidationMap;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ChildrenValidationLocator
     */
    private $model;

    /**
     * @var QuoteItem|MockObject
     */
    private $quoteItemMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->productTypeChildrenValidationMap = [
            'type1' => true,
            'type2' => false,
        ];

        $this->quoteItemMock = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMockForAbstractClass();

        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId'])
            ->getMock();

        $this->model = $this->objectManager->getObject(
            ChildrenValidationLocator::class,
            [
                'productTypeChildrenValidationMap' => $this->productTypeChildrenValidationMap,
            ]
        );
    }

    /**
     * @dataProvider productTypeDataProvider
     * @param string $type
     * @param bool $expected
     *
     * @return void
     */
    public function testIsChildrenValidationRequired(string $type, bool $expected): void
    {
        $this->quoteItemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($type);

        $this->assertEquals($this->model->isChildrenValidationRequired($this->quoteItemMock), $expected);
    }

    /**
     * @return array
     */
    public function productTypeDataProvider(): array
    {
        return [
            ['type1', true],
            ['type2', false],
            ['type3', true],
        ];
    }
}
