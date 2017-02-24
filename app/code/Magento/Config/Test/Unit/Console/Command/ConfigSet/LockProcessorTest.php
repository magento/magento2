<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\LockProcessor;
use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for LockProcessor.
 *
 * @see LockProcessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LockProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LockProcessor
     */
    private $model;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfigMock;

    /**
     * @var DeploymentConfig\Writer|Mock
     */
    private $deploymentConfigWriterMock;

    /**
     * @var ArrayManager|Mock
     */
    private $arrayManagerMock;

    /**
     * @var ConfigPathResolver|Mock
     */
    private $configPathResolver;

    /**
     * @var Structure|Mock
     */
    private $structureMock;

    /**
     * @var Value|Mock
     */
    private $valueMock;

    /**
     * @var ValueFactory|Mock
     */
    private $valueFactoryMock;

    /**
     * @var Field|Mock
     */
    private $fieldMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigWriterMock = $this->getMockBuilder(DeploymentConfig\Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configPathResolver = $this->getMockBuilder(ConfigPathResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueFactoryMock = $this->getMockBuilder(ValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldMock = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->setMethods(['validateBeforeSave', 'beforeSave', 'setValue', 'getValue', 'afterSave'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->structureMock->expects($this->any())
            ->method('getElement')
            ->willReturn($this->fieldMock);
        $this->deploymentConfigMock->expects($this->any())
            ->method('isAvailable')
            ->willReturn(true);
        $this->valueFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->valueMock);

        $this->model = new LockProcessor(
            $this->deploymentConfigMock,
            $this->deploymentConfigWriterMock,
            $this->arrayManagerMock,
            $this->configPathResolver,
            $this->structureMock,
            $this->valueFactoryMock
        );
    }

    /**
     * Tests process of lock flow.
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string|null $scopeCode
     * @dataProvider processDataProvider
     */
    public function testProcess($path, $value, $scope, $scopeCode)
    {
        $this->fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(true);
        $this->fieldMock->expects($this->once())
            ->method('getBackendModel')
            ->willReturn($this->valueMock);
        $this->configPathResolver->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');
        $this->arrayManagerMock->expects($this->once())
            ->method('set')
            ->with('system/default/test/test/test', [], $value)
            ->willReturn([
                'system' => [
                    'default' => [
                        'test' => [
                            'test' => [
                                'test' => $value
                            ]
                        ]
                    ]
                ]
            ]);
        $this->valueMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        $this->deploymentConfigWriterMock->expects($this->once())
            ->method('saveConfig')
            ->with(
                [
                    ConfigFilePool::APP_CONFIG => [
                        'system' => [
                            'default' => [
                                'test' => [
                                    'test' => [
                                        'test' => $value
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                false
            );
        $this->valueMock->expects($this->once())
            ->method('validateBeforeSave');
        $this->valueMock->expects($this->once())
            ->method('beforeSave');
        $this->valueMock->expects($this->once())
            ->method('afterSave');

        $this->model->process($path, $value, $scope, $scopeCode);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            ['test/test/test', 'value', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null],
            ['test/test/test', 'value', ScopeInterface::SCOPE_WEBSITE, 'base'],
            ['test/test/test', 'value', ScopeInterface::SCOPE_STORE, 'test'],
        ];
    }

    public function testProcessBackendModelNotExists()
    {
        $path = 'test/test/test';
        $value = 'value';

        $this->fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(false);
        $this->fieldMock->expects($this->never())
            ->method('getBackendModel');
        $this->configPathResolver->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');
        $this->arrayManagerMock->expects($this->once())
            ->method('set')
            ->with('system/default/test/test/test', [], $value)
            ->willReturn([
                'system' => [
                    'default' => [
                        'test' => [
                            'test' => [
                                'test' => $value
                            ]
                        ]
                    ]
                ]
            ]);
        $this->valueMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        $this->deploymentConfigWriterMock->expects($this->once())
            ->method('saveConfig')
            ->with(
                [
                    ConfigFilePool::APP_CONFIG => [
                        'system' => [
                            'default' => [
                                'test' => [
                                    'test' => [
                                        'test' => $value
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                false
            );

        $this->model->process($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Filesystem is not writable.
     */
    public function testProcessNotReadableFs()
    {
        $path = 'test/test/test';
        $value = 'value';

        $this->fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(true);
        $this->fieldMock->expects($this->once())
            ->method('getBackendModel')
            ->willReturn($this->valueMock);
        $this->valueMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        $this->configPathResolver->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');
        $this->arrayManagerMock->expects($this->once())
            ->method('set')
            ->with('system/default/test/test/test', [], $value)
            ->willReturn(null);
        $this->deploymentConfigWriterMock->expects($this->once())
            ->method('saveConfig')
            ->willThrowException(new FileSystemException(__('Filesystem is not writable.')));

        $this->model->process($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid values
     */
    public function testCustomException()
    {
        $path = 'test/test/test';
        $value = 'value';

        $this->fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(true);
        $this->fieldMock->expects($this->once())
            ->method('getBackendModel')
            ->willReturn($this->valueMock);
        $this->configPathResolver->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');
        $this->arrayManagerMock->expects($this->never())
            ->method('set');
        $this->valueMock->expects($this->never())
            ->method('getValue');
        $this->valueMock->expects($this->once())
            ->method('validateBeforeSave')
            ->willThrowException(new \Exception('Invalid values'));
        $this->deploymentConfigWriterMock->expects($this->never())
            ->method('saveConfig');

        $this->model->process($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
    }
}
