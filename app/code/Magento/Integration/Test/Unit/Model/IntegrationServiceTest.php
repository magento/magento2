<?php
/**
 * Test for \Magento\Integration\Model\IntegrationService
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Model\Integration;

class IntegrationServiceTest extends \PHPUnit\Framework\TestCase
{
    const VALUE_INTEGRATION_ID = 1;

    const VALUE_INTEGRATION_NAME = 'Integration Name';

    const VALUE_INTEGRATION_ANOTHER_NAME = 'Another Integration Name';

    const VALUE_INTEGRATION_EMAIL = 'test@magento.com';

    const VALUE_INTEGRATION_SETUP_BACKEND = 0;

    const VALUE_INTEGRATION_ENDPOINT = 'http://magento.ll/endpoint';

    const VALUE_INTEGRATION_CONSUMER_ID = 1;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $_integrationFactory;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $_integrationMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $_emptyIntegrationMock;

    /** @var \Magento\Integration\Model\IntegrationService */
    private $_service;

    /** @var array */
    private $_integrationData;

    protected function setUp(): void
    {
        $this->_integrationFactory = $this->getMockBuilder(\Magento\Integration\Model\IntegrationFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->_integrationMock = $this->getMockBuilder(
            \Magento\Integration\Model\Integration::class
        )->disableOriginalConstructor()->setMethods(
            [
                'getData',
                'getId',
                'getName',
                'getEmail',
                'getEndpoint',
                'load',
                'loadByName',
                'save',
                'delete',
                '__wakeup',
            ]
        )->getMock();
        $this->_integrationData = [
            Integration::ID => self::VALUE_INTEGRATION_ID,
            Integration::NAME => self::VALUE_INTEGRATION_NAME,
            Integration::EMAIL => self::VALUE_INTEGRATION_EMAIL,
            Integration::EMAIL => self::VALUE_INTEGRATION_ENDPOINT,
            Integration::SETUP_TYPE => self::VALUE_INTEGRATION_SETUP_BACKEND,
        ];
        $this->_integrationFactory->expects(
            $this->any()
        )->method(
            'create'
        )->willReturn(
            $this->_integrationMock
        );

        $oauthConsumerHelper = $this->getMockBuilder(
            \Magento\Integration\Api\OauthServiceInterface::class
        )->disableOriginalConstructor()->getMock();
        $oauthConsumer = $this->getMockBuilder(
            \Magento\Integration\Model\Oauth\Consumer::class
        )->disableOriginalConstructor()->getMock();
        $oauthConsumerHelper->expects(
            $this->any()
        )->method(
            'createConsumer'
        )->willReturn(
            $oauthConsumer
        );
        $oauthConsumerHelper->expects($this->any())->method('loadConsumer')->willReturn($oauthConsumer);

        $this->_service = new \Magento\Integration\Model\IntegrationService(
            $this->_integrationFactory,
            $oauthConsumerHelper
        );
        $this->_emptyIntegrationMock = $this->getMockBuilder(
            \Magento\Integration\Model\Integration::class
        )->disableOriginalConstructor()->setMethods(
            [
                'getData',
                'getId',
                'getName',
                'getEmail',
                'getEndpoint',
                'load',
                'loadByName',
                'save',
                'delete',
                '__wakeup',
            ]
        )->getMock();
        $this->_emptyIntegrationMock->expects($this->any())->method('getId')->willReturn(null);
    }

    public function testCreateSuccess()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->willReturn(
            self::VALUE_INTEGRATION_ID
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->willReturn(
            $this->_integrationData
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_NAME,
            'name'
        )->willReturn(
            $this->_emptyIntegrationMock
        );
        $this->_integrationMock->expects($this->any())->method('save')->willReturnSelf();
        $this->_setValidIntegrationData();
        $resultData = $this->_service->create($this->_integrationData)->getData();
        $this->assertSame($this->_integrationData, $resultData);
    }

    /**
     */
    public function testCreateIntegrationAlreadyExistsException()
    {
        $this->expectException(\Magento\Framework\Exception\IntegrationException::class);
        $this->expectExceptionMessage('The integration with name "Integration Name" exists.');

        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->willReturn(
            self::VALUE_INTEGRATION_ID
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->willReturn(
            $this->_integrationData
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_NAME,
            'name'
        )->willReturn(
            $this->_integrationMock
        );
        $this->_integrationMock->expects($this->never())->method('save')->willReturnSelf();
        $this->_service->create($this->_integrationData);
    }

    public function testUpdateSuccess()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->willReturn(
            self::VALUE_INTEGRATION_ID
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->willReturn(
            $this->_integrationData
        );
        $this->_integrationMock->expects(
            $this->at(0)
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_ID
        )->willReturn(
            $this->_integrationMock
        );
        $this->_integrationMock->expects($this->once())->method('save')->willReturnSelf();
        $this->_setValidIntegrationData();
        $integrationData = $this->_service->update($this->_integrationData)->getData();
        $this->assertEquals($this->_integrationData, $integrationData);
    }

    public function testUpdateSuccessNameChanged()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->willReturn(
            self::VALUE_INTEGRATION_ID
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->onConsecutiveCalls($this->_integrationMock, $this->_emptyIntegrationMock)
        );
        $this->_integrationMock->expects($this->once())->method('save')->willReturnSelf();
        $this->_setValidIntegrationData();
        $integrationData = [
            'integration_id' => self::VALUE_INTEGRATION_ID,
            'name' => self::VALUE_INTEGRATION_ANOTHER_NAME,
            'email' => self::VALUE_INTEGRATION_EMAIL,
            'endpoint' => self::VALUE_INTEGRATION_ENDPOINT,
        ];
        $this->_integrationMock->expects($this->any())->method('getData')->willReturn($integrationData);

        $updatedData = $this->_service->update($integrationData)->getData();
        $this->assertEquals($integrationData, $updatedData);
    }

    /**
     */
    public function testUpdateException()
    {
        $this->expectException(\Magento\Framework\Exception\IntegrationException::class);
        $this->expectExceptionMessage('The integration with name "Another Integration Name" exists.');

        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->willReturn(
            self::VALUE_INTEGRATION_ID
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->onConsecutiveCalls($this->_integrationMock, $this->_getAnotherIntegrationMock())
        );
        $this->_integrationMock->expects($this->never())->method('save')->willReturnSelf();
        $this->_setValidIntegrationData();
        $integrationData = [
            'integration_id' => self::VALUE_INTEGRATION_ID,
            'name' => self::VALUE_INTEGRATION_ANOTHER_NAME,
            'email' => self::VALUE_INTEGRATION_EMAIL,
            'endpoint' => self::VALUE_INTEGRATION_ENDPOINT,
        ];
        $this->_service->update($integrationData);
    }

    public function testGet()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getId'
        )->willReturn(
            self::VALUE_INTEGRATION_ID
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->willReturn(
            $this->_integrationData
        );
        $this->_integrationMock->expects($this->once())->method('load')->willReturnSelf();
        $this->_integrationMock->expects($this->never())->method('save');
        $integrationData = $this->_service->get(self::VALUE_INTEGRATION_ID)->getData();
        $this->assertEquals($this->_integrationData, $integrationData);
    }

    /**
     */
    public function testGetException()
    {
        $this->expectException(\Magento\Framework\Exception\IntegrationException::class);
        $this->expectExceptionMessage('The integration with ID "1" doesn\'t exist.');

        $this->_integrationMock->expects($this->any())->method('getId')->willReturn(null);
        $this->_integrationMock->expects($this->once())->method('load')->willReturnSelf();
        $this->_integrationMock->expects($this->never())->method('save');
        $this->_service->get(self::VALUE_INTEGRATION_ID)->getData();
    }

    public function testFindByName()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_NAME,
            'name'
        )->willReturn(
            $this->_integrationMock
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->willReturn(
            $this->_integrationData
        );
        $integration = $this->_service->findByName(self::VALUE_INTEGRATION_NAME);
        $this->assertEquals($this->_integrationData[Integration::NAME], $integration->getData()[Integration::NAME]);
    }

    public function testFindByNameNotFound()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_NAME,
            'name'
        )->willReturn(
            $this->_emptyIntegrationMock
        );
        $this->_emptyIntegrationMock->expects($this->any())->method('getData')->willReturn(null);
        $integration = $this->_service->findByName(self::VALUE_INTEGRATION_NAME);
        $this->assertNull($integration->getData());
    }

    public function testDelete()
    {
        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'getId'
        )->willReturn(
            self::VALUE_INTEGRATION_ID
        );
        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_ID
        )->willReturn(
            $this->_integrationMock
        );
        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'delete'
        )->willReturn(
            $this->_integrationMock
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->willReturn(
            $this->_integrationData
        );
        $integrationData = $this->_service->delete(self::VALUE_INTEGRATION_ID);
        $this->assertEquals($this->_integrationData[Integration::ID], $integrationData[Integration::ID]);
    }

    /**
     */
    public function testDeleteException()
    {
        $this->expectException(\Magento\Framework\Exception\IntegrationException::class);
        $this->expectExceptionMessage('The integration with ID "1" doesn\'t exist.');

        $this->_integrationMock->expects($this->any())->method('getId')->willReturn(null);
        $this->_integrationMock->expects($this->once())->method('load')->willReturnSelf();
        $this->_integrationMock->expects($this->never())->method('delete');
        $this->_service->delete(self::VALUE_INTEGRATION_ID);
    }

    public function testFindByConsumerId()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getData'
        )->willReturn(
            $this->_integrationData
        );

        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_CONSUMER_ID,
            'consumer_id'
        )->willReturn(
            $this->_integrationMock
        );

        $integration = $this->_service->findByConsumerId(self::VALUE_INTEGRATION_CONSUMER_ID);
        $this->assertEquals($this->_integrationData[Integration::NAME], $integration->getData()[Integration::NAME]);
    }

    public function testFindByConsumerIdNotFound()
    {
        $this->_emptyIntegrationMock->expects($this->any())->method('getData')->willReturn(null);

        $this->_integrationMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            self::VALUE_INTEGRATION_CONSUMER_ID,
            'consumer_id'
        )->willReturn(
            $this->_emptyIntegrationMock
        );

        $integration = $this->_service->findByConsumerId(1);
        $this->assertNull($integration->getData());
    }

    /**
     * Set valid integration data
     */
    private function _setValidIntegrationData()
    {
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getName'
        )->willReturn(
            self::VALUE_INTEGRATION_NAME
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getEmail'
        )->willReturn(
            self::VALUE_INTEGRATION_EMAIL
        );
        $this->_integrationMock->expects(
            $this->any()
        )->method(
            'getEndpoint'
        )->willReturn(
            self::VALUE_INTEGRATION_ENDPOINT
        );
    }

    /**
     * Create mock integration
     *
     * @param string $name
     * @param int $integrationId
     * @return mixed
     */
    private function _getAnotherIntegrationMock(
        $name = self::VALUE_INTEGRATION_NAME,
        $integrationId = self::VALUE_INTEGRATION_ID
    ) {
        $integrationMock = $this->getMockBuilder(
            \Magento\Integration\Model\Integration::class
        )->disableOriginalConstructor()->setMethods(
            [
                'getData',
                'getId',
                'getName',
                'getEmail',
                'getEndpoint',
                'load',
                'loadByName',
                'save',
                'delete',
                '__wakeup',
            ]
        )->getMock();
        $integrationMock->expects($this->any())->method('getId')->willReturn($integrationId);
        $integrationMock->expects($this->any())->method('getName')->willReturn($name);
        $integrationMock->expects(
            $this->any()
        )->method(
            'getEmail'
        )->willReturn(
            self::VALUE_INTEGRATION_EMAIL
        );
        $integrationMock->expects(
            $this->any()
        )->method(
            'getEndpoint'
        )->willReturn(
            self::VALUE_INTEGRATION_ENDPOINT
        );
        return $integrationMock;
    }
}
