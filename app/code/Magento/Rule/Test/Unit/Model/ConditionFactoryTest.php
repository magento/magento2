<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConditionFactoryTest extends \PHPUnit\Framework\TestCase
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
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

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
        $type = 'type';
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with($type)
            ->willReturn(new \stdClass());

        $this->conditionFactory->create($type);
    }

    public function testExceptingClonedObject()
    {
        $origin = new \stdClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('clone')
            ->willReturn($origin);

        $cloned = $this->conditionFactory->create('clone');

        $this->assertNotSame($cloned, $origin);
    }
}
