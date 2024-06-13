<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model;

use Magento\Config\Model\Config\BackendFactory;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolver;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Store\Model\ScopeTypeNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * @inheritdoc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class PreparedValueFactoryTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var StructureFactory|Mock
     */
    private $structureFactoryMock;

    /**
     * @var BackendFactory|Mock
     */
    private $valueFactoryMock;

    /**
     * @var Value|Mock
     */
    private $valueMock;

    /**
     * @var Structure|Mock
     */
    private $structureMock;

    /**
     * @var Field|Mock
     */
    private $fieldMock;

    /**
     * @var ScopeConfigInterface|Mock
     */
    private $configMock;

    /**
     * @var ScopeResolverPool|Mock
     */
    private $scopeResolverPoolMock;

    /**
     * @var ScopeResolver|Mock
     */
    private $scopeResolverMock;

    /**
     * @var ScopeInterface|Mock
     */
    private $scopeMock;

    /**
     * @var ScopeTypeNormalizer|Mock
     */
    private $scopeTypeNormalizer;

    /**
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->structureFactoryMock = $this->createPartialMock(
            StructureFactory::class,
            ['create']
        );
        $this->valueFactoryMock = $this->createPartialMock(
            BackendFactory::class,
            ['create']
        );
        $this->structureMock = $this->createMock(Structure::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->valueMock = $this->createPartialMockWithReflection(
            Value::class,
            [
                'setPath', 'setScope', 'setScopeId', 'setValue', 'setField',
                'setGroupId', 'setFieldConfig', 'setScopeCode'
            ]
        );
        $this->configMock = $this->createMock(ScopeConfigInterface::class);
        $this->scopeResolverPoolMock = $this->createMock(ScopeResolverPool::class);
        $this->scopeResolverMock = $this->createMock(ScopeResolver::class);
        $this->scopeMock = $this->createMock(ScopeInterface::class);
        $this->scopeTypeNormalizer = $this->createMock(ScopeTypeNormalizer::class);

        $this->preparedValueFactory = new PreparedValueFactory(
            $this->scopeResolverPoolMock,
            $this->structureFactoryMock,
            $this->valueFactoryMock,
            $this->configMock,
            $this->scopeTypeNormalizer
        );
    }

    /**
     * @param string $path
     * @param string|null $configPath
     * @param string $value
     * @param string $scope
     * @param string|int|null $scopeCode
     * @param int $scopeId
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(
        $path,
        $configPath,
        $value,
        $scope,
        $scopeCode,
        $scopeId
    ) {
        $groupPath = 'some/group';
        $groupId = 'some_group';
        $fieldId = 'some_field';
        $fieldData = ['backend_model' => 'some_model'];

        if (ScopeInterface::SCOPE_DEFAULT !== $scope) {
            $this->scopeResolverPoolMock->expects($this->once())
                ->method('get')
                ->with($scope)
                ->willReturn($this->scopeResolverMock);
            $this->scopeResolverMock->expects($this->once())
                ->method('getScope')
                ->with($scopeCode)
                ->willReturn($this->scopeMock);
            $this->scopeMock->expects($this->once())
                ->method('getId')
                ->willReturn($scopeId);
        }
        /** @var Group|Mock $groupMock */
        $groupMock = $this->createMock(Group::class);
        $groupMock->expects($this->once())
            ->method('getId')
            ->willReturn($groupId);

        $this->scopeTypeNormalizer->expects($this->once())
            ->method('normalize')
            ->with($scope, true)
            ->willReturnArgument(0);
        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->structureMock);
        $this->structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->willReturn($this->fieldMock);
        $this->structureMock->expects($this->once())
            ->method('getElement')
            ->with($groupPath)
            ->willReturn($groupMock);
        $this->fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(true);
        $this->fieldMock
            ->method('getConfigPath')
            ->willReturn($configPath);
        $this->fieldMock
            ->method('getId')
            ->willReturn($fieldId);
        $this->fieldMock
            ->method('getData')
            ->willReturn($fieldData);
        $this->fieldMock->expects($this->once())
            ->method('getGroupPath')
            ->willReturn($groupPath);
        $this->valueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->valueMock);
        $this->valueMock->expects($this->once())
            ->method('setPath')
            ->with($configPath ?: $path)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScope')
            ->with($scope)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScopeId')
            ->with($scopeId)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScopeCode')
            ->with($scopeCode)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setField')
            ->with($fieldId)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setGroupId')
            ->with($groupId)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setFieldConfig')
            ->with($fieldData)
            ->willReturnSelf();

        $this->assertInstanceOf(
            Value::class,
            $this->preparedValueFactory->create($path, $value, $scope, $scopeCode)
        );
    }

    /**
     * @return array
     */
    public static function createDataProvider()
    {
        return [
            'standard flow' => [
                '/some/path',
                null,
                'someValue',
                'someScope',
                'someScopeCode',
                1,
            ],
            'standard flow with custom config path' => [
                '/some/path',
                '/custom/config_path',
                'someValue',
                'someScope',
                'someScopeCode',
                1,
            ],
            'default scope flow' => [
                '/some/path',
                null,
                'someValue',
                ScopeInterface::SCOPE_DEFAULT,
                null,
                0,
            ],
            'website scope flow' => [
                '/some/path',
                null,
                'someValue',
                StoreScopeInterface::SCOPE_WEBSITES,
                null,
                0,
            ],
            'websites scope flow' => [
                '/some/path',
                null,
                'someValue',
                StoreScopeInterface::SCOPE_WEBSITES,
                null,
                0,
            ],
            'store scope flow' => [
                '/some/path',
                null,
                'someValue',
                StoreScopeInterface::SCOPE_STORES,
                null,
                0,
            ],
            'stores scope flow' => [
                '/some/path',
                null,
                'someValue',
                StoreScopeInterface::SCOPE_STORES,
                null,
                0,
            ],
        ];
    }

    /**
     * @param string $path
     * @param string $scope
     * @param string|int|null $scopeCode
     */
    #[DataProvider('createNotInstanceOfValueDataProvider')]
    public function testCreateNotInstanceOfValue(
        $path,
        $scope,
        $scopeCode
    ) {
        $this->scopeResolverPoolMock->expects($this->never())
            ->method('get');
        $this->scopeResolverMock->expects($this->never())
            ->method('getScope');
        $this->scopeMock->expects($this->never())
            ->method('getId');

        $value = new \stdClass();

        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->structureMock);
        $this->structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->willReturn($this->fieldMock);
        $this->fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(false);
        $this->fieldMock->expects($this->never())
            ->method('getBackendModel');
        $this->valueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($value);
        $this->valueMock->expects($this->never())
            ->method('setPath');
        $this->valueMock->expects($this->never())
            ->method('setScope');
        $this->valueMock->expects($this->never())
            ->method('setScopeId');
        $this->valueMock->expects($this->never())
            ->method('setValue');

        $this->assertSame(
            $value,
            $this->preparedValueFactory->create($path, $value, $scope, $scopeCode)
        );
    }

    /**
     * @return array
     */
    public static function createNotInstanceOfValueDataProvider()
    {
        return [
            'standard flow' => [
                '/some/path',
                'someScope',
                'someScopeCode',
            ],
            'default scope flow' => [
                '/some/path',
                ScopeInterface::SCOPE_DEFAULT,
                null,
            ],
        ];
    }

    public function testCreateWithException()
    {
        $this->expectException('Magento\Framework\Exception\RuntimeException');
        $this->expectExceptionMessage('Some exception');
        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Some exception'));

        $this->preparedValueFactory->create('path', 'value', ScopeInterface::SCOPE_DEFAULT);
    }
}
