<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
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
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

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

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $composerInformation = $this->getMock('Magento\Framework\Composer\ComposerInformation', [], [], '', false);
        $composerInformation->expects($this->any())
            ->method('getRootRequiredPackages')
            ->willReturn(['magento/theme-a', 'magento/theme-b', 'magento/theme-c']);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
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
        $this->command = new ThemeUninstallCommand(
            $this->cache,
            $this->cleanupFiles,
            $composerInformation,
            $this->maintenanceMode,
            $this->filesystem,
            $this->dependencyChecker,
            $this->collection,
            $this->themeProvider,
            $this->remove,
            $this->backupRollbackFactory,
            $this->themeValidator
        );
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteFailedValidationNotPackage()
    {
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
        $dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        // package name "dummy" is not in root composer.json file
        $dirRead->expects($this->any())
            ->method('readFile')
            ->will($this->returnValueMap(
                [
                    ['test1/composer.json', null, null, '{"name": "dummy1"}'],
                    ['test2/composer.json', null, null, '{"name": "magento/theme-b"}'],
                    ['test4/composer.json', null, null, '{"name": "dummy2"}']
                ]
            ));
        $dirRead->expects($this->any())
            ->method('isExist')
            ->will($this->returnValueMap(
                [
                    ['test1/composer.json', true],
                    ['test2/composer.json', true],
                    ['test3/composer.json', false],
                    ['test4/composer.json', true]
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
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::THEMES)
            ->willReturn($dirRead);
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
