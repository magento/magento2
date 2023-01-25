<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rule\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\ConditionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConditionFactoryTest extends TestCase
{
    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->conditionFactory = $this->objectManagerHelper->getObject(
            ConditionFactory::class,
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testExceptingToCallMethodCreateInObjectManager()
    {
        $type = Combine::class;
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
        $type = Combine::class;
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class does not exist');

        $this->conditionFactory->create($type);
    }

    public function testCreateExceptionType()
    {
        $type = ConditionFactory::class;

        $this->objectManagerMock
            ->expects($this->never())
            ->method('create')
            ->with($type)
            ->willReturn(new \stdClass());
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Class does not implement condition interface');
        $this->conditionFactory->create($type);
    }
}
