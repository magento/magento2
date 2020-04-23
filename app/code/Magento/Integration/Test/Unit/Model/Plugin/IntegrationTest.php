<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Plugin;

use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Api\AuthorizationServiceInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\ConsolidatedConfig;
use Magento\Integration\Model\Integration;
use Magento\Integration\Model\IntegrationConfig;
use Magento\Integration\Model\IntegrationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Integration\Model\Plugin\Integration
 */
class IntegrationTest extends TestCase
{
    /**
     * API setup plugin
     *
     * @var \Magento\Integration\Model\Plugin\Integration
     */
    protected $integrationPlugin;

    /**
     * @var IntegrationServiceInterface|MockObject
     */
    protected $subjectMock;

    /**
     * @var  AclRetriever|MockObject
     */
    protected $aclRetrieverMock;

    /**
     * @var AuthorizationServiceInterface|MockObject
     */
    protected $integrationAuthServiceMock;

    /**
     * @var IntegrationConfig|MockObject
     */
    protected $integrationConfigMock;

    /**
     * @var ConsolidatedConfig|MockObject
     */
    protected $consolidatedConfigMock;

    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(IntegrationService::class);
        $this->integrationAuthServiceMock = $this->createPartialMock(
            AuthorizationServiceInterface::class,
            ['removePermissions', 'grantAllPermissions', 'grantPermissions']
        );
        $this->aclRetrieverMock = $this->createPartialMock(
            AclRetriever::class,
            ['getAllowedResourcesByUser']
        );
        $this->integrationConfigMock = $this->getMockBuilder(IntegrationConfig::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->consolidatedConfigMock = $this->getMockBuilder(ConsolidatedConfig::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);

        $this->integrationPlugin = $objectManagerHelper->getObject(
            \Magento\Integration\Model\Plugin\Integration::class,
            [
                'integrationAuthorizationService' => $this->integrationAuthServiceMock,
                'aclRetriever' => $this->aclRetrieverMock,
                'integrationConfig' => $this->integrationConfigMock,
                'consolidatedConfig' => $this->consolidatedConfigMock
            ]
        );
    }

    public function testAfterDelete()
    {
        $integrationId = 1;
        $integrationsData = [
            Integration::ID => $integrationId,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::SETUP_TYPE => 1,
        ];

        $this->integrationAuthServiceMock->expects($this->once())
            ->method('removePermissions')
            ->with($integrationId);
        $this->integrationPlugin->afterDelete($this->subjectMock, $integrationsData);
    }

    public function testAfterCreateAllResources()
    {
        $integrationId = 1;
        $integrationModelMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $integrationModelMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($integrationId);
        $integrationModelMock->expects($this->once())
            ->method('getData')
            ->with('all_resources')
            ->willReturn(1);

        $this->integrationAuthServiceMock->expects($this->once())
            ->method('grantAllPermissions')
            ->with($integrationId);

        $this->integrationPlugin->afterCreate($this->subjectMock, $integrationModelMock);
    }

    public function testAfterCreateSomeResources()
    {
        $integrationId = 1;
        $integrationModelMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $integrationModelMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($integrationId);
        $integrationModelMock->expects($this->at(2))
            ->method('getData')
            ->with('all_resources')
            ->willReturn(null);
        $integrationModelMock->expects($this->at(3))
            ->method('getData')
            ->with('resource')
            ->willReturn(['testResource']);
        $integrationModelMock->expects($this->at(5))
            ->method('getData')
            ->with('resource')
            ->willReturn(['testResource']);

        $this->integrationAuthServiceMock->expects($this->once())
            ->method('grantPermissions')
            ->with($integrationId, ['testResource']);

        $this->integrationPlugin->afterCreate($this->subjectMock, $integrationModelMock);
    }

    public function testAfterCreateNoResource()
    {
        $integrationId = 1;
        $integrationModelMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $integrationModelMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($integrationId);
        $integrationModelMock->expects($this->at(2))
            ->method('getData')
            ->with('all_resources')
            ->willReturn(null);
        $integrationModelMock->expects($this->at(3))
            ->method('getData')
            ->with('resource')
            ->willReturn(null);

        $this->integrationAuthServiceMock->expects($this->once())
            ->method('grantPermissions')
            ->with($integrationId, []);

        $this->integrationPlugin->afterCreate($this->subjectMock, $integrationModelMock);
    }

    public function testAfterUpdateAllResources()
    {
        $integrationId = 1;
        $integrationModelMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $integrationModelMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($integrationId);
        $integrationModelMock->expects($this->once())
            ->method('getData')
            ->with('all_resources')
            ->willReturn(1);

        $this->integrationAuthServiceMock->expects($this->once())
            ->method('grantAllPermissions')
            ->with($integrationId);

        $this->integrationPlugin->afterUpdate($this->subjectMock, $integrationModelMock);
    }

    public function testAfterGet()
    {
        $integrationId = 1;
        $integrationModelMock = $this->getMockBuilder(Integration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $integrationModelMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($integrationId);
        $integrationModelMock->expects($this->once())
            ->method('setData')
            ->with('resource', ['testResource']);
        $deprecatedIntegrationsData = [
            Integration::ID => $integrationId,
            Integration::NAME => 'TestIntegration1',
            Integration::EMAIL => 'test-integration1@magento.com',
            Integration::ENDPOINT => 'http://endpoint.com',
            Integration::SETUP_TYPE => 1,
            'resource' => ['testResource']
        ];
        $consolidatedIntegrationsData = [
            Integration::ID => 2,
            Integration::NAME => 'TestIntegration2',
            Integration::EMAIL => 'test-integration2@magento.com',
            Integration::ENDPOINT => 'http://endpoint2.com',
            Integration::SETUP_TYPE => 1,
            'resource' => ['testResource']
        ];
        $this->integrationConfigMock->method('getIntegrations')->willReturn($deprecatedIntegrationsData);
        $this->consolidatedConfigMock->method('getIntegrations')->willReturn($consolidatedIntegrationsData);

        $this->aclRetrieverMock->expects($this->once())
            ->method('getAllowedResourcesByUser')
            ->with(UserContextInterface::USER_TYPE_INTEGRATION, $integrationId)
            ->willReturn(['testResource']);

        $this->integrationPlugin->afterGet($this->subjectMock, $integrationModelMock);
    }
}
