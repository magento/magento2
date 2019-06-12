<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Quote;

<<<<<<< HEAD
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;

/**
 * @covers \Magento\SalesRule\Model\Quote\ChildrenValidationLocator
=======
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product;

/**
 * Test for Magento\SalesRule\Model\Quote\ChildrenValidationLocator
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class ChildrenValidationLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
<<<<<<< HEAD
     * Testable Object
     *
     * @var ChildrenValidationLocator
     */
    private $childrenValidationLocator;

    /**
     * @var QuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var array
     */
    private $productTypeChildrenValidationMap = [
        'simple' => false,
        'bundle' => true,
    ];

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->itemMock = $this->createMock(QuoteItem::class);
        $this->productMock = $this->createMock(Product::class);
        $this->childrenValidationLocator = new ChildrenValidationLocator($this->productTypeChildrenValidationMap);
    }

    /**
     * Test isChildrenValidationRequired method
     *
     * @dataProvider childrenValidationDataProvider
     *
     * @param string $typeId
     * @param bool $isValidationRequired
     *
     * @return void
     */
    public function testIsChildrenValidationRequired($typeId, $isValidationRequired)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn($typeId);
        $this->itemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
        $actual = $this->childrenValidationLocator->isChildrenValidationRequired($this->itemMock);
        $expected = $isValidationRequired;
        self::assertEquals($expected, $actual);
=======
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
     * @var QuoteItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItemMock;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    protected function setUp()
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * @return array
     */
<<<<<<< HEAD
    public function childrenValidationDataProvider()
    {
        return [
            ['simple', $this->productTypeChildrenValidationMap['simple']],
            ['bundle', $this->productTypeChildrenValidationMap['bundle']],
            ['configurable', true],
=======
    public function productTypeDataProvider(): array
    {
        return [
            ['type1', true],
            ['type2', false],
            ['type3', true],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ];
    }
}
