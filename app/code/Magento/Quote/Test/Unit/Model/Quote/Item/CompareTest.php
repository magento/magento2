<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the class that is used to compare Quote Item Options
 */
class CompareTest extends TestCase
{
    use MockCreationTrait;
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
            ->onlyMethods(['__wakeup', 'getOptions', 'getOptionsByCode', 'getSku'])
            ->setConstructorArgs($constrArgs)
            ->getMock();
        $this->comparedMock = $this->getMockBuilder(Item::class)
            ->onlyMethods(['__wakeup', 'getOptions', 'getOptionsByCode', 'getSku'])
            ->setConstructorArgs($constrArgs)
            ->getMock();
        $serializer = $this->createMock(Json::class);
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
     * @return Option
     */
    protected function getOption($code, $value): Option
    {
        $option = $this->createPartialMockWithReflection(
            Option::class,
            ['setCode', 'getCode', 'setValue', 'getValue']
        );
        $option->method('getCode')->willReturn($code);
        $option->method('getValue')->willReturn($value);
        return $option;
    }

    /**
     * test compare two different products
     */
    public function testCompareDifferentProduct()
    {
        $this->itemMock->setData('product_id', 1);
        $this->comparedMock->setData('product_id', 2);

        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items with different options
     */
    public function testCompareProductWithDifferentOptions()
    {
        // Identical Product Ids
        $this->itemMock->setData('product_id', 1);
        $this->comparedMock->setData('product_id', 1);

        // Identical Option Keys
        $this->itemMock->method('getOptions')->willReturn([$this->getOption('identical', 'value')]);
        $this->comparedMock->method('getOptions')->willReturn([$this->getOption('identical', 'value')]);

        // Different Option Values
        $this->itemMock->expects($this->once())
            ->method('getOptionsByCode')
            ->willReturn(
                [
                    'info_buyRequest' => $this->getOption('info_buyRequest', ['value-1']),
                    'option' => $this->getOption('option', 1),
                    'simple_product' => $this->getOption('simple_product', 3),
                    'product_qty_2' => $this->getOption('product_qty_2', 10),
                    'attributes' => $this->getOption('attributes', 93),
                ]
            );

        $this->comparedMock->expects($this->once())
            ->method('getOptionsByCode')
            ->willReturn(
                [
                    'info_buyRequest' => $this->getOption('info_buyRequest', ['value-2']),
                    'option' => $this->getOption('option', 1),
                    'simple_product' => $this->getOption('simple_product', 3),
                    'product_qty_2' => $this->getOption('product_qty_2', 10),
                    'attributes' => $this->getOption('attributes', 94),
                ]
            );

        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items first with options and second without options
     */
    public function testCompareItemWithComparedWithoutOption()
    {
        $this->itemMock->setData('product_id', 1);
        $this->comparedMock->setData('product_id', 1);
        $this->itemMock->expects($this->once())
            ->method('getOptionsByCode')
            ->willReturn(
                [
                    'info_buyRequest' => $this->getOption('info_buyRequest', ['value-1']),
                    'option' => $this->getOption('option', 1),
                    'simple_product' => $this->getOption('simple_product', 3),
                    'product_qty_2' => $this->getOption('product_qty_2', 10),
                    'attributes' => $this->getOption('attributes', 93),
                ]
            );
        $this->comparedMock->method('getOptionsByCode')->willReturn([]);
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items first without options and second with options
     */
    public function testCompareItemWithoutOptionWithCompared()
    {
        $this->itemMock->setData('product_id', 1);
        $this->comparedMock->setData('product_id', 1);

        $this->comparedMock->expects($this->once())
            ->method('getOptionsByCode')
            ->willReturn(
                [
                    'info_buyRequest' => $this->getOption('info_buyRequest', ['value-2']),
                    'option' => $this->getOption('option', 1),
                    'simple_product' => $this->getOption('simple_product', 3),
                    'product_qty_2' => $this->getOption('product_qty_2', 10),
                    'attributes' => $this->getOption('attributes', 94),
                ]
            );
        $this->itemMock->method('getOptionsByCode')->willReturn([]);
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }
}
