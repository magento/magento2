<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\Cache;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Console\Command\DiCompileCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Module\Di\App\Task\Manager;
use Magento\Setup\Module\Di\App\Task\OperationFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiCompileCommandTest extends TestCase
{
    /** @var DeploymentConfig|MockObject */
    private $deploymentConfigMock;

    /** @var Manager|MockObject */
    private $managerMock;

    /** @var ObjectManagerInterface|MockObject */
    private $objectManagerMock;

    /** @var DiCompileCommand|MockObject */
    private $command;

    /** @var  Cache|MockObject */
    private $cacheMock;

    /** @var  Filesystem|MockObject */
    private $filesystemMock;

    /** @var  File|MockObject */
    private $fileDriverMock;

    /** @var  DirectoryList|MockObject */
    private $directoryListMock;

    /** @var  ComponentRegistrar|MockObject */
    private $componentRegistrarMock;

    /** @var  OutputInterface|MockObject */
    private $outputMock;

    /** @var OutputFormatterInterface|MockObject */
    private $outputFormatterMock;

    /** @var Filesystem\Io\File|MockObject */
    private $fileMock;

    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $objectManagerProviderMock = $this->createMock(ObjectManagerProvider::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMockBuilder(Cache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerProviderMock->expects($this->once())
            ->method('get')
            ->willReturn($this->objectManagerMock);
        $this->managerMock = $this->createMock(Manager::class);
        $this->directoryListMock =
            $this->createMock(DirectoryList::class);
        $this->directoryListMock->expects($this->any())->method('getPath')->willReturnMap([
            [DirectoryList::SETUP, '/path (1)/to/setup/'],
        ]);

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileDriverMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileDriverMock->method('getParentDirectory')->willReturnMap(
            [
                ['/path/to/module/one', '/path/to/module'],
                ['/path/to/module', '/path/to'],
                ['/path (1)/to/module/two', '/path (1)/to/module'],
                ['/path (1)/to/module', '/path (1)/to'],
            ]
        );
        $this->componentRegistrarMock = $this->createMock(ComponentRegistrar::class);
        $this->componentRegistrarMock->expects($this->any())->method('getPaths')->willReturnMap([
            [ComponentRegistrar::MODULE, ['/path/to/module/one', '/path (1)/to/module/two']],
            [ComponentRegistrar::LIBRARY, ['/path/to/library/one', '/path (1)/to/library/two']],
        ]);

        $this->outputFormatterMock = $this->createMock(
            OutputFormatterInterface::class
        );
        $this->outputMock = $this->getMockForAbstractClass(OutputInterface::class);
        $this->outputMock->method('getFormatter')
            ->willReturn($this->outputFormatterMock);
        $this->fileMock = $this->getMockBuilder(Filesystem\Io\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileMock->method('getPathInfo')->willReturnMap(
            [
                ['/path/to/module/one', ['basename' => 'one']],
                ['/path/to/module', ['basename' => 'module']],
                ['/path (1)/to/module/two', ['basename' => 'two']],
                ['/path (1)/to/module', ['basename' => 'module']],
            ]
        );

        $this->command = new DiCompileCommand(
            $this->deploymentConfigMock,
            $this->directoryListMock,
            $this->managerMock,
            $objectManagerProviderMock,
            $this->filesystemMock,
            $this->fileDriverMock,
            $this->componentRegistrarMock,
            $this->fileMock
        );
    }

    public function testExecuteModulesNotEnabled()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsListConstants::KEY_MODULES)
            ->willReturn(null);
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertEquals(
            'You cannot run this command because modules are not enabled. You can enable modules by running the '
            . "'module:enable --all' command." . PHP_EOL,
            $tester->getDisplay()
        );
    }

    public function testExecute()
    {
        $this->directoryListMock->expects($this->atLeastOnce())->method('getPath')->willReturn(null);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Cache::class)
            ->willReturn($this->cacheMock);
        $this->cacheMock->expects($this->once())->method('clean');
        $writeDirectory = $this->getMockForAbstractClass(WriteInterface::class);
        $writeDirectory->expects($this->atLeastOnce())->method('delete');
        $this->filesystemMock->expects($this->atLeastOnce())->method('getDirectoryWrite')->willReturn($writeDirectory);

        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(ConfigOptionsListConstants::KEY_MODULES)
            ->willReturn(['Magento_Catalog' => 1]);
        $progressBar = new ProgressBar($this->outputMock);

        $this->objectManagerMock->expects($this->once())->method('configure');
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(ProgressBar::class)
            ->willReturn($progressBar);

        $this->managerMock->expects($this->exactly(9))->method('addOperation')
            ->withConsecutive(
                [OperationFactory::PROXY_GENERATOR, []],
                [OperationFactory::REPOSITORY_GENERATOR, $this->anything()],
                [OperationFactory::DATA_ATTRIBUTES_GENERATOR, []],
                [OperationFactory::APPLICATION_CODE_GENERATOR, $this->callback(function ($subject) {
                    $this->assertEmpty(array_diff($subject['excludePatterns'], [
                        "#^(?:/path \(1\)/to/setup/)(/[\w]+)*/Test#",
                        "#^(?:/path/to/library/one|/path \(1\)/to/library/two)/([\w]+/)?Test#",
                        "#^(?:/path/to/library/one|/path \(1\)/to/library/two)/([\w]+/)?tests#",
                        "#^(?:/path/to/(?:module/(?:one))|/path \(1\)/to/(?:module/(?:two)))/Test#",
                        "#^(?:/path/to/(?:module/(?:one))|/path \(1\)/to/(?:module/(?:two)))/tests#"
                    ]));
                    return true;
                })],
                [OperationFactory::INTERCEPTION, $this->anything()],
                [OperationFactory::AREA_CONFIG_GENERATOR, $this->anything()],
                [OperationFactory::INTERCEPTION_CACHE, $this->anything()],
                [OperationFactory::APPLICATION_ACTION_LIST_GENERATOR, $this->anything()],
                [OperationFactory::PLUGIN_LIST_GENERATOR, $this->anything()]
            );

        $this->managerMock->expects($this->once())->method('process');
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertContains(
            'Generated code and dependency injection configuration successfully.',
            explode(PHP_EOL, $tester->getDisplay())
        );
        $this->assertSame(DiCompileCommand::NAME, $this->command->getName());
    }
}
