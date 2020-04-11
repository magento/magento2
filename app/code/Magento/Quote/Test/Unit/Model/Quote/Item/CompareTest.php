<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\Quote\Item;

use Magento\Framework\Serialize\JsonValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Compare;
use Magento\Quote\Model\Quote\Item\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * test setUp
     */
    protected function setUp(): void
    {
        $this->itemMock = $this->createPartialMock(
            Item::class,
            ['__wakeup', 'getProductId', 'getOptions']
        );
        $this->comparedMock = $this->createPartialMock(
            Item::class,
            ['__wakeup', 'getProductId', 'getOptions']
        );
        $this->optionMock = $this->createPartialMock(
            Option::class,
            ['__wakeup', 'getCode', 'getValue']
        );
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

        $objectManagerHelper = new ObjectManager($this);
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
            ->will(
                $this->returnValue(
                    [
                        $this->getOptionMock('option-1', 1),
                        $this->getOptionMock('option-2', 'option-value'),
                        $this->getOptionMock('option-3', json_encode(['value' => 'value-1', 'qty' => 2]))
                    ]
                )
            );
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue(
                [
                    $this->getOptionMock('option-4', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', json_encode([
                        'value' => 'value-1',
                        'qty' => 2,
                    ])),
                ]
            ));
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
            ->will(
                $this->returnValue(
                    [
                        $this->getOptionMock('option-1', 1),
                        $this->getOptionMock('option-2', 'option-value'),
                        $this->getOptionMock('option-3', json_encode(['value' => 'value-1', 'qty' => 2])),
                    ]
                )
            );
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
            ->will($this->returnValue(
                [
                    $this->getOptionMock('option-1', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock(
                        'option-3',
                        json_encode(['value' => 'value-1', 'qty' => 2])
                    ),
                ]
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
