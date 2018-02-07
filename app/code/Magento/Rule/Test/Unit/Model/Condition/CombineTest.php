<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model\Condition;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CombineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\Condition\Combine | \PHPUnit_Framework_MockObject_MockObject
     */
    private $combine;

    /**
     * @var \Magento\Rule\Model\ConditionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var  \Magento\SalesRule\Model\Rule\Condition\Product | \PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionObjectMock;

    /**
     * Sets up the Mocks.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->conditionFactoryMock = $this->getMockBuilder('\Magento\Rule\Model\ConditionFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->loggerMock = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->conditionObjectMock = $this->getMockBuilder('\Magento\SalesRule\Model\Rule\Condition\Product')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->combine = (new ObjectManagerHelper($this))->getObject(
            '\Magento\Rule\Model\Condition\Combine',
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

        $this->assertInstanceOf('\Magento\Rule\Model\Condition\Combine', $result);
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

        $this->assertInstanceOf('\Magento\Rule\Model\Condition\Combine', $result);
    }
}
