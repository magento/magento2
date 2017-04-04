<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\DeploymentConfig;

/**
 * @inheritdoc
 */
class PreparedValueFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var StructureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureFactoryMock;

    /**
     * @var ValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $valueFactoryMock;

    /**
     * @var Value|\PHPUnit_Framework_MockObject_MockObject
     */
    private $valueMock;

    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var Field|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldMock;

    /**
     * @var PreparedValueFactory
     */
    private $valueBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureFactoryMock = $this->getMockBuilder(StructureFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->valueFactoryMock = $this->getMockBuilder(ValueFactory::class)
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

        $this->valueBuilder = new PreparedValueFactory(
            $this->deploymentConfigMock,
            $this->structureFactoryMock,
            $this->valueFactoryMock
        );
    }

    public function testCreateWhenDeploymentConfigIsNotAvailable()
    {
        $configPath = '/some/path';
        $value = 'someValue';
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';
        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->structureMock);
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);
        $this->valueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->valueMock);
        $this->valueMock->expects($this->once())
            ->method('setPath')
            ->with($configPath)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScope')
            ->with($scope)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScopeId')
            ->with($scopeCode)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();

        $this->assertInstanceOf(
            Value::class,
            $this->valueBuilder->create($configPath, $value, $scope, $scopeCode)
        );
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param string $structurePath
     * @param string $customPath
     * @param string $expectedPath
     */
    public function testCreateAndSetDataToBackendModel(
        $structurePath,
        $customPath,
        $expectedPath
    ) {
        $value = 'someValue';
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';
        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->structureMock);
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->structureMock
            ->expects($this->once())
            ->method('getElement')
            ->willReturn($this->fieldMock);
        $this->fieldMock
            ->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(true);
        $this->fieldMock
            ->expects($this->once())
            ->method('getBackendModel')
            ->willReturn($this->valueMock);
        $this->valueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->valueMock);
        $this->fieldMock->expects($this->once())
            ->method('getConfigPath')
            ->willReturn($customPath);
        $this->valueMock->expects($this->once())
            ->method('setPath')
            ->with($expectedPath)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScope')
            ->with($scope)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScopeId')
            ->with($scopeCode)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();

        $this->assertInstanceOf(
            Value::class,
            $this->valueBuilder->create($structurePath, $value, $scope, $scopeCode)
        );
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            'withCustomConfigPath' => [
                'structurePath' => '/some/path',
                'customPath' => 'customPath',
                'expectedPath' => 'customPath'
            ],
            'withOutCustomConfigPath' => [
                'structurePath' => '/some/path',
                'customPath' => null,
                'expectedPath' => '/some/path'
            ],
        ];
    }

    public function testCreateAndSetDataWithNewBackendModel()
    {
        $structurePath = '/some/path';
        $value = 'someValue';
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';
        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->structureMock);
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->structureMock
            ->expects($this->once())
            ->method('getElement')
            ->willReturn($this->fieldMock);
        $this->fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(false);
        $this->valueFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->valueMock);
        $this->valueMock->expects($this->once())
            ->method('setPath')
            ->with($structurePath)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScope')
            ->with($scope)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setScopeId')
            ->with($scopeCode)
            ->willReturnSelf();
        $this->valueMock->expects($this->once())
            ->method('setValue')
            ->with($value)
            ->willReturnSelf();

        $this->assertSame(
            $this->valueMock,
            $this->valueBuilder->create($structurePath, $value, $scope, $scopeCode)
        );
    }
}
