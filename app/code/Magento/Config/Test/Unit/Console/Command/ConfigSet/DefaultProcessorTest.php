<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\DefaultProcessor;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Model\Config;
use Magento\Config\Model\ConfigFactory;
use Magento\Framework\App\Config\ConfigPathResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Test for DefaultProcessor.
 *
 * @see DefaultProcessor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DefaultProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultProcessor
     */
    private $model;

    /**
     * @var ConfigFactory|Mock
     */
    private $configFactoryMock;

    /**
     * @var Config|Mock
     */
    private $configMock;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfigMock;

    /**
     * @var ConfigPathResolver|Mock
     */
    private $configPathResolverMock;

    /**
     * @var InputInterface|Mock
     */
    private $inputMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->configFactoryMock = $this->getMockBuilder(ConfigFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->configPathResolverMock = $this->getMockBuilder(ConfigPathResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->configMock);

        $this->model = new DefaultProcessor(
            $this->configFactoryMock,
            $this->deploymentConfigMock,
            $this->configPathResolverMock
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
                [ConfigSetCommand::ARG_VALUE, $value]
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_SCOPE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT],
                [ConfigSetCommand::OPTION_SCOPE_CODE, null],
            ]);
        $this->configPathResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturnMap([
                ['system/default/test/test/test', null],
            ]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('save');

        $this->model->process($this->inputMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We can't save this option because Magento is not installed.
     */
    public function testProcessNotInstalled()
    {
        $path = 'test/test/test';
        $value = 'value';

        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value]
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_SCOPE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT],
                [ConfigSetCommand::OPTION_SCOPE_CODE, null],
            ]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $this->model->process($this->inputMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The value you set has already been locked. To change the value, use the --lock option.
     */
    public function testProcessLockedValue()
    {
        $path = 'test/test/test';
        $value = 'value';

        $this->inputMock->expects($this->any())
            ->method('getArgument')
            ->willReturnMap([
                [ConfigSetCommand::ARG_PATH, $path],
                [ConfigSetCommand::ARG_VALUE, $value]
            ]);
        $this->inputMock->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                [ConfigSetCommand::OPTION_SCOPE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT],
                [ConfigSetCommand::OPTION_SCOPE_CODE, null],
            ]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturnMap([
                ['db', null, 'exists'],
                ['system/default/test/test/test', null, 5],
            ]);
        $this->configPathResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');

        $this->model->process($this->inputMock);
    }
}
