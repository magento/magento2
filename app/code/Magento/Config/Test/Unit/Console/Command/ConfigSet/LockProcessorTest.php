<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\LockProcessor;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Stdlib\ArrayManager;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Config\Model\Config\Structure;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Config\Model\Config\Structure\Element\Field;
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
     * @var DeploymentConfig\Writer|Mock
     */
    private $deploymentConfigWriterMock;

    /**
     * @var ArrayManager|Mock
     */
    private $arrayManagerMock;

    /**
     * @var InputInterface|Mock
     */
    private $inputMock;

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
        $this->deploymentConfigWriterMock = $this->getMockBuilder(DeploymentConfig\Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
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
            ->disableOriginalConstructor()
            ->getMock();

        $this->structureMock->expects($this->any())
            ->method('getElement')
            ->willReturn($this->fieldMock);

        $this->model = new LockProcessor(
            $this->deploymentConfigWriterMock,
            $this->arrayManagerMock,
            $this->configPathResolver,
            $this->structureMock,
            $this->valueFactoryMock
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
                [ConfigSetCommand::OPTION_SCOPE_CODE, 'test_scope_code']
            ]);
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
            ]);
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
            ->willReturn(null);
        $this->deploymentConfigWriterMock->expects($this->once())
            ->method('saveConfig')
            ->willThrowException(new FileSystemException(__('Filesystem is not writable.')));

        $this->model->process($this->inputMock);
    }
}
