<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

/**
 * Class CompareTest
 *
 * Tests the class that is used to compare Quote Item Options
 */
class CompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\Compare
     */
    private $helper;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private $itemMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private $comparedMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Option|\PHPUnit\Framework\MockObject\MockObject
     */
    private $optionMock;

    /**
     * @var \Magento\Framework\Serialize\JsonValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $jsonValidatorMock;

    /**
     * test setUp
     */
    protected function setUp(): void
    {
        $this->itemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['__wakeup', 'getProductId', 'getOptions', 'getOptionsByCode']
        );
        $this->comparedMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['__wakeup', 'getProductId', 'getOptions', 'getOptionsByCode']
        );
        $this->optionMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item\Option::class,
            ['__wakeup', 'getCode', 'getValue']
        );
        $serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
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

        $this->jsonValidatorMock = $this->getMockBuilder(\Magento\Framework\Serialize\JsonValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper = $objectManagerHelper->getObject(
            \Magento\Quote\Model\Quote\Item\Compare::class,
            [
                'serializer' => $serializer,
                'jsonValidator' => $this->jsonValidatorMock
            ]
        );
    }

    /**
     * @param string $code
     * @param mixed $value
     * @return \PHPUnit\Framework\MockObject\MockObject
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
            ->willReturn(
                [
                    $this->getOptionMock('identical', 'value')
                ]
            );
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->willReturn(
                [
                    $this->getOptionMock('identical', 'value')
                ]
            );

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
}
