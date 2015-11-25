<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Console\Command\DiCompileCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DiCompileCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject */
    private $deploymentConfig;

    /** @var \Magento\Setup\Module\Di\App\Task\Manager|\PHPUnit_Framework_MockObject_MockObject */
    private $manager;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var DiCompileCommand|\PHPUnit_Framework_MockObject_MockObject */
    private $command;

    /** @var  \Magento\Framework\App\Cache|\PHPUnit_Framework_MockObject_MockObject */
    private $cacheMock;

    /** @var  \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    private $filesystem;

    /** @var  \Magento\Framework\Filesystem\Driver\File | \PHPUnit_Framework_MockObject_MockObject*/
    private $fileDriver;

    /** @var  \Magento\Framework\App\Filesystem\DirectoryList | \PHPUnit_Framework_MockObject_MockObject*/
    private $directoryList;

    /** @var  \Magento\Framework\Component\ComponentRegistrar|\PHPUnit_Framework_MockObject_MockObject */
    private $componentRegistrar;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $objectManagerProvider = $this->getMock(
            'Magento\Setup\Model\ObjectManagerProvider',
            [],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $this->cacheMock = $this->getMockBuilder('Magento\Framework\App\Cache')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerProvider->expects($this->once())
            ->method('get')
            ->willReturn($this->objectManager);
        $this->manager = $this->getMock('Magento\Setup\Module\Di\App\Task\Manager', [], [], '', false);
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileDriver = $this->getMockBuilder('Magento\Framework\Filesystem\Driver\File')
            ->disableOriginalConstructor()
            ->getMock();
        $this->componentRegistrar = $this->getMock(
            '\Magento\Framework\Component\ComponentRegistrar',
            [],
            [],
            '',
            false
        );
        $this->componentRegistrar->expects($this->any())->method('getPaths')->willReturnMap([
            [ComponentRegistrar::MODULE, ['/path/to/module/one', '/path/to/module/two']],
            [ComponentRegistrar::LIBRARY, ['/path/to/library/one', '/path/to/library/two']],
        ]);

        $this->command = new DiCompileCommand(
            $this->deploymentConfig,
            $this->directoryList,
            $this->manager,
            $objectManagerProvider,
            $this->filesystem,
            $this->fileDriver,
            $this->componentRegistrar
        );
    }

    public function testExecuteDiExists()
    {
        $diPath = '/root/magento/var/di';
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $this->fileDriver->expects($this->atLeastOnce())->method('isExists')->with($diPath)->willReturn(true);
        $this->directoryList->expects($this->atLeastOnce())->method('getPath')->willReturn($diPath);
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertContains("delete '/root/magento/var/di'", $tester->getDisplay());
    }

    public function testExecuteNotInstalled()
    {
        $this->directoryList->expects($this->atLeastOnce())->method('getPath')->willReturn(null);
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertEquals(
            'You cannot run this command because the Magento application is not installed.' . PHP_EOL,
            $tester->getDisplay()
        );
    }

    public function testExecute()
    {
        $this->directoryList->expects($this->atLeastOnce())->method('getPath')->willReturn(null);
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\App\Cache')
            ->willReturn($this->cacheMock);
        $this->cacheMock->expects($this->once())->method('clean');
        $writeDirectory = $this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface');
        $writeDirectory->expects($this->atLeastOnce())->method('delete');
        $this->filesystem->expects($this->atLeastOnce())->method('getDirectoryWrite')->willReturn($writeDirectory);

        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $progressBar = $this->getMockBuilder(
            'Symfony\Component\Console\Helper\ProgressBar'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->once())->method('configure');
        $this->objectManager
            ->expects($this->once())
            ->method('create')
            ->with('Symfony\Component\Console\Helper\ProgressBar')
            ->willReturn($progressBar);
        $this->manager->expects($this->exactly(7))->method('addOperation');
        $this->manager->expects($this->once())->method('process');
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertContains(
            'Generated code and dependency injection configuration successfully.',
            explode(PHP_EOL, $tester->getDisplay())
        );
        $this->assertSame(DiCompileCommand::NAME, $this->command->getName());
    }
}
