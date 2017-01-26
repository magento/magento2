<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\LockProcessor;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ScopePathResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\ArrayManager;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Input\InputInterface;

/**
 * {@inheritdoc}
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LockProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LockProcessor
     */
    private $model;

    /**
     * @var DeploymentConfig|Mock
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
     * @var MetadataProcessor|Mock
     */
    private $metadataProcessorMock;

    /**
     * @var InputInterface|Mock
     */
    private $inputMock;

    /**
     * @var ScopePathResolver|Mock
     */
    private $scopePathResolverMock;

    /**
     * {@inheritdoc}
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
        $this->metadataProcessorMock = $this->getMockBuilder(MetadataProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->scopePathResolverMock = $this->getMockBuilder(ScopePathResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new LockProcessor(
            $this->deploymentConfigMock,
            $this->deploymentConfigWriterMock,
            $this->arrayManagerMock,
            $this->metadataProcessorMock,
            $this->scopePathResolverMock
        );
    }

    public function testProcess()
    {
        $path = 'test/test/test';
        $value = 'value';

        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value],
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_SCOPE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT],
                [ConfigSetCommand::OPTION_SCOPE_CODE, 'test_scope_code'],
                [ConfigSetCommand::OPTION_FORCE, false]
            ]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('system/default/test/test/test')
            ->willReturn(null);
        $this->metadataProcessorMock->expects($this->once())
            ->method('prepareValue')
            ->with($value, $path)
            ->willReturn($value);
        $this->scopePathResolverMock->expects($this->once())
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
                true
            );

        $this->model->process($this->inputMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Value is already locked.
     */
    public function testProcessToBeAlreadyLocked()
    {
        $path = 'test/test/test';
        $value = 'value';

        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value],
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_SCOPE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT],
                [ConfigSetCommand::OPTION_SCOPE_CODE, 'test_scope_code'],
                [ConfigSetCommand::OPTION_FORCE, false]
            ]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('system/default/test/test/test')
            ->willReturn([]);
        $this->scopePathResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');

        $this->model->process($this->inputMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Filesystem is not writable.
     */
    public function testProcessNotReadableFs()
    {
        $path = 'test/test/test';
        $value = 'value';

        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value],
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_SCOPE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT],
                [ConfigSetCommand::OPTION_SCOPE_CODE, 'test_scope_code'],
                [ConfigSetCommand::OPTION_FORCE, false]
            ]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with('system/default/test/test/test')
            ->willReturn(null);
        $this->metadataProcessorMock->expects($this->once())
            ->method('prepareValue')
            ->with($value, $path)
            ->willReturn($value);
        $this->scopePathResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');
        $this->arrayManagerMock->expects($this->once())
            ->method('set')
            ->with('system/default/test/test/test', [], $value)
            ->willReturn(null);
        $this->deploymentConfigWriterMock->expects($this->once())
            ->method('saveConfig')
            ->willThrowException(new FileSystemException(__('Filesystem is not writable.')));

        $this->model->process($this->inputMock);
    }
}
