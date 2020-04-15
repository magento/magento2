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

class QueryModifierFactoryTest extends \PHPUnit\Framework\TestCase
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
     * @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var InQueryModifier|\PHPUnit\Framework\MockObject\MockObject
     */
    private $inQueryModifierMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->inQueryModifierMock = $this->createMock(InQueryModifier::class);
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
     */
    public function testCreateUnknownQueryModifierType()
    {
        $this->expectException(\InvalidArgumentException::class);

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
     */
    public function testCreateDoesNotImplementInterface()
    {
        $this->expectException(\InvalidArgumentException::class);

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
