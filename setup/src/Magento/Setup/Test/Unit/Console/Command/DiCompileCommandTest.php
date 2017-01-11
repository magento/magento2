<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Console\Command\DiCompileCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiCompileCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject */
    private $deploymentConfigMock;

    /** @var \Magento\Setup\Module\Di\App\Task\Manager|\PHPUnit_Framework_MockObject_MockObject */
    private $managerMock;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    /** @var DiCompileCommand|\PHPUnit_Framework_MockObject_MockObject */
    private $command;

    /** @var  \Magento\Framework\App\Cache|\PHPUnit_Framework_MockObject_MockObject */
    private $cacheMock;

    /** @var  \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    private $filesystemMock;

    /** @var  \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject */
    private $fileDriverMock;

    /** @var  \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject */
    private $directoryListMock;

    /** @var  \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject */
    private $componentRegistrarMock;

    public function setUp()
    {
        $this->deploymentConfigMock = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $objectManagerProviderMock = $this->getMock(
            \Magento\Setup\Model\ObjectManagerProvider::class,
            [],
            [],
            '',
            false
        );
        $this->objectManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMockBuilder(\Magento\Framework\App\Cache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerProviderMock->expects($this->once())
            ->method('get')
            ->willReturn($this->objectManagerMock);
        $this->managerMock = $this->getMock(\Magento\Setup\Module\Di\App\Task\Manager::class, [], [], '', false);
        $this->directoryListMock =
            $this->getMock(\Magento\Framework\App\Filesystem\DirectoryList::class, [], [], '', false);
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileDriverMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Driver\File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->componentRegistrarMock = $this->getMock(
            \Magento\Framework\Component\ComponentRegistrar::class,
            [],
            [],
            '',
            false
        );
        $this->componentRegistrarMock->expects($this->any())->method('getPaths')->willReturnMap([
            [ComponentRegistrar::MODULE, ['/path/to/module/one', '/path/to/module/two']],
            [ComponentRegistrar::LIBRARY, ['/path/to/library/one', '/path/to/library/two']],
        ]);

        $this->command = new DiCompileCommand(
            $this->deploymentConfigMock,
            $this->directoryListMock,
            $this->managerMock,
            $objectManagerProviderMock,
            $this->filesystemMock,
            $this->fileDriverMock,
            $this->componentRegistrarMock
        );
    }

    public function testExecuteModulesNotEnabled()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Config\ConfigOptionsListConstants::KEY_MODULES)
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
            ->with(\Magento\Framework\App\Cache::class)
            ->willReturn($this->cacheMock);
        $this->cacheMock->expects($this->once())->method('clean');
        $writeDirectory = $this->getMock(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $writeDirectory->expects($this->atLeastOnce())->method('delete');
        $this->filesystemMock->expects($this->atLeastOnce())->method('getDirectoryWrite')->willReturn($writeDirectory);

        $this->deploymentConfigMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Config\ConfigOptionsListConstants::KEY_MODULES)
            ->willReturn(['Magento_Catalog' => 1]);
        $progressBar = $this->getMockBuilder(
            \Symfony\Component\Console\Helper\ProgressBar::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())->method('configure');
        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(\Symfony\Component\Console\Helper\ProgressBar::class)
            ->willReturn($progressBar);
        $this->managerMock->expects($this->exactly(7))->method('addOperation');
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
