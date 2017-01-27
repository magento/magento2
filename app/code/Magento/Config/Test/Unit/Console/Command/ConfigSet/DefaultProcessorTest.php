<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\DefaultProcessor;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Config\Model\ResourceModel\ConfigFactory;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\MetadataProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ScopePathResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ScopeResolverPool;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Config\Model\ResourceModel\Config\Data\Collection;

/**
 * Test for DefaultProcessor.
 *
 * @see \Magento\Config\Console\Command\ConfigSet\DefaultProcessor
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
     * @var CollectionFactory|Mock
     */
    private $collectionFactoryMock;

    /**
     * @var Collection|Mock
     */
    private $collection;

    /**
     * @var DeploymentConfig|Mock
     */
    private $deploymentConfigMock;

    /**
     * @var ScopeResolverPool|Mock
     */
    private $scopeResolverPoolMock;

    /**
     * @var ScopePathResolver|Mock
     */
    private $scopePathResolverMock;

    /**
     * @var MetadataProcessor|Mock
     */
    private $metadataProcessorMock;

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
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeResolverPoolMock = $this->getMockBuilder(ScopeResolverPool::class)
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

        $this->collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);
        $this->collection->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->configFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->configMock);

        $this->model = new DefaultProcessor(
            $this->configFactoryMock,
            $this->collectionFactoryMock,
            $this->deploymentConfigMock,
            $this->scopeResolverPoolMock,
            $this->scopePathResolverMock,
            $this->metadataProcessorMock
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
                [ConfigSetCommand::OPTION_FORCE, false],
            ]);
        $this->scopePathResolverMock->expects($this->once())
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
        $this->collection->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->metadataProcessorMock->expects($this->once())
            ->method('prepareValue')
            ->with($value, $path)
            ->willReturn($value);
        $this->configMock->expects($this->once())
            ->method('saveConfig')
            ->with($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);

        $this->model->process($this->inputMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Magento is not installed yet.
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
                [ConfigSetCommand::OPTION_FORCE, false],
            ]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(false);

        $this->model->process($this->inputMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Effective value already locked.
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
                [ConfigSetCommand::OPTION_FORCE, false],
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
        $this->scopePathResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturn('system/default/test/test/test');

        $this->model->process($this->inputMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Config value is already exists.
     */
    public function testProcessDuplicate()
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
                [ConfigSetCommand::OPTION_FORCE, false],
            ]);
        $this->deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->willReturnMap([
                ['system/default/test/test/test', null],
            ]);
        $this->collection->expects($this->once())
            ->method('getItems')
            ->willReturn(['item1']);

        $this->model->process($this->inputMock);
    }
}
