<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Integration\Model\Integration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\Integration
 */
class IntegrationTest extends TestCase
{
    /**
     * @var \Magento\Integration\Model\Integration
     */
    protected $integrationModel;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var AbstractResource|MockObject
     */
    protected $resourceMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceCollectionMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createPartialMock(Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($eventManagerMock);
        $this->registryMock = $this->createMock(Registry::class);
        $this->resourceMock = $this->getMockForAbstractClass(
            AbstractResource::class,
            [],
            '',
            false,
            true,
            true,
            ['getIdFieldName', 'load', 'selectActiveIntegrationByConsumerId']
        );
        $this->resourceCollectionMock = $this->createMock(AbstractDb::class);
        $this->integrationModel = new Integration(
            $this->contextMock,
            $this->registryMock,
            $this->resourceMock,
            $this->resourceCollectionMock
        );
    }

    public function testLoadByConsumerId()
    {
        $consumerId = 1;
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($this->integrationModel, $consumerId, Integration::CONSUMER_ID);

        $this->integrationModel->loadByConsumerId($consumerId);
        $this->assertFalse($this->integrationModel->hasDataChanges());
    }

    public function testLoadActiveIntegrationByConsumerId()
    {
        $consumerId = 1;
        $integrationData = [
            'integration_id' => 1,
            'name' => 'Test Integration'
        ];

        $this->resourceMock->expects($this->once())
            ->method('selectActiveIntegrationByConsumerId')
            ->with($consumerId)
            ->willReturn($integrationData);

        $this->integrationModel->loadActiveIntegrationByConsumerId($consumerId);
        $this->assertEquals($integrationData, $this->integrationModel->getData());
    }

    public function testGetStatus()
    {
        $this->integrationModel->setStatus(1);
        $this->assertEquals(1, $this->integrationModel->getStatus());
    }
}
