<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTimeMock;

    /**
     * @var \Magento\Framework\Model\Resource\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceCollectionMock;

    public function setUp()
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
        $this->dateTimeMock = $this->getMock(
            'Magento\Framework\Stdlib\DateTime',
            [],
            [],
            '',
            false
        );
        $this->resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\AbstractResource',
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
            $this->dateTimeMock,
            $this->resourceMock,
            $this->resourceCollectionMock
        );
    }

    public function testBeforeSave()
    {
        $timeStamp = '0000';
        $this->dateTimeMock->expects($this->exactly(2))
            ->method('formatDate')
            ->will($this->returnValue($timeStamp));
        $this->integrationModel->beforeSave();
        $this->assertEquals($timeStamp, $this->integrationModel->getCreatedAt());
        $this->assertEquals($timeStamp, $this->integrationModel->getUpdatedAt());
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
