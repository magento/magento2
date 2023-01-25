<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Webapi;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Helper\Data;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Model\IntegrationService;
use PHPUnit\Framework\TestCase;

class WebapiTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Info
     */
    private $webapiBlock;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RootResource
     */
    private $rootResource;

    /**
     * @var ProviderInterface
     */
    private $aclResourceProvider;

    /**
     * @var Data
     */
    private $integrationHelper;

    /**
     * @var IntegrationService
     */
    private $integrationService;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rootResource = $this->getMockBuilder(RootResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->aclResourceProvider = $this->getMockBuilder(ProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->integrationHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->integrationService = $this->getMockBuilder(IntegrationService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $integrationData
     * @param bool $expectedValue
     * @dataProvider canShowTabProvider
     */
    public function testCanShowTab($integrationData, $expectedValue)
    {
        $this->webapiBlock = $this->getWebapiBlock($integrationData);
        $this->assertEquals($expectedValue, $this->webapiBlock->canShowTab());
    }

    /**
     * @return array
     */
    public function canShowTabProvider()
    {
        return [
            'null data' => [
                null,
                true
            ],
            'empty integration data' => [
                [],
                true
            ],
            'manual integration data' => [
                Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_MANUAL,
                true
            ],
            'config integration data' => [
                [Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_CONFIG],
                false
            ],
        ];
    }

    public function testIsHidden()
    {
        $this->webapiBlock = $this->getWebapiBlock();
        $this->assertFalse($this->webapiBlock->isHidden());
    }

    /**
     * @param string $rootResourceId
     * @param array $integrationData
     * @param array $selectedResources
     * @param bool $expectedValue
     * @dataProvider isEverythingAllowedProvider
     */
    public function testIsEverythingAllowed($rootResourceId, $integrationData, $selectedResources, $expectedValue)
    {
        $this->webapiBlock = $this->getWebapiBlock($integrationData, $selectedResources);
        $this->rootResource->expects($this->once())
            ->method('getId')
            ->willReturn($rootResourceId);
        $this->assertEquals($expectedValue, $this->webapiBlock->isEverythingAllowed());
    }

    /**
     * @return array
     */
    public function isEverythingAllowedProvider()
    {
        return [
            'root resource in array' => [
                2,
                ['integration_id' => 1],
                [1, 2, 3],
                true
            ],
            'root resource not in array' => [
                1,
                ['integration_id' => 1],
                [2, 3, 4],
                false
            ],
            'no integration data' => [
                1,
                [],
                [],
                false
            ],
        ];
    }

    public function testGetTree()
    {
        $this->webapiBlock = $this->getWebapiBlock();
        $resources = [
            ['id' => 'Magento_Backend::admin', 'children' => ['resource1', 'resource2', 'resource3']],
            ['id' => 'Invalid_Node', 'children' => ['resource4', 'resource5', 'resource6']]
        ];
        $this->aclResourceProvider->expects($this->once())
            ->method('getAclResources')
            ->willReturn($resources);
        $rootArray = "rootArrayValue";
        $this->integrationHelper->expects($this->once())
            ->method('mapResources')
            ->with(['resource1', 'resource2', 'resource3'])
            ->willReturn($rootArray);
        $this->assertEquals($rootArray, $this->webapiBlock->getTree());
    }

    /**
     * @param string $rootResourceId
     * @param array $savedData
     * @param bool $expectedValue
     * @dataProvider isEverythingAllowedWithSavedFromDataProvider
     */
    public function testIsEverythingAllowedWithSavedFromData($rootResourceId, $savedData, $expectedValue)
    {
        $this->registry->expects($this->once())
            ->method('registry')->with(IntegrationController::REGISTRY_KEY_CURRENT_RESOURCE)
            ->willReturn($savedData);

        $this->rootResource->expects($this->any())
            ->method('getId')
            ->willReturn($rootResourceId);

        $this->webapiBlock = $this->getWebapiBlock();

        $this->assertEquals($expectedValue, $this->webapiBlock->isEverythingAllowed());
    }

    /**
     * @return array
     */
    public function isEverythingAllowedWithSavedFromDataProvider()
    {
        return [
            'root resource in array' => [
                2,
                ['all_resources' => 0, 'resource' => [2, 3]],
                true
            ],
            'root resource not in array' => [
                2,
                ['all_resources' => 1],
                true
            ]
        ];
    }

    /**
     * @param array $integrationData
     * @param array $selectedResources
     * @return Webapi
     */
    private function getWebapiBlock($integrationData = [], array $selectedResources = [])
    {
        if ($integrationData) {
            if (isset($integrationData['integration_id'])) {
                $this->integrationService->expects($this->once())
                    ->method('getSelectedResources')
                    ->with($integrationData['integration_id'])
                    ->willReturn($selectedResources);
            }
        }

        $this->registry->expects($this->any())
            ->method('registry')->withConsecutive(
                [IntegrationController::REGISTRY_KEY_CURRENT_RESOURCE],
                [IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION],
                [IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION]
            )
            ->willReturnOnConsecutiveCalls(false, $integrationData, $integrationData);

        return $this->objectManager->getObject(
            Webapi::class,
            [
                'registry' => $this->registry,
                'rootResource' => $this->rootResource,
                'aclResourceProvider' => $this->aclResourceProvider,
                'integrationData' => $this->integrationHelper,
                'integrationService' => $this->integrationService,
            ]
        );
    }
}
