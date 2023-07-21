<?php declare(strict_types=1);
/**
 * Test for \Magento\Integration\Model\IntegrationService
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Model;

use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\IntegrationFactory;
use Magento\Integration\Model\IntegrationService;
use Magento\Integration\Model\Oauth\Consumer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IntegrationServiceTest extends TestCase
{
    const VALUE_INTEGRATION_ID = 1;
    const VALUE_INTEGRATION_NAME = 'Integration Name';
    const VALUE_INTEGRATION_ANOTHER_NAME = 'Another Integration Name';
    const VALUE_INTEGRATION_EMAIL = 'test@magento.com';
    const VALUE_INTEGRATION_SETUP_BACKEND = 0;
    const VALUE_INTEGRATION_ENDPOINT = 'http://magento.ll/endpoint';
    const VALUE_INTEGRATION_CONSUMER_ID = 1;

    /**
     * @var MockObject
     */
    private $_integrationFactory;

    /**
     * @var MockObject
     */
    private $_integrationMock;

    /**
     * @var MockObject
     */
    private $_emptyIntegrationMock;

    /**
     * @var IntegrationService
     */
    private $_service;

    /**
     * @var array
     */
    private $_integrationData;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_integrationFactory = $this->getMockBuilder(IntegrationFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->_integrationMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getId', 'load', 'save', 'delete', '__wakeup'])
            ->addMethods(['getName', 'getEmail', 'getEndpoint', 'loadByName'])
            ->getMock();
        $this->_integrationData = [
            Integration::ID => self::VALUE_INTEGRATION_ID,
            Integration::NAME => self::VALUE_INTEGRATION_NAME,
            Integration::EMAIL => self::VALUE_INTEGRATION_EMAIL,
            Integration::ENDPOINT => self::VALUE_INTEGRATION_ENDPOINT,
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
            OauthServiceInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $oauthConsumer = $this->getMockBuilder(
            Consumer::class
        )->disableOriginalConstructor()
            ->getMock();
        $oauthConsumerHelper->expects(
            $this->any()
        )->method(
            'createConsumer'
        )->willReturn(
            $oauthConsumer
        );
        $oauthConsumerHelper->expects($this->any())->method('loadConsumer')->willReturn($oauthConsumer);

        $this->_service = new IntegrationService(
            $this->_integrationFactory,
            $oauthConsumerHelper
        );
        $this->_emptyIntegrationMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getId', 'load', 'save', 'delete', '__wakeup'])
            ->addMethods(['getName', 'getEmail', 'getEndpoint', 'loadByName'])
            ->getMock();
        $this->_emptyIntegrationMock->expects($this->any())->method('getId')->willReturn(null);
    }

    /**
     * @return void
     */
    public function testCreateSuccess(): void
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
     * @return void
     */
    public function testCreateIntegrationAlreadyExistsException(): void
    {
        $this->expectException('Magento\Framework\Exception\IntegrationException');
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

    /**
     * @return void
     */
    public function testUpdateSuccess(): void
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
        $this->_integrationMock
            ->method('load')
            ->with(self::VALUE_INTEGRATION_ID)
            ->willReturn($this->_integrationMock);
        $this->_integrationMock->expects($this->once())->method('save')->willReturnSelf();
        $this->_setValidIntegrationData();
        $integrationData = $this->_service->update($this->_integrationData)->getData();
        $this->assertEquals($this->_integrationData, $integrationData);
    }

    /**
     * @return void
     */
    public function testUpdateSuccessNameChanged(): void
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
     * @return void
     */
    public function testUpdateException(): void
    {
        $this->expectException('Magento\Framework\Exception\IntegrationException');
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

    /**
     * @return void
     */
    public function testGet(): void
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
     * @return void
     */
    public function testGetException(): void
    {
        $this->expectException('Magento\Framework\Exception\IntegrationException');
        $this->expectExceptionMessage('The integration with ID "1" doesn\'t exist.');
        $this->_integrationMock->expects($this->any())->method('getId')->willReturn(null);
        $this->_integrationMock->expects($this->once())->method('load')->willReturnSelf();
        $this->_integrationMock->expects($this->never())->method('save');
        $this->_service->get(self::VALUE_INTEGRATION_ID)->getData();
    }

    /**
     * @return void
     */
    public function testFindByName(): void
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

    /**
     * @return void
     */
    public function testFindByNameNotFound(): void
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

    /**
     * @return void
     */
    public function testDelete(): void
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
     * @return void
     */
    public function testDeleteException(): void
    {
        $this->expectException('Magento\Framework\Exception\IntegrationException');
        $this->expectExceptionMessage('The integration with ID "1" doesn\'t exist.');
        $this->_integrationMock->expects($this->any())->method('getId')->willReturn(null);
        $this->_integrationMock->expects($this->once())->method('load')->willReturnSelf();
        $this->_integrationMock->expects($this->never())->method('delete');
        $this->_service->delete(self::VALUE_INTEGRATION_ID);
    }

    /**
     * @return void
     */
    public function testFindByConsumerId(): void
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

    /**
     * @return void
     */
    public function testFindByConsumerIdNotFound(): void
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
     *
     * @return void
     */
    private function _setValidIntegrationData(): void
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
     *
     * @return mixed
     */
    private function _getAnotherIntegrationMock(
        string $name = self::VALUE_INTEGRATION_NAME,
        int $integrationId = self::VALUE_INTEGRATION_ID
    ) {
        $integrationMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getId', 'load', 'save', 'delete', '__wakeup'])
            ->addMethods(['getName', 'getEmail', 'getEndpoint', 'loadByName'])->getMock();
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
