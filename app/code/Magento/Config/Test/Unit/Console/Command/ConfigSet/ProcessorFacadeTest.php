<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorInterface;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacade;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Config\Model\Config\PathValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for ProcessorFacade.
 *
 * @see ProcessorFacade
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorFacadeTest extends \PHPUnit\Framework\TestCase
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
    protected function setUp()
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
            $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false)
        );
    }

    /**
     * @param LocalizedException $exception
     * @dataProvider processWithValidatorExceptionDataProvider
     */
    public function testProcessWithValidatorException(LocalizedException $exception)
    {
        $this->expectException(ValidatorException::class, 'Some error');
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willThrowException($exception);

        $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false);
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

    /**
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     * @expectedExceptionMessage Some error
     */
    public function testProcessWithConfigurationMismatchException()
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
            ->willThrowException(new ConfigurationMismatchException(__('Some error')));
        $this->processorMock->expects($this->never())
            ->method('process');
        $this->configMock->expects($this->never())
            ->method('clean');

        $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Some error
     */
    public function testProcessWithCouldNotSaveException()
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
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willThrowException(new CouldNotSaveException(__('Some error')));
        $this->configMock->expects($this->never())
            ->method('clean');

        $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, false);
    }

    public function testExecuteLock()
    {
        $this->scopeValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_LOCK)
            ->willReturn($this->processorMock);
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);
        $this->configMock->expects($this->once())
            ->method('clean');

        $this->assertSame(
            'Value was saved and locked.',
            $this->model->process('test/test/test', 'test', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, true)
        );
    }
}
