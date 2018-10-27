<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

/**
 * Class CompareTest
 */
class CompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\Compare
     */
    private $helper;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $comparedMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionMock;

    /**
     * @var \Magento\Framework\Serialize\JsonValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonValidatorMock;

    /**
     * test setUp
     */
    protected function setUp()
    {
        $this->itemMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['__wakeup', 'getProductId', 'getOptions']);
        $this->comparedMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['__wakeup', 'getProductId', 'getOptions']);
        $this->optionMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Item\Option::class, ['__wakeup', 'getCode', 'getValue']);
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOptionMock($code, $value)
    {
        $optionMock = clone $this->optionMock;
        $optionMock->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue($code));
        $optionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($value));
        return $optionMock;
    }

    /**
     * test compare two different products
     */
    public function testCompareDifferentProduct()
    {
        $this->itemMock->expects($this->once())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->itemMock->expects($this->once())
            ->method('getProductId')
            ->will($this->returnValue(2));

        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items with different options
     */
    public function testCompareProductWithDifferentOptions()
    {
        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));

        $this->itemMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                    $this->getOptionMock('option-1', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', json_encode([
                            'value' => 'value-1',
                            'qty' => 2,
                        ])
                    ), ]
            ));
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                    $this->getOptionMock('option-4', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', json_encode([
                        'value' => 'value-1',
                        'qty' => 2,
                    ])),
                ])
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
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->itemMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                    $this->getOptionMock('option-1', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', json_encode([
                            'value' => 'value-1',
                            'qty' => 2,
                        ])
                    ), ]
            ));
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([]));
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items first without options and second with options
     */
    public function testCompareItemWithoutOptionWithCompared()
    {
        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                    $this->getOptionMock('option-1', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', json_encode([
                            'value' => 'value-1',
                            'qty' => 2,
                        ])
                    ), ]
            ));
        $this->itemMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([]));
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * Verify that compare ignores empty options.
     */
    public function testCompareWithEmptyValues()
    {
        $itemOptionValue = '{"non-empty-option":"test","empty_option":""}';
        $comparedOptionValue = '{"non-empty-option":"test"}';

        $this->jsonValidatorMock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));

        $this->itemMock->expects($this->once())
            ->method('getOptions')
            ->willReturn(
                [
                    $this->getOptionMock('option-1', $itemOptionValue)
                ]
            );
        $this->comparedMock->expects($this->once())
            ->method('getOptions')
            ->willReturn(
                [
                    $this->getOptionMock('option-1', $comparedOptionValue)
                ]
            );
        
        $this->assertTrue($this->helper->compare($this->itemMock, $this->comparedMock));
    }
}
