<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\DB\Select\InQueryModifier;
use Magento\Framework\ObjectManagerInterface;

class QueryModifierFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var InQueryModifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inQueryModifierMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMock(ObjectManagerInterface::class);
        $this->inQueryModifierMock = $this->getMock(InQueryModifier::class, [], [], '', false);
    }

    public function testCreate()
    {
        $params = ['foo' => 'bar'];
        $this->queryModifierFactory = $this->objectManager->getObject(
            QueryModifierFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'queryModifiers' => [
                    'in' => InQueryModifier::class
                ]
            ]
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                InQueryModifier::class,
                $params
            )
            ->willReturn($this->inQueryModifierMock);
        $this->queryModifierFactory->create('in', $params);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateUnknownQueryModifierType()
    {
        $params = ['foo' => 'bar'];
        $this->queryModifierFactory = $this->objectManager->getObject(
            QueryModifierFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'queryModifiers' => []
            ]
        );
        $this->objectManagerMock->expects($this->never())
            ->method('create');
        $this->queryModifierFactory->create('in', $params);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateDoesNotImplementInterface()
    {
        $params = ['foo' => 'bar'];
        $this->queryModifierFactory = $this->objectManager->getObject(
            QueryModifierFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'queryModifiers' => [
                    'in' => \stdClass::class
                ]
            ]
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                \stdClass::class,
                $params
            )
            ->willReturn(new \stdClass());
        $this->queryModifierFactory->create('in', $params);
    }
}
