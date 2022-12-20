<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Framework\Serialize\JsonValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Compare;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote\Item\Option\Comparator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the class that is used to compare Quote Item Options
 */
class CompareTest extends TestCase
{
    /**
     * @var Compare
     */
    private $helper;

    /**
     * @var Item|MockObject
     */
    private $itemMock;

    /**
     * @var Item|MockObject
     */
    private $comparedMock;

    /**
     * @var Option|MockObject
     */
    private $optionMock;

    /**
     * @var JsonValidator|MockObject
     */
    private $jsonValidatorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $constrArgs = $objectManagerHelper->getConstructArguments(
            Item::class,
            [
                'itemOptionComparator' => new Comparator()
            ]
        );
        $this->itemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getProductId'])
            ->onlyMethods(['__wakeup', 'getOptions', 'getOptionsByCode', 'getSku'])
            ->setConstructorArgs($constrArgs)
            ->getMock();
        $this->comparedMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getProductId'])
            ->onlyMethods(['__wakeup', 'getOptions', 'getOptionsByCode', 'getSku'])
            ->setConstructorArgs($constrArgs)
            ->getMock();
        $this->optionMock = $this->getMockBuilder(Option::class)
            ->addMethods(['getCode'])
            ->onlyMethods(['__wakeup', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $serializer = $this->getMockBuilder(Json::class)
            ->setMethods(['unserialize'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->jsonValidatorMock = $this->getMockBuilder(JsonValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $objectManagerHelper->getObject(
            Compare::class,
            [
                'serializer' => $serializer,
                'jsonValidator' => $this->jsonValidatorMock
            ]
        );
    }

    /**
     * @param string $code
     * @param mixed $value
     * @return MockObject
     */
    protected function getOptionMock($code, $value)
    {
        $optionMock = clone $this->optionMock;
        $optionMock->expects($this->any())
            ->method('getCode')
            ->willReturn($code);
        $optionMock->expects($this->any())
            ->method('getValue')
            ->willReturn($value);
        return $optionMock;
    }

    /**
     * test compare two different products
     */
    public function testCompareDifferentProduct()
    {
        $this->itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn(1);
        $this->itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn(2);

        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items with different options
     */
    public function testCompareProductWithDifferentOptions()
    {
        // Identical Product Ids
        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);

        // Identical Option Keys
        $this->itemMock->expects($this->any())
            ->method('getOptions')
            ->willReturn([$this->getOptionMock('identical', 'value')]);
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->willReturn([$this->getOptionMock('identical', 'value')]);

        // Different Option Values
        $this->itemMock->expects($this->once())
            ->method('getOptionsByCode')
            ->willReturn(
                [
                    'info_buyRequest' => $this->getOptionMock('info_buyRequest', ['value-1']),
                    'option' => $this->getOptionMock('option', 1),
                    'simple_product' => $this->getOptionMock('simple_product', 3),
                    'product_qty_2' => $this->getOptionMock('product_qty_2', 10),
                    'attributes' => $this->getOptionMock('attributes', 93),
                ]
            );

        $this->comparedMock->expects($this->once())
            ->method('getOptionsByCode')
            ->willReturn(
                [
                    'info_buyRequest' => $this->getOptionMock('info_buyRequest', ['value-2']),
                    'option' => $this->getOptionMock('option', 1),
                    'simple_product' => $this->getOptionMock('simple_product', 3),
                    'product_qty_2' => $this->getOptionMock('product_qty_2', 10),
                    'attributes' => $this->getOptionMock('attributes', 94),
                ]
            );

        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items first with options and second without options
     */
    public function testCompareItemWithComparedWithoutOption()
    {
        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);
        $this->itemMock->expects($this->once())
            ->method('getOptionsByCode')
            ->willReturn(
                [
                    'info_buyRequest' => $this->getOptionMock('info_buyRequest', ['value-1']),
                    'option' => $this->getOptionMock('option', 1),
                    'simple_product' => $this->getOptionMock('simple_product', 3),
                    'product_qty_2' => $this->getOptionMock('product_qty_2', 10),
                    'attributes' => $this->getOptionMock('attributes', 93),
                ]
            );
        $this->comparedMock->expects($this->any())
            ->method('getOptionsByCode')
            ->willReturn([]);
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items first without options and second with options
     */
    public function testCompareItemWithoutOptionWithCompared()
    {
        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->willReturn(1);

        $this->comparedMock->expects($this->once())
            ->method('getOptionsByCode')
            ->willReturn(
                [
                    'info_buyRequest' => $this->getOptionMock('info_buyRequest', ['value-2']),
                    'option' => $this->getOptionMock('option', 1),
                    'simple_product' => $this->getOptionMock('simple_product', 3),
                    'product_qty_2' => $this->getOptionMock('product_qty_2', 10),
                    'attributes' => $this->getOptionMock('attributes', 94),
                ]
            );
        $this->itemMock->expects($this->any())
            ->method('getOptionsByCode')
            ->willReturn([]);
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items- when configurable products has assigned sku of its selected variant
     */
    public function testCompareConfigurableProductAndItsVariant()
    {
        $this->itemMock->expects($this->exactly(2))
            ->method('getSku')
            ->willReturn('cr1-r');
        $this->comparedMock->expects($this->once())
            ->method('getSku')
            ->willReturn('cr1-r');

        $this->assertTrue($this->helper->compare($this->itemMock, $this->comparedMock));
    }
}
