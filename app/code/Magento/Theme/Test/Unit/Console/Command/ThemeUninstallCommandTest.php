<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Theme\Console\Command\ThemeUninstallCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Setup\BackupRollbackFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThemeUninstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var \Magento\Framework\Composer\DependencyChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dependencyChecker;

    /**
     * @var \Magento\Theme\Model\Theme\Data\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\Framework\Composer\Remove|\PHPUnit_Framework_MockObject_MockObject
     */
    private $remove;

    /**
     * @var \Magento\Framework\App\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanupFiles;

    /**
     * @var \Magento\Theme\Model\Theme\ThemeProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeProvider;

    /**
     * @var ThemeUninstallCommand
     */
    private $command;

    /**
     * @var BackupRollbackFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backupRollbackFactory;

    /**
     * Theme Validator
     *
     * @var ThemeValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeValidator;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readDirFactory;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $composerInformation = $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $composerInformation->expects($this->any())
            ->method('getRootRequiredPackages')
            ->willReturn(['magento/theme-a', 'magento/theme-b', 'magento/theme-c']);
        $this->dependencyChecker = $this->getMock(
            'Magento\Framework\Composer\DependencyChecker',
            [],
            [],
            '',
            false
        );
        $this->collection = $this->getMock('Magento\Theme\Model\Theme\Data\Collection', [], [], '', false);
        $this->remove = $this->getMock('Magento\Framework\Composer\Remove', [], [], '', false);
        $this->cache = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $this->cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $this->themeProvider = $this->getMock('Magento\Theme\Model\Theme\ThemeProvider', [], [], '', false);
        $this->backupRollbackFactory = $this->getMock(
            'Magento\Framework\Setup\BackupRollbackFactory',
            [],
            [],
            '',
            false
        );
        $this->themeValidator = $this->getMock('Magento\Theme\Model\ThemeValidator', [], [], '', false);
        $this->componentRegistrar = $this->getMockForAbstractClass(
            '\Magento\Framework\Component\ComponentRegistrarInterface'
        );
        $this->readDirFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $this->command = new ThemeUninstallCommand(
            $this->cache,
            $this->cleanupFiles,
            $composerInformation,
            $this->maintenanceMode,
            $this->dependencyChecker,
            $this->collection,
            $this->themeProvider,
            $this->remove,
            $this->backupRollbackFactory,
            $this->themeValidator,
            $this->componentRegistrar,
            $this->readDirFactory
        );
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteFailedValidationNotPackage()
    {
        $dirReadOne = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $dirReadTwo = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $dirReadOne->expects($this->once())
            ->method('readFile')
            ->with('composer.json')
            ->will($this->returnValue('{"name": "dummy"}'));
        $dirReadTwo->expects($this->once())
            ->method('readFile')
            ->with('composer.json')
            ->will($this->returnValue('{"name": "magento/theme-a"}'));
        $dirReadOne->expects($this->any())->method('isExist')->willReturn(true);
        $dirReadTwo->expects($this->any())->method('isExist')->willReturn(true);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['theme1', DriverPool::FILE, $dirReadOne],
                ['theme2', DriverPool::FILE, $dirReadTwo],
            ]));
        $this->componentRegistrar->expects($this->any())
            ->method('getPath')
            ->will($this->returnValueMap([
                [ComponentRegistrar::THEME, 'test1', 'theme1'],
                [ComponentRegistrar::THEME, 'test2', 'theme2'],
            ]));
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn($this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface', [], '', false));
        $this->collection->expects($this->any())->method('hasTheme')->willReturn(true);
        $this->tester->execute(['theme' => ['test1', 'test2']]);
        $this->assertContains(
            'test1 is not an installed Composer package',
            $this->tester->getDisplay()
        );
        $this->assertNotContains(
            'test2 is not an installed Composer package',
            $this->tester->getDisplay()
        );
    }

    public function testExecuteFailedValidationNotTheme()
    {
        $dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $dirRead->expects($this->any())->method('isExist')->willReturn(false);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($dirRead));
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
        $dirReadOne = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $dirReadTwo = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $dirReadThree = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $dirReadFour = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $dirReadOne->expects($this->once())
            ->method('readFile')
            ->with('composer.json')
            ->will($this->returnValue('{"name": "dummy1"}'));
        $dirReadTwo->expects($this->once())
            ->method('readFile')
            ->with('composer.json')
            ->will($this->returnValue('{"name": "magento/theme-b"}'));
        $dirReadFour->expects($this->once())
            ->method('readFile')
            ->with('composer.json')
            ->will($this->returnValue('{"name": "dummy2"}'));
        $dirReadOne->expects($this->any())->method('isExist')->willReturn(true);
        $dirReadTwo->expects($this->any())->method('isExist')->willReturn(true);
        $dirReadThree->expects($this->any())->method('isExist')->willReturn(false);
        $dirReadFour->expects($this->any())->method('isExist')->willReturn(true);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap([
                ['theme1', DriverPool::FILE, $dirReadOne],
                ['theme2', DriverPool::FILE, $dirReadTwo],
                ['theme3', DriverPool::FILE, $dirReadThree],
                ['theme4', DriverPool::FILE, $dirReadFour],
            ]));
        $this->componentRegistrar->expects($this->any())
            ->method('getPath')
            ->will($this->returnValueMap([
                [ComponentRegistrar::THEME, 'test1', 'theme1'],
                [ComponentRegistrar::THEME, 'test2', 'theme2'],
                [ComponentRegistrar::THEME, 'test3', 'theme3'],
                [ComponentRegistrar::THEME, 'test4', 'theme4'],
            ]));
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn($this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface', [], '', false));
        $this->collection->expects($this->at(1))->method('hasTheme')->willReturn(true);
        $this->collection->expects($this->at(3))->method('hasTheme')->willReturn(true);
        $this->collection->expects($this->at(5))->method('hasTheme')->willReturn(false);
        $this->collection->expects($this->at(7))->method('hasTheme')->willReturn(true);
        $this->tester->execute(['theme' => ['test1', 'test2', 'test3', 'test4']]);
        $this->assertContains(
            'test1, test4 are not installed Composer packages',
            $this->tester->getDisplay()
        );
        $this->assertNotContains(
            'test2 is not an installed Composer package',
            $this->tester->getDisplay()
        );
        $this->assertContains(
            'Unknown theme(s): test3' . PHP_EOL,
            $this->tester->getDisplay()
        );
    }

    public function setUpPassValidation()
    {
        $dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        // package name "dummy" is not in root composer.json file
        $dirRead->expects($this->any())
            ->method('readFile')
            ->willReturn('{"name": "magento/theme-a"}');
        $dirRead->expects($this->any())
            ->method('isExist')
            ->willReturn(true);
        $this->readDirFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($dirRead));
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn($this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface', [], '', false));
        $this->collection->expects($this->any())->method('hasTheme')->willReturn(true);
    }

    public function setupPassChildThemeCheck()
    {
        $theme = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $theme->expects($this->any())->method('hasChildThemes')->willReturn(false);
        $this->themeProvider->expects($this->any())->method('getThemeByFullPath')->willReturn($theme);
        $this->collection->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator([]));
    }

    public function setupPassThemeInUseCheck()
    {
        $this->themeValidator->expects($this->once())->method('validateIsThemeInUse')->willReturn([]);
    }

    public function setupPassDependencyCheck()
    {
        $this->dependencyChecker->expects($this->once())->method('checkDependencies')->willReturn([]);
    }

    public function testExecuteFailedThemeInUseCheck()
    {
        $this->setUpPassValidation();
        $this->setupPassChildThemeCheck();
        $this->setupPassDependencyCheck();
        $this->themeValidator
            ->expects($this->once())
            ->method('validateIsThemeInUse')
            ->willReturn(['frontend/Magento/a is in use in default config']);
        $this->tester->execute(['theme' => ['frontend/Magento/a']]);
        $this->assertEquals(
            'Unable to uninstall. Please resolve the following issues:' . PHP_EOL
            . 'frontend/Magento/a is in use in default config' . PHP_EOL,
            $this->tester->getDisplay()
        );
    }

    /**
     * @dataProvider executeFailedChildThemeCheckDataProvider
     * @param bool $hasVirtual
     * @param bool $hasPhysical
     * @param array $input
     * @param string $expected
     * @return void
     */
    public function testExecuteFailedChildThemeCheck($hasVirtual, $hasPhysical, array $input, $expected)
    {
        $this->setUpPassValidation();
        $this->setupPassThemeInUseCheck();
        $this->setupPassDependencyCheck();
        $theme = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $theme->expects($this->any())->method('hasChildThemes')->willReturn($hasVirtual);
        $parentThemeA = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $parentThemeA->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/a');
        $parentThemeB = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $parentThemeB->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/b');
        $childThemeC = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $childThemeC->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/c');
        $childThemeD = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $childThemeD->expects($this->any())->method('getFullPath')->willReturn('frontend/Magento/d');

        if ($hasPhysical) {
            $childThemeC->expects($this->any())->method('getParentTheme')->willReturn($parentThemeA);
            $childThemeD->expects($this->any())->method('getParentTheme')->willReturn($parentThemeB);
        }

        $this->themeProvider->expects($this->any())->method('getThemeByFullPath')->willReturn($theme);
        $this->collection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$childThemeC, $childThemeD]));
        $this->tester->execute($input);
        $this->assertContains($expected, $this->tester->getDisplay());
    }

    /**
     * @return array
     */
    public function executeFailedChildThemeCheckDataProvider()
    {
        return [
            [
                true,
                false,
                ['theme' => ['frontend/Magento/a']],
                'Unable to uninstall. Please resolve the following issues:' . PHP_EOL
                . 'frontend/Magento/a is a parent of virtual theme. Parent themes cannot be uninstalled.'
            ],
            [
                true,
                false,
                ['theme' => ['frontend/Magento/a', 'frontend/Magento/b']],
                'Unable to uninstall. Please resolve the following issues:' . PHP_EOL .
                'frontend/Magento/a, frontend/Magento/b are parents of virtual theme.'
                . ' Parent themes cannot be uninstalled.'
            ],
            [
                false,
                true,
                ['theme' => ['frontend/Magento/a']],
                'Unable to uninstall. Please resolve the following issues:' . PHP_EOL .
                'frontend/Magento/a is a parent of physical theme. Parent themes cannot be uninstalled.'
            ],
            [
                false,
                true,
                ['theme' => ['frontend/Magento/a', 'frontend/Magento/b']],
                'Unable to uninstall. Please resolve the following issues:' . PHP_EOL .
                'frontend/Magento/a, frontend/Magento/b are parents of physical theme.'
                . ' Parent themes cannot be uninstalled.'
            ],
            [
                true,
                true,
                ['theme' => ['frontend/Magento/a']],
                'Unable to uninstall. Please resolve the following issues:' . PHP_EOL .
                'frontend/Magento/a is a parent of virtual theme. Parent themes cannot be uninstalled.' . PHP_EOL .
                'frontend/Magento/a is a parent of physical theme. Parent themes cannot be uninstalled.'
            ],
            [
                true,
                true,
                ['theme' => ['frontend/Magento/a', 'frontend/Magento/b']],
                'frontend/Magento/a, frontend/Magento/b are parents of virtual theme.'
                . ' Parent themes cannot be uninstalled.' . PHP_EOL .
                'frontend/Magento/a, frontend/Magento/b are parents of physical theme.'
                . ' Parent themes cannot be uninstalled.'
            ],
        ];
    }

    public function testExecuteFailedDependencyCheck()
    {
        $this->setUpPassValidation();
        $this->setupPassThemeInUseCheck();
        $this->setupPassChildThemeCheck();
        $this->dependencyChecker->expects($this->once())
            ->method('checkDependencies')
            ->willReturn(['magento/theme-a' => ['magento/theme-b', 'magento/theme-c']]);
        $this->tester->execute(['theme' => ['frontend/Magento/a']]);
        $this->assertContains(
            'Unable to uninstall. Please resolve the following issues:' . PHP_EOL .
            'frontend/Magento/a has the following dependent package(s):'
            . PHP_EOL . "\tmagento/theme-b" . PHP_EOL . "\tmagento/theme-c",
            $this->tester->getDisplay()
        );
    }

    public function setUpExecute()
    {
        $this->setUpPassValidation();
        $this->setupPassThemeInUseCheck();
        $this->setupPassChildThemeCheck();
        $this->setupPassDependencyCheck();
        $this->remove->expects($this->once())->method('remove');
        $this->cache->expects($this->once())->method('clean');
        $theme = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false);
        $this->themeProvider->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn($theme);
    }

    public function testExecuteWithBackupCode()
    {
        $this->setUpExecute();
        $backupRollback = $this->getMock('Magento\Framework\Setup\BackupRollback', [], [], '', false);
        $this->backupRollbackFactory->expects($this->once())
            ->method('create')
            ->willReturn($backupRollback);
        $this->tester->execute(['theme' => ['test'], '--backup-code' => true]);
        $this->tester->getDisplay();
    }

    public function testExecute()
    {
        $this->setUpExecute();
        $this->cleanupFiles->expects($this->never())->method('clearMaterializedViewFiles');
        $this->tester->execute(['theme' => ['test']]);
        $this->assertContains('Enabling maintenance mode', $this->tester->getDisplay());
        $this->assertContains('Disabling maintenance mode', $this->tester->getDisplay());
        $this->assertContains('Alert: Generated static view files were not cleared.', $this->tester->getDisplay());
        $this->assertNotContains('Generated static view files cleared successfully', $this->tester->getDisplay());
    }

    public function testExecuteCleanStaticFiles()
    {
        $this->setUpExecute();
        $this->cleanupFiles->expects($this->once())->method('clearMaterializedViewFiles');
        $this->tester->execute(['theme' => ['test'], '-c' => true]);
        $this->assertContains('Enabling maintenance mode', $this->tester->getDisplay());
        $this->assertContains('Disabling maintenance mode', $this->tester->getDisplay());
        $this->assertNotContains('Alert: Generated static view files were not cleared.', $this->tester->getDisplay());
        $this->assertContains('Generated static view files cleared successfully', $this->tester->getDisplay());
    }
}
