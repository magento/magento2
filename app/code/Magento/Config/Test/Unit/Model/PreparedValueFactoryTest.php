<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model;

use Magento\Config\Model\Config\BackendFactory;
use Magento\Config\Model\Config\PreparedValue\AdditionalData;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\Config\Value;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\ScopeInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Framework\App\ScopeResolver;
use Magento\Framework\App\ScopeResolverPool;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PreparedValueFactoryTest extends \PHPUnit_Framework_TestCase
{
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
     * @var AdditionalData|Mock
     */
    private $additionalDataMock;

    /**
     * @var PreparedValueFactory
     */
    private $preparedValueFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->structureFactoryMock = $this->getMockBuilder(StructureFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->valueFactoryMock = $this->getMockBuilder(BackendFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath', 'setScope', 'setScopeId', 'setValue'])
            ->getMock();
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->scopeResolverPoolMock = $this->getMockBuilder(ScopeResolverPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeResolverMock = $this->getMockBuilder(ScopeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $this->additionalDataMock = $this->getMockBuilder(AdditionalData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->preparedValueFactory = new PreparedValueFactory(
            $this->scopeResolverPoolMock,
            $this->structureFactoryMock,
            $this->valueFactoryMock,
            $this->configMock,
            $this->additionalDataMock
        );
    }

    /**
     * @param string $path
     * @param string|null $configPath
     * @param string $value
     * @param string $scope
     * @param string|int|null $scopeCode
     * @param int $scopeId
     * @dataProvider createDataProvider
     */
    public function testCreate(
        $path,
        $configPath,
        $value,
        $scope,
        $scopeCode,
        $scopeId
    ) {
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

        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->structureMock);
        $this->structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->willReturn($this->fieldMock);
        $this->fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(true);
        $this->fieldMock
            ->method('getConfigPath')
            ->willReturn($configPath);
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
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();
        $this->additionalDataMock->expects($this->once())
            ->method('apply')
            ->with($this->valueMock);

        $this->assertInstanceOf(
            Value::class,
            $this->preparedValueFactory->create($path, $value, $scope, $scopeCode)
        );
    }

    /**
     * @return array
     */
    public function createDataProvider()
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
                'someScope',
                'someScopeCode',
                1,
            ],
            'default scope flow' => [
                '/some/path',
                null,
                'someValue',
                ScopeInterface::SCOPE_DEFAULT,
                ScopeInterface::SCOPE_DEFAULT,
                null,
                0,
            ],
            'website scope flow' => [
                '/some/path',
                'someValue',
                StoreScopeInterface::SCOPE_WEBSITE,
                StoreScopeInterface::SCOPE_WEBSITES,
                null,
                0,
            ],
            'websites scope flow' => [
                '/some/path',
                'someValue',
                StoreScopeInterface::SCOPE_WEBSITES,
                StoreScopeInterface::SCOPE_WEBSITES,
                null,
                0,
            ],
            'store scope flow' => [
                '/some/path',
                'someValue',
                StoreScopeInterface::SCOPE_STORE,
                StoreScopeInterface::SCOPE_STORES,
                null,
                0,
            ],
            'stores scope flow' => [
                '/some/path',
                'someValue',
                StoreScopeInterface::SCOPE_STORES,
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
     * @dataProvider createDataProvider
     */
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
        $this->additionalDataMock->expects($this->never())
            ->method('apply');

        $this->assertSame(
            $value,
            $this->preparedValueFactory->create($path, $value, $scope, $scopeCode)
        );
    }

    /**
     * @return array
     */
    public function createNotInstanceOfValueDataProvider()
    {
        return [
            'standard flow' => [
                '/some/path',
                'someScope',
                'someScopeCode',
                1,
            ],
            'default scope flow' => [
                '/some/path',
                ScopeInterface::SCOPE_DEFAULT,
                null,
            ],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Some exception
     */
    public function testCreateWithException()
    {
        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Some exception'));

        $this->preparedValueFactory->create('path', 'value', ScopeInterface::SCOPE_DEFAULT);
    }
}
