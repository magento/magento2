<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model;

/**
 * Unit test for \Magento\Integration\Model\Integration
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Model\Integration
     */
    protected $integrationModel;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollectionMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMock(
            'Magento\Framework\Model\Context',
            ['getEventDispatcher'],
            [],
            '',
            false
        );
        $eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            ['dispatch']
        );
        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($eventManagerMock));
        $this->registryMock = $this->getMock(
            'Magento\Framework\Registry',
            [],
            [],
            '',
            false
        );
        $this->resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\ResourceModel\AbstractResource',
            [],
            '',
            false,
            true,
            true,
            ['getIdFieldName', 'load', 'selectActiveIntegrationByConsumerId']
        );
        $this->resourceCollectionMock = $this->getMock(
            'Magento\Framework\Data\Collection\AbstractDb',
            [],
            [],
            '',
            false
        );
        $this->integrationModel = new \Magento\Integration\Model\Integration(
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
            ->with($this->integrationModel, $consumerId, \Magento\Integration\Model\Integration::CONSUMER_ID);

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
            ->will($this->returnValue($integrationData));

        $this->integrationModel->loadActiveIntegrationByConsumerId($consumerId);
        $this->assertEquals($integrationData, $this->integrationModel->getData());
    }

    public function testGetStatus()
    {
        $this->integrationModel->setStatus(1);
        $this->assertEquals(1, $this->integrationModel->getStatus());
    }
}
