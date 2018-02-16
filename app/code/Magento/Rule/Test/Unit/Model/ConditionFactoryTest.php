<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConditionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->conditionFactory = $this->objectManagerHelper->getObject(
            \Magento\Rule\Model\ConditionFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testExceptingToCallMethodCreateInObjectManager()
    {
        $type = \Magento\Rule\Model\Condition\Combine::class;
        $origin = $this->getMockBuilder($type)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($type)
            ->willReturn($origin);

        $this->conditionFactory->create($type);
    }

    public function testExceptingClonedObject()
    {
        $type = \Magento\Rule\Model\Condition\Combine::class;
        $origin = $this->getMockBuilder($type)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($type)
            ->willReturn($origin);

        $cloned = $this->conditionFactory->create($type);

        $this->assertNotSame($cloned, $origin);
    }
    
    public function testCreateExceptionClass()
    {
        $type = 'type';
        $this->objectManagerMock
            ->expects($this->never())
            ->method('create');

        $this->setExpectedException(\InvalidArgumentException::class, 'Class does not exist');

        $this->conditionFactory->create($type);
    }

    public function testCreateExceptionType()
    {
        $type = \Magento\Rule\Model\ConditionFactory::class;

        $this->objectManagerMock
            ->expects($this->never())
            ->method('create')
            ->with($type)
            ->willReturn(new \stdClass());
        $this->setExpectedException(\InvalidArgumentException::class, 'Class does not implement condition interface');
        $this->conditionFactory->create($type);
    }
}
