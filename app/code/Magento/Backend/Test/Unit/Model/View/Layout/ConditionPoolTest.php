<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Model\View\Layout;

use Magento\Backend\Model\View\Layout\ConditionPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class ConditionPoolTest
 */
class ConditionPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var ConditionPool
     */
    private $conditionPool;

    public function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->conditionPool = $objectManager->getObject(
            ConditionPool::class,
            [
                'conditions' => [
                    'condition-1' => 'Condition_1',
                    'condition-2' => 'Condition_2',
                ],
                'objectManager' => $this->objectManagerMock
            ]
        );
    }

    public function testGetCondition()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Condition_1')
            ->willReturn('Condition1Instance');
        $this->assertEquals(
            'Condition1Instance',
            $this->conditionPool->getCondition('condition-1')
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testGetConditionUnknownCondition()
    {
        $this->conditionPool->getCondition('condition-3');
    }
}
