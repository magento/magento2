<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select\InQueryModifier;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryModifierFactoryTest extends TestCase
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
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var InQueryModifier|MockObject
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

    public function testCreateUnknownQueryModifierType()
    {
        $this->expectException('InvalidArgumentException');
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

    public function testCreateDoesNotImplementInterface()
    {
        $this->expectException('InvalidArgumentException');
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
