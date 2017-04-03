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

    /**
     * @param array $deploymentConfigIsAvailable
     * @param array $structureGetElement
     * @param array $field
     * @param array $valueFactory
     * @dataProvider createDataProvider
     */
    public function testCreate(
        array $deploymentConfigIsAvailable,
        array $structureGetElement,
        array $field,
        array $valueFactory
    ) {
        $path = '/some/path';
        $value = 'someValue';
        $scope = 'someScope';
        $scopeCode = 'someScopeCode';
        $this->structureFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->structureMock);
        $this->deploymentConfigMock
            ->expects($this->once())
            ->method('isAvailable')
            ->willReturn($deploymentConfigIsAvailable['return']);
        $this->structureMock
            ->expects($structureGetElement['expects'])
            ->method('getElement')
            ->willReturn($this->fieldMock);
        $this->fieldMock
            ->expects($field['hasBackendModel']['expects'])
            ->method('hasBackendModel')
            ->willReturn($field['hasBackendModel']['return']);
        $this->fieldMock
            ->expects($field['getBackendModel']['expects'])
            ->method('getBackendModel')
            ->willReturn($this->valueMock);
        $this->valueFactoryMock->expects($valueFactory['expects'])
            ->method('create')
            ->willReturn($this->valueMock);
        $this->valueMock->expects($this->once())
            ->method('setPath')
            ->with($path)
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
            $this->valueBuilder->create($path, $value, $scope, $scopeCode)
        );
    }

    public function createDataProvider()
    {
        return [
            [
                'deploymentConfigIsAvailable' => ['return' => false],
                'structureGetElement' => ['expects' => $this->never()],
                'field' => [
                    'hasBackendModel' => [
                        'expects' => $this->never(),
                        'return' => true
                    ],
                    'getBackendModel' => ['expects' => $this->never()]
                ],
                'valueFactory' => ['expects' => $this->once()]
            ],
            [
                'deploymentConfigIsAvailable' => ['return' => true],
                'structureGetElement' => ['expects' => $this->once()],
                'field' => [
                    'hasBackendModel' => [
                        'expects' => $this->once(),
                        'return' => true
                    ],
                    'getBackendModel' => ['expects' => $this->once()]
                ],
                'valueFactory' => ['expects' => $this->never()]
            ],
            [
                'deploymentConfigIsAvailable' => ['return' => true],
                'structureGetElement' => ['expects' => $this->once()],
                'field' => [
                    'hasBackendModel' => [
                        'expects' => $this->once(),
                        'return' => false
                    ],
                    'getBackendModel' => ['expects' => $this->never()]
                ],
                'valueFactory' => ['expects' => $this->once()]
            ],
        ];
    }
}
