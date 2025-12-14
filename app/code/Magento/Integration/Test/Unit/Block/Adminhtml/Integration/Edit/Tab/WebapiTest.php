<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Integration\Edit\Tab;

use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Webapi;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;
use Magento\Integration\Helper\Data;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Model\IntegrationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class WebapiTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Webapi
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

        // Mock JsonHelper that Backend blocks need
        $jsonHelperMock = $this->getMockBuilder(JsonHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock ObjectManager to return mocked dependencies
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->method('get')
            ->willReturn($jsonHelperMock);

        // Set global ObjectManager instance for Backend blocks
        AppObjectManager::setInstance($objectManagerMock);

        $this->registry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rootResource = $this->getMockBuilder(RootResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->aclResourceProvider = $this->createMock(ProviderInterface::class);

        $this->integrationHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->integrationService = $this->getMockBuilder(IntegrationService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array<string, mixed>|null $integrationData
     * @param bool $expectedValue
     */
    #[DataProvider('canShowTabProvider')]
    public function testCanShowTab(?array $integrationData, bool $expectedValue): void
    {
        $this->webapiBlock = $this->getWebapiBlock($integrationData);
        $this->assertEquals($expectedValue, $this->webapiBlock->canShowTab());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function canShowTabProvider(): array
    {
        return [
            'null data' => [
                'integrationData' => null,
                'expectedValue' => true
            ],
            'empty integration data' => [
                'integrationData' => [],
                'expectedValue' => true
            ],
            'manual integration data' => [
                'integrationData' => [Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_MANUAL],
                'expectedValue' => true
            ],
            'config integration data' => [
                'integrationData' => [Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_CONFIG],
                'expectedValue' => false
            ]
        ];
    }

    public function testIsHidden(): void
    {
        $this->webapiBlock = $this->getWebapiBlock();
        $this->assertFalse($this->webapiBlock->isHidden());
    }

    /**
     * @param int|string $rootResourceId
     * @param array<string, mixed> $integrationData
     * @param array<int> $selectedResources
     * @param bool $expectedValue
     */
    #[DataProvider('isEverythingAllowedProvider')]
    public function testIsEverythingAllowed(
        int|string $rootResourceId,
        array $integrationData,
        array $selectedResources,
        bool $expectedValue
    ): void {
        $this->webapiBlock = $this->getWebapiBlock($integrationData, $selectedResources);
        $this->rootResource->expects($this->once())
            ->method('getId')
            ->willReturn($rootResourceId);
        $this->assertEquals($expectedValue, $this->webapiBlock->isEverythingAllowed());
    }

    /**
     * @return array<string, array{string|int, array<string, mixed>, array<int>, bool}>
     */
    public static function isEverythingAllowedProvider(): array
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

    public function testGetTree(): void
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
     * @param int|string $rootResourceId
     * @param array<string, mixed> $savedData
     * @param bool $expectedValue
     */
    #[DataProvider('isEverythingAllowedWithSavedFromDataProvider')]
    public function testIsEverythingAllowedWithSavedFromData(
        int|string $rootResourceId,
        array $savedData,
        bool $expectedValue
    ): void {
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
     * @return array<string, array{string|int, array<string, mixed>, bool}>
     */
    public static function isEverythingAllowedWithSavedFromDataProvider(): array
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
     * @param array<string, mixed>|null $integrationData
     * @param array<int> $selectedResources
     * @return Webapi
     */
    private function getWebapiBlock(?array $integrationData = [], array $selectedResources = []): Webapi
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
            ->method('registry')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [IntegrationController::REGISTRY_KEY_CURRENT_RESOURCE] => false,
                [IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION] => $integrationData
            });

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
