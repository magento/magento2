<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rule\Test\Unit\Model\Condition;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\ConditionFactory;
use Magento\SalesRule\Model\Rule\Condition\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CombineTest extends TestCase
{
    /**
     * @var Combine|MockObject
     */
    private $combine;

    /**
     * @var ConditionFactory|MockObject
     */
    private $conditionFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var  Product|MockObject
     */
    private $conditionObjectMock;

    /**
     * Sets up the Mocks.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->conditionFactoryMock = $this->getMockBuilder(ConditionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->conditionObjectMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->combine = (new ObjectManagerHelper($this))->getObject(
            Combine::class,
            [
                "conditionFactory"    => $this->conditionFactoryMock,
                "logger"    => $this->loggerMock,
            ]
        );
    }

    /**
     *
     * @covers \Magento\Rule\Model\Condition\AbstractCondition::getValueName
     *
     * @dataProvider optionValuesData
     *
     * @param string|array $value
     * @param string $expectingData
     */
    public function testGetValueName($value, $expectingData)
    {
        $this->combine
            ->setValueOption(['option_key' => 'option_value'])
            ->setValue($value);

        $this->assertEquals($expectingData, $this->combine->getValueName());
    }

    /**
     * @return array
     */
    public function optionValuesData()
    {
        return [
            ['option_key', 'option_value'],
            ['option_value', 'option_value'],
            [['option_key'], 'option_value'],
            ['', '...'],
        ];
    }

    public function testLoadArray()
    {
        $array['conditions'] = [
            [
                'type' => 'test',
                'attribute' => '',
                'operator' => '',
                'value' => '',
            ],
        ];

        $this->conditionObjectMock->expects($this->once())
            ->method('loadArray')
            ->with($array['conditions'][0], 'conditions');

        $this->conditionFactoryMock->expects($this->once())
            ->method('create')
            ->with($array['conditions'][0]['type'])
            ->willReturn($this->conditionObjectMock);

        $this->loggerMock->expects($this->never())
            ->method('critical');

        $result = $this->combine->loadArray($array);

        $this->assertInstanceOf(Combine::class, $result);
    }

    public function testLoadArrayLoggerCatchException()
    {
        $array['conditions'] = [
            [
                'type' => '',
                'attribute' => '',
                'operator' => '',
                'value' => '',
            ],
        ];

        $this->conditionObjectMock->expects($this->never())
            ->method('loadArray');

        $this->conditionFactoryMock->expects($this->once())
            ->method('create')
            ->with($array['conditions'][0]['type'])
            ->willThrowException(new \Exception('everything is fine, it is test'));

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with();

        $result = $this->combine->loadArray($array);

        $this->assertInstanceOf(Combine::class, $result);
    }
}
