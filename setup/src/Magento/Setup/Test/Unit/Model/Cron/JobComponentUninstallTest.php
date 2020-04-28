<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Cron\Helper\ModuleUninstall;
use Magento\Setup\Model\Cron\Helper\ThemeUninstall;
use Magento\Setup\Model\Cron\JobComponentUninstall;
use Magento\Setup\Model\Cron\Queue;
use Magento\Setup\Model\Cron\Status;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Model\Updater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JobComponentUninstallTest extends TestCase
{
    /**
     * @var JobComponentUninstall
     */
    private $job;

    /**
     * @var MockObject|OutputInterface
     */
    private $output;

    /**
     * @var MockObject|Status
     */
    private $status;

    /**
     * @var MockObject|Updater
     */
    private $updater;

    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MockObject|ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var MockObject|ModuleUninstall
     */
    private $moduleUninstallHelper;

    /**
     * @var MockObject|ThemeUninstall
     */
    private $themeUninstallHelper;

    /**
     * @var MockObject|ComposerInformation
     */
    private $composerInformation;

    /**
     * @var MockObject|Queue
     */
    private $quence;

    protected function setUp(): void
    {
        $this->output = $this->getMockForAbstractClass(
            OutputInterface::class,
            [],
            '',
            false
        );
        $this->status = $this->createMock(Status::class);
        $this->moduleUninstallHelper = $this->createMock(ModuleUninstall::class);
        $this->themeUninstallHelper = $this->createMock(ThemeUninstall::class);
        $this->composerInformation = $this->createMock(ComposerInformation::class);
        $this->objectManagerProvider =
            $this->createMock(ObjectManagerProvider::class);
        $this->objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );

        $packageInfoFactory = $this->createMock(PackageInfoFactory::class);
        $packageInfo = $this->createMock(PackageInfo::class);
        $packageInfoFactory->expects($this->any())->method('create')->willReturn($packageInfo);
        $this->objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->updater = $this->createMock(Updater::class);
        $this->quence = $this->createPartialMock(Queue::class, ['addJobs']);
    }

    private function setUpUpdater()
    {
        $this->updater->expects($this->any())->method('createUpdaterTask')->willReturn('');
    }

    private function setUpQuence()
    {
        $this->quence->expects($this->once())->method('addJobs');
    }

    public function testExecuteModule()
    {
        $this->setUpUpdater();
        $this->setUpQuence();
        $this->moduleUninstallHelper->expects($this->once())
            ->method('uninstall')
            ->with($this->output, 'vendor/module-package', true);

        $this->job = new JobComponentUninstall(
            $this->composerInformation,
            $this->moduleUninstallHelper,
            $this->themeUninstallHelper,
            $this->objectManagerProvider,
            $this->output,
            $this->quence,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_NAME => 'vendor/module-package',
                    ]
                ],
                'dataOption' => 'true'
            ]
        );

        $this->composerInformation->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn(['vendor/module-package' => ['type' => ComposerInformation::MODULE_PACKAGE_TYPE]]);
        $this->job->execute();
    }

    public function testExecuteLanguage()
    {
        $this->setUpUpdater();
        $this->setUpQuence();
        $this->composerInformation->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn(['vendor/language-a' => ['type' =>  ComposerInformation::LANGUAGE_PACKAGE_TYPE]]);

        $this->moduleUninstallHelper->expects($this->never())->method($this->anything());
        $this->themeUninstallHelper->expects($this->never())->method($this->anything());

        $this->job = new JobComponentUninstall(
            $this->composerInformation,
            $this->moduleUninstallHelper,
            $this->themeUninstallHelper,
            $this->objectManagerProvider,
            $this->output,
            $this->quence,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_NAME => 'vendor/language-a',
                    ]
                ]
            ]
        );
        $this->job->execute();
    }

    public function testExecuteTheme()
    {
        $this->setUpUpdater();
        $this->setUpQuence();
        $this->composerInformation->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn(['vendor/theme-a' => ['type' => ComposerInformation::THEME_PACKAGE_TYPE]]);
        $this->themeUninstallHelper->expects($this->once())
            ->method('uninstall')
            ->with($this->output, 'vendor/theme-a');
        $this->moduleUninstallHelper->expects($this->never())->method($this->anything());

        $this->job = new JobComponentUninstall(
            $this->composerInformation,
            $this->moduleUninstallHelper,
            $this->themeUninstallHelper,
            $this->objectManagerProvider,
            $this->output,
            $this->quence,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_NAME => 'vendor/theme-a',
                    ]
                ]
            ]
        );
        $this->job->execute();
    }

    public function testExecuteUnknownType()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unknown component type');
        $this->setUpUpdater();
        $this->composerInformation->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn(['vendor/unknown-a' => ['type' => 'unknown']]);

        $this->moduleUninstallHelper->expects($this->never())->method($this->anything());
        $this->themeUninstallHelper->expects($this->never())->method($this->anything());

        $this->job = new JobComponentUninstall(
            $this->composerInformation,
            $this->moduleUninstallHelper,
            $this->themeUninstallHelper,
            $this->objectManagerProvider,
            $this->output,
            $this->quence,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_NAME => 'vendor/unknown-a',
                    ]
                ]
            ]
        );
        $this->job->execute();
    }

    /**
     * @param array $params
     * @dataProvider executeWrongFormatDataProvider
     */
    public function testExecuteWrongFormat(array $params)
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Job parameter format is incorrect');
        $this->moduleUninstallHelper->expects($this->never())->method($this->anything());
        $this->themeUninstallHelper->expects($this->never())->method($this->anything());

        $this->job = new JobComponentUninstall(
            $this->composerInformation,
            $this->moduleUninstallHelper,
            $this->themeUninstallHelper,
            $this->objectManagerProvider,
            $this->output,
            $this->quence,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            $params
        );
        $this->job->execute();
    }

    /**
     * @return array
     */
    public function executeWrongFormatDataProvider()
    {
        return [
            'empty' => [[]],
            'no name' => [['components' => [['key' => 'value']]]],
            'components not array' => [['components' => '']],
        ];
    }

    public function testExecuteUpdateFails()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('error');
        $this->updater->expects($this->once())->method('createUpdaterTask')->willReturn('error');
        $this->composerInformation->expects($this->once())
            ->method('getInstalledMagentoPackages')
            ->willReturn(['vendor/language-a' => ['type' => ComposerInformation::LANGUAGE_PACKAGE_TYPE]]);

        $this->job = new JobComponentUninstall(
            $this->composerInformation,
            $this->moduleUninstallHelper,
            $this->themeUninstallHelper,
            $this->objectManagerProvider,
            $this->output,
            $this->quence,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_NAME => 'vendor/language-a',
                    ]
                ]
            ]
        );
        $this->job->execute();
    }
}
