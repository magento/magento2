<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Test\Unit\Console\Command;

use Magento\Framework\App\Console\MaintenanceModeEnabler;
use Magento\Theme\Console\Command\ThemeUninstallCommand;
use Magento\Theme\Model\Theme\ThemePackageInfo;
use Magento\Theme\Model\Theme\ThemeUninstaller;
use Magento\Theme\Model\Theme\ThemeDependencyChecker;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Setup\BackupRollbackFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThemeUninstallCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Console\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\App\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanupFiles;

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
     * @var ThemeUninstaller|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeUninstaller;

    /**
     * @var ThemeDependencyChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themeDependencyChecker;

    /**
     * @var ThemePackageInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $themePackageInfo;

    /**
     * @var CommandTester
     */
    private $tester;

    protected function setUp()
    {
        $this->maintenanceMode = $this->createMock(\Magento\Framework\App\MaintenanceMode::class);
        $composerInformation = $this->createMock(\Magento\Framework\Composer\ComposerInformation::class);
        $composerInformation->expects($this->any())
            ->method('getRootRequiredPackages')
            ->willReturn(['magento/theme-a', 'magento/theme-b', 'magento/theme-c']);
        $this->dependencyChecker = $this->createMock(\Magento\Framework\Composer\DependencyChecker::class);
        $this->collection = $this->createMock(\Magento\Theme\Model\Theme\Data\Collection::class);
        $this->cache = $this->createMock(\Magento\Framework\App\Cache::class);
        $this->cleanupFiles = $this->createMock(\Magento\Framework\App\State\CleanupFiles::class);
        $this->backupRollbackFactory = $this->createMock(\Magento\Framework\Setup\BackupRollbackFactory::class);
        $this->themeValidator = $this->createMock(\Magento\Theme\Model\ThemeValidator::class);
        $this->themeUninstaller = $this->createMock(\Magento\Theme\Model\Theme\ThemeUninstaller::class);
        $this->themeDependencyChecker = $this->createMock(\Magento\Theme\Model\Theme\ThemeDependencyChecker::class);
        $this->themePackageInfo = $this->createMock(\Magento\Theme\Model\Theme\ThemePackageInfo::class);
        $this->command = new ThemeUninstallCommand(
            $this->cache,
            $this->cleanupFiles,
            $composerInformation,
            $this->maintenanceMode,
            $this->dependencyChecker,
            $this->collection,
            $this->backupRollbackFactory,
            $this->themeValidator,
            $this->themePackageInfo,
            $this->themeUninstaller,
            $this->themeDependencyChecker,
            new MaintenanceModeEnabler($this->maintenanceMode)
        );
        $this->tester = new CommandTester($this->command);
    }

    public function testExecuteFailedValidationNotPackage()
    {
        $this->themePackageInfo->expects($this->at(0))->method('getPackageName')->willReturn('dummy');
        $this->themePackageInfo->expects($this->at(1))->method('getPackageName')->willReturn('magento/theme-a');
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn(
                $this->getMockForAbstractClass(
                    \Magento\Framework\View\Design\ThemeInterface::class,
                    [],
                    '',
                    false
                )
            );
        $this->collection->expects($this->any())->method('hasTheme')->willReturn(true);
        $this->tester->execute(['theme' => ['area/vendor/test1', 'area/vendor/test2']]);
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
        $this->themePackageInfo->expects($this->exactly(2))->method('getPackageName')->willReturn('');
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn(
                $this->getMockForAbstractClass(
                    \Magento\Framework\View\Design\ThemeInterface::class,
                    [],
                    '',
                    false
                )
            );
        $this->collection->expects($this->any())->method('hasTheme')->willReturn(false);
        $this->tester->execute(['theme' => ['area/vendor/test1', 'area/vendor/test2']]);
        $this->assertContains(
            'Unknown theme(s): area/vendor/test1, area/vendor/test2' . PHP_EOL,
            $this->tester->getDisplay()
        );
    }

    public function testExecuteFailedValidationMixed()
    {
        $this->themePackageInfo->expects($this->exactly(4))
            ->method('getPackageName')
            ->will($this->returnValueMap([
                ['area/vendor/test1', 'dummy1'],
                ['area/vendor/test2', 'magento/theme-b'],
                ['area/vendor/test3', ''],
                ['area/vendor/test4', 'dummy2'],
            ]));
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn(
                $this->getMockForAbstractClass(
                    \Magento\Framework\View\Design\ThemeInterface::class,
                    [],
                    '',
                    false
                )
            );
        $this->collection->expects($this->at(1))->method('hasTheme')->willReturn(true);
        $this->collection->expects($this->at(3))->method('hasTheme')->willReturn(true);
        $this->collection->expects($this->at(5))->method('hasTheme')->willReturn(false);
        $this->collection->expects($this->at(7))->method('hasTheme')->willReturn(true);
        $this->tester->execute([
            'theme' => [
                'area/vendor/test1',
                'area/vendor/test2',
                'area/vendor/test3',
                'area/vendor/test4',
            ],
        ]);
        $this->assertContains(
            'area/vendor/test1, area/vendor/test4 are not installed Composer packages',
            $this->tester->getDisplay()
        );
        $this->assertNotContains(
            'area/vendor/test2 is not an installed Composer package',
            $this->tester->getDisplay()
        );
        $this->assertContains(
            'Unknown theme(s): area/vendor/test3' . PHP_EOL,
            $this->tester->getDisplay()
        );
    }

    public function setUpPassValidation()
    {
        $this->themePackageInfo->expects($this->any())->method('getPackageName')->willReturn('magento/theme-a');
        $this->collection->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn(
                $this->getMockForAbstractClass(
                    \Magento\Framework\View\Design\ThemeInterface::class,
                    [],
                    '',
                    false
                )
            );
        $this->themeDependencyChecker->expects($this->any())->method('checkChildTheme')->willReturn([]);
        $this->collection->expects($this->any())->method('hasTheme')->willReturn(true);
    }

    public function setupPassChildThemeCheck()
    {
        $theme = $this->createMock(\Magento\Theme\Model\Theme::class);
        $theme->expects($this->any())->method('hasChildThemes')->willReturn(false);
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
        $this->cache->expects($this->once())->method('clean');

        $this->themeUninstaller->expects($this->once())
            ->method('uninstallRegistry')
            ->with($this->isInstanceOf(\Symfony\Component\Console\Output\OutputInterface::class), $this->anything());
        $this->themeUninstaller->expects($this->once())
            ->method('uninstallCode')
            ->with($this->isInstanceOf(\Symfony\Component\Console\Output\OutputInterface::class), $this->anything());
    }

    public function testExecuteWithBackupCode()
    {
        $this->setUpExecute();
        $backupRollback = $this->createMock(\Magento\Framework\Setup\BackupRollback::class);
        $this->backupRollbackFactory->expects($this->once())
            ->method('create')
            ->willReturn($backupRollback);
        $this->tester->execute(['theme' => ['area/vendor/test'], '--backup-code' => true]);
        $this->tester->getDisplay();
    }

    public function testExecute()
    {
        $this->setUpExecute();
        $this->cleanupFiles->expects($this->never())->method('clearMaterializedViewFiles');
        $this->tester->execute(['theme' => ['area/vendor/test']]);
        $this->assertContains('Enabling maintenance mode', $this->tester->getDisplay());
        $this->assertContains('Disabling maintenance mode', $this->tester->getDisplay());
        $this->assertContains('Alert: Generated static view files were not cleared.', $this->tester->getDisplay());
        $this->assertNotContains('Generated static view files cleared successfully', $this->tester->getDisplay());
    }

    public function testExecuteCleanStaticFiles()
    {
        $this->setUpExecute();
        $this->cleanupFiles->expects($this->once())->method('clearMaterializedViewFiles');
        $this->tester->execute(['theme' => ['area/vendor/test'], '-c' => true]);
        $this->assertContains('Enabling maintenance mode', $this->tester->getDisplay());
        $this->assertContains('Disabling maintenance mode', $this->tester->getDisplay());
        $this->assertNotContains('Alert: Generated static view files were not cleared.', $this->tester->getDisplay());
        $this->assertContains('Generated static view files cleared successfully', $this->tester->getDisplay());
    }

    /**
     * @param $themePath
     * @dataProvider dataProviderThemeFormat
     */
    public function testExecuteWrongThemeFormat($themePath)
    {
        $this->tester->execute(['theme' => [$themePath]]);
        $this->assertContains(
            'Theme path should be specified as full path which is area/vendor/name.',
            $this->tester->getDisplay()
        );
    }

    /**
     * @return array
     */
    public function dataProviderThemeFormat()
    {
        return [
            ['test1'],
            ['/test1'],
            ['test1/'],
            ['/test1/'],
            ['vendor/test1'],
            ['/vendor/test1'],
            ['vendor/test1/'],
            ['/vendor/test1/'],
            ['area/vendor/test1/'],
            ['/area/vendor/test1'],
        ];
    }
}
