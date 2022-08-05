<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorInterface;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacade;
use Magento\Config\Model\Config\PathValidator;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * Test for ProcessorFacade.
 *
 * @see ProcessorFacade
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorFacadeTest extends TestCase
{
    /**
     * @var ProcessorFacade
     */
    private $model;

    /**
     * @var ValidatorInterface|Mock
     */
    private $scopeValidatorMock;

    /**
     * @var PathValidator|Mock
     */
    private $pathValidatorMock;

    /**
     * @var ConfigSetProcessorFactory|Mock
     */
    private $configSetProcessorFactoryMock;

    /**
     * @var ConfigSetProcessorInterface|Mock
     */
    private $processorMock;

    /**
     * @var Hash|Mock
     */
    private $hashMock;

    /**
     * @var Config|Mock
     */
    private $configMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeValidatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->pathValidatorMock = $this->getMockBuilder(PathValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configSetProcessorFactoryMock = $this->getMockBuilder(ConfigSetProcessorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorMock = $this->getMockBuilder(ConfigSetProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->configSetProcessorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->processorMock);

        $this->hashMock = $this->getMockBuilder(Hash::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new ProcessorFacade(
            $this->scopeValidatorMock,
            $this->pathValidatorMock,
            $this->configSetProcessorFactoryMock,
            $this->hashMock,
            $this->configMock
        );
    }

    public function testProcess()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->pathValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_DEFAULT)
            ->willReturn($this->processorMock);
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
        $this->hashMock->expects($this->once())
            ->method('regenerate')
            ->with(System::CONFIG_TYPE);
        $this->configMock->expects($this->once())
            ->method('clean');

        $this->assertSame(
            'Value was saved.',
            $this->model->processWithLockTarget(
                'test/test/test',
                'test',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                false
            )
        );
    }

    /**
     * @param LocalizedException $exception
     * @dataProvider processWithValidatorExceptionDataProvider
     */
    public function testProcessWithValidatorException(LocalizedException $exception)
    {
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Some error');
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willThrowException($exception);

        $this->model->processWithLockTarget(
            'test/test/test',
            'test',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            null,
            false
        );
    }

    /**
     * @return array
     */
    public function processWithValidatorExceptionDataProvider()
    {
        return [
            [new LocalizedException(__('Some error'))],
            [new ValidatorException(__('Some error'))],
        ];
    }

    public function testProcessWithConfigurationMismatchException()
    {
        $this->expectException('Magento\Framework\Exception\ConfigurationMismatchException');
        $this->expectExceptionMessage('Some error');
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->pathValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_DEFAULT)
            ->willThrowException(new ConfigurationMismatchException(__('Some error')));
        $this->processorMock->expects($this->never())
            ->method('process');
        $this->configMock->expects($this->never())
            ->method('clean');

        $this->model->processWithLockTarget(
            'test/test/test',
            'test',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            null,
            false
        );
    }

    public function testProcessWithCouldNotSaveException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('Some error');
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->pathValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_DEFAULT)
            ->willReturn($this->processorMock);
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willThrowException(new CouldNotSaveException(__('Some error')));
        $this->configMock->expects($this->never())
            ->method('clean');

        $this->model->processWithLockTarget(
            'test/test/test',
            'test',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            null,
            false
        );
    }

    public function testExecuteLockEnv()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_LOCK_ENV)
            ->willReturn($this->processorMock);
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
        $this->configMock->expects($this->once())
            ->method('clean');

        $this->assertSame(
            'Value was saved in app/etc/env.php and locked.',
            $this->model->processWithLockTarget(
                'test/test/test',
                'test',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                true
            )
        );
    }

    public function testExecuteLockConfig()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_LOCK_CONFIG)
            ->willReturn($this->processorMock);
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
        $this->configMock->expects($this->once())
            ->method('clean');

        $this->assertSame(
            'Value was saved in app/etc/config.php and locked.',
            $this->model->processWithLockTarget(
                'test/test/test',
                'test',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                null,
                true,
                ConfigFilePool::APP_CONFIG
            )
        );
    }
}
