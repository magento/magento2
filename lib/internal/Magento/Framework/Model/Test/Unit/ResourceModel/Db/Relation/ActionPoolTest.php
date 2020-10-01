<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db\Relation;

use Magento\Framework\Model\ResourceModel\Db\Relation\ActionPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionPoolTest extends TestCase
{
    /**
     * @var ActionPool
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);

        $entityType = 'Entity_Test';
        $actionName = ['Test_Read' => ['Test_Class']];

        $relationActions = [$entityType => $actionName];
        $this->model = $objectManager->getObject(
            ActionPool::class,
            [
                'objectManager' => $this->objectManagerMock,
                'relationActions' => $relationActions
            ]
        );
    }

    public function testGetActionsNoAction()
    {
        $this->assertEmpty($this->model->getActions('test', 'test'));
    }

    public function testGetActions()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('Test_Class')
            ->willReturn(new \stdClass());
        $this->assertNotEmpty($this->model->getActions('Entity_Test', 'Test_Read'));
    }
}
