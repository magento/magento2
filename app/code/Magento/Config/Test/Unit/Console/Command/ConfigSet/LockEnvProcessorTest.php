<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\LockProcessor;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * Test for LockProcessor.
 *
 * @see LockProcessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LockEnvProcessorTest extends TestCase
{
    /**
     * @var LockProcessor
     */
    private $model;

    /**
     * @var PreparedValueFactory|Mock
     */
    private $preparedValueFactory;

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
     * @var Value|Mock
     */
    private $valueMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->preparedValueFactory = $this->getMockBuilder(PreparedValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigWriterMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configPathResolver = $this->getMockBuilder(ConfigPathResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->setMethods(['validateBeforeSave', 'beforeSave', 'setValue', 'getValue', 'afterSave'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new LockProcessor(
            $this->preparedValueFactory,
            $this->deploymentConfigWriterMock,
            $this->arrayManagerMock,
            $this->configPathResolver,
            ConfigFilePool::APP_ENV
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
        $this->preparedValueFactory->expects($this->once())
            ->method('create')
            ->with($path, $value, $scope, $scopeCode)
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
                    ConfigFilePool::APP_ENV => [
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

    public function testProcessNotReadableFs()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Filesystem is not writable.');
        $path = 'test/test/test';
        $value = 'value';

        $this->preparedValueFactory->expects($this->once())
            ->method('create')
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

    public function testCustomException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid values');
        $path = 'test/test/test';
        $value = 'value';

        $this->configPathResolver->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');
        $this->preparedValueFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->valueMock);
        $this->arrayManagerMock->expects($this->never())
            ->method('set');
        $this->valueMock->expects($this->once())
            ->method('getValue');
        $this->valueMock->expects($this->once())
            ->method('afterSave')
            ->willThrowException(new \Exception('Invalid values'));
        $this->deploymentConfigWriterMock->expects($this->never())
            ->method('saveConfig');

        $this->model->process($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
    }
}
