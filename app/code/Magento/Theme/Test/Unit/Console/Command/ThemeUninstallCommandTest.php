<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Theme\Console\Command\ThemeUninstallCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Tester\CommandTester;

class ThemeUninstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Backup\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupFS;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $file;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Composer\GeneralDependencyChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dependencyChecker;

    /**
     * @var \Magento\Theme\Model\Theme\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\Framework\Composer\Remove|\PHPUnit_Framework_MockObject_MockObject
     */
    private $remove;

    /**
     * @var ThemeUninstallCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $this->backupFS = $this->getMock('Magento\Framework\Backup\Filesystem', [], [], '', false);
        $this->objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['Magento\Framework\Backup\Filesystem', [], $this->backupFS],
            ]));
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $path = realpath(__DIR__ . '/../../_files/');
        $this->directoryList->expects($this->any())
            ->method('getRoot')
            ->willReturn($path);
        $this->directoryList->expects($this->any())
            ->method('getPath')
            ->willReturn($path);
        $this->file = $this->getMock('Magento\Framework\Filesystem\Driver\File', [], [], '', false);
        $composerInformation = $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $composerInformation->expects($this->any())
            ->method('getRootRequiredPackages')
            ->willReturn(['magento/theme-a', 'magento/theme-b', 'magento/theme-c']);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->dependencyChecker = $this->getMock(
            'Magento\Framework\Composer\GeneralDependencyChecker',
            [],
            [],
            '',
            false
        );
        $this->collection = $this->getMock('Magento\Theme\Model\Theme\Collection', [], [], '', false);
        $this->remove = $this->getMock('Magento\Framework\Composer\Remove', [], [], '', false);
        $state = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->command = new ThemeUninstallCommand(
            $composerInformation,
            $this->deploymentConfig,
            $this->maintenanceMode,
            $this->objectManager,
            $this->directoryList,
            $this->file,
            $this->filesystem,
            $this->dependencyChecker,
            $this->collection,
            $this->remove,
            $state
        );
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteWithoutApplicationInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->tester->execute(['theme' => ['test']]);
        $this->assertContains(
            'You cannot run this command because the Magento application is not installed.',
            $this->tester->getDisplay()
        );
    }

    public function testExecuteFailedValidationNotPackage()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        // package name "dummy" is not in root composer.json file
        $dirRead->expects($this->any())
            ->method('readFile')
            ->will($this->returnValueMap(
                [
                    ['test1/composer.json', null, null, '{"name": "dummy"}'],
                    ['test2/composer.json', null, null, '{"name": "magento/theme-a"}']
                ]
            ));
        $dirRead->expects($this->any())->method('isExist')->willReturn(true);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->willReturn($dirRead);
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn($this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface', [], '', false));
        $this->collection->expects($this->any())->method('hasTheme')->willReturn(true);
        $this->tester->execute(['theme' => ['test1', 'test2']]);
        $this->assertContains(
            'test1 is not an installed composer package',
            $this->tester->getDisplay()
        );
        $this->assertNotContains(
            'test2 is not an installed composer package',
            $this->tester->getDisplay()
        );
    }

    public function testExecuteFailedValidationNotTheme()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $dirRead->expects($this->any())->method('isExist')->willReturn(false);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->willReturn($dirRead);
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn($this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface', [], '', false));
        $this->collection->expects($this->any())->method('hasTheme')->willReturn(false);
        $this->tester->execute(['theme' => ['test1', 'test2']]);
        $this->assertContains(
            'Unknown theme(s): test1, test2' . PHP_EOL,
            $this->tester->getDisplay()
        );
    }

    public function testExecuteFailedValidationMixed()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        // package name "dummy" is not in root composer.json file
        $dirRead->expects($this->any())
            ->method('readFile')
            ->will($this->returnValueMap(
                [
                    ['test1/composer.json', null, null, '{"name": "dummy1"}'],
                    ['test2/composer.json', null, null, '{"name": "magento/theme-b"}'],
                    ['test4/composer.json', null, null, '{"name": "dummy2"}'],
                ]
            ));
        $dirRead->expects($this->any())
            ->method('isExist')
            ->will($this->returnValueMap(
                [
                    ['test1/composer.json', true],
                    ['test2/composer.json', true],
                    ['test3/composer.json', false],
                    ['test4/composer.json', true],
                ]
            ));
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn($this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface', [], '', false));
        $this->collection->expects($this->at(1))->method('hasTheme')->willReturn(true);
        $this->collection->expects($this->at(3))->method('hasTheme')->willReturn(true);
        $this->collection->expects($this->at(5))->method('hasTheme')->willReturn(false);
        $this->collection->expects($this->at(7))->method('hasTheme')->willReturn(true);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->willReturn($dirRead);
        $this->tester->execute(['theme' => ['test1', 'test2', 'test3', 'test4']]);
        $this->assertContains(
            'test1, test4 are not installed composer packages',
            $this->tester->getDisplay()
        );
        $this->assertNotContains(
            'test2 is not an installed composer package',
            $this->tester->getDisplay()
        );
        $this->assertContains(
            'Unknown theme(s): test3' . PHP_EOL,
            $this->tester->getDisplay()
        );
    }

    public function setUpPassValidation()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        // package name "dummy" is not in root composer.json file
        $dirRead->expects($this->any())
            ->method('readFile')
            ->willReturn('{"name": "magento/theme-a"}');
        $dirRead->expects($this->any())
            ->method('isExist')
            ->willReturn(true);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->willReturn($dirRead);
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn($this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface', [], '', false));
        $this->collection->expects($this->any())->method('hasTheme')->willReturn(true);
    }

    public function testExecuteFailedDependencyCheck()
    {
        $this->setUpPassValidation();
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->willReturn(['magento/theme-a' => ['magento/theme-b', 'magento/theme-c']]);
        $this->tester->execute(['theme' => ['frontend/Magento/a']]);
        $this->assertContains(
            'Cannot uninstall frontend/Magento/a because the following package(s) ' .
            'depend on it:' . PHP_EOL . "\tmagento/theme-b" . PHP_EOL . "\tmagento/theme-c",
            $this->tester->getDisplay()
        );
    }

    public function setUpPassValidationAndDependencyCheck()
    {
        $this->setUpPassValidation();
        $this->dependencyChecker->expects($this->once())->method('checkDependencies')->willReturn([]);
        $this->remove->expects($this->once())->method('remove');
    }

    public function testExecuteWithBackupCode()
    {
        $this->setUpPassValidationAndDependencyCheck();
        $this->backupFS->expects($this->once())
            ->method('addIgnorePaths');
        $this->backupFS->expects($this->once())
            ->method('setBackupsDir');
        $this->backupFS->expects($this->once())
            ->method('setBackupExtension');
        $this->backupFS->expects($this->once())
            ->method('setTime');
        $this->backupFS->expects($this->once())
            ->method('create');
        $this->backupFS->expects($this->once())
            ->method('getBackupFilename')
            ->willReturn('RollbackFile_A.tgz');
        $this->backupFS->expects($this->once())
            ->method('getBackupPath')
            ->willReturn('pathToFile/RollbackFile_A.tgz');
        $this->file->expects($this->once())->method('isExists')->willReturn(false);
        $this->file->expects($this->once())->method('createDirectory');
        $this->tester->execute(['theme' => ['test'], '--backup-code' => true]);
        $this->tester->getDisplay();
    }

    public function testExecute()
    {
        $this->setUpPassValidationAndDependencyCheck();
        $this->tester->execute(['theme' => ['test']]);
        $this->assertContains('Enabling maintenance mode', $this->tester->getDisplay());
        $this->assertContains('Disabling maintenance mode', $this->tester->getDisplay());
    }
}
