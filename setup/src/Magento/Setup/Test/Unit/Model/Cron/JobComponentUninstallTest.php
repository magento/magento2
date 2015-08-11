<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\Cron\ComponentUninstallerFactory;
use Magento\Setup\Model\Cron\JobComponentUninstall;

class JobComponentUninstallTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobComponentUninstall
     */
    private $job;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Cron\Status
     */
    private $status;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ComponentUninstallerFactory
     */
    private $componentUninstallerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Updater
     */
    private $updater;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ObjectManagerProvider
     */
    private $objectManagerProvider;

    public function setUp()
    {
        $this->output = $this->getMockForAbstractClass(
            'Symfony\Component\Console\Output\OutputInterface',
            [],
            '',
            false
        );
        $this->status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $this->componentUninstallerFactory = $this->getMock(
            'Magento\Setup\Model\Cron\ComponentUninstallerFactory',
            [],
            [],
            '',
            false
        );
        $this->objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );

        $packageInfoFactory = $this->getMock('Magento\Framework\Module\PackageInfoFactory', [], [], '', false);
        $packageInfo = $this->getMock('Magento\Framework\Module\PackageInfo', [], [], '', false);
        $packageInfoFactory->expects($this->once())->method('create')->willReturn($packageInfo);
        $cache = $this->getMock('Magento\Framework\App\Cache', [], [], '', false);
        $cleanupFiles = $this->getMock('Magento\Framework\App\State\CleanupFiles', [], [], '', false);
        $cache->expects($this->any())->method('clean');
        $cleanupFiles->expects($this->any())->method('clearCodeGeneratedClasses');
        $cleanupFiles->expects($this->any())->method('clearMaterializedViewFiles');
        $this->objectManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['Magento\Framework\App\Cache', $cache],
                        ['Magento\Framework\App\State\CleanupFiles', $cleanupFiles],
                        ['Magento\Framework\Module\PackageInfoFactory', $packageInfoFactory],
                    ]
                )
            );
        $this->objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
    }

    private function setUpUpdater()
    {
        $this->updater->expects($this->any())->method('createUpdaterTask')->willReturn('');
    }

    public function testExecuteModule()
    {
        $this->setUpUpdater();
        $uninstaller = $this->getMockForAbstractClass(
            'Magento\Framework\Composer\AbstractComponentUninstaller',
            [],
            '',
            false
        );
        $uninstaller->expects($this->once())->method('uninstall');
        $this->componentUninstallerFactory->expects($this->once())
            ->method('create')
            ->with(JobComponentUninstall::COMPONENT_MODULE)
            ->willReturn($uninstaller);

        $this->job = new JobComponentUninstall(
            $this->componentUninstallerFactory,
            $this->objectManagerProvider,
            $this->output,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_TYPE => JobComponentUninstall::COMPONENT_MODULE,
                        JobComponentUninstall::COMPONENT_NAME => 'moduleA',
                    ]
                ],
                'dataOption' => true
            ]
        );
        $this->job->execute();
    }

    public function testExecuteLanguage()
    {
        $this->setUpUpdater();
        $this->componentUninstallerFactory->expects($this->never())->method('create');

        $this->job = new JobComponentUninstall(
            $this->componentUninstallerFactory,
            $this->objectManagerProvider,
            $this->output,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_TYPE => JobComponentUninstall::COMPONENT_LANGUAGE,
                        JobComponentUninstall::COMPONENT_NAME => 'languageA',
                    ]
                ]
            ]
        );
        $this->job->execute();
    }

    public function testExecuteTheme()
    {
        $this->setUpUpdater();
        $uninstaller = $this->getMockForAbstractClass(
            'Magento\Framework\Composer\AbstractComponentUninstaller',
            [],
            '',
            false
        );
        $uninstaller->expects($this->once())->method('uninstall');
        $this->componentUninstallerFactory->expects($this->once())
            ->method('create')
            ->with(JobComponentUninstall::COMPONENT_THEME)
            ->willReturn($uninstaller);

        $this->job = new JobComponentUninstall(
            $this->componentUninstallerFactory,
            $this->objectManagerProvider,
            $this->output,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_TYPE => JobComponentUninstall::COMPONENT_THEME,
                        JobComponentUninstall::COMPONENT_NAME => 'themeA',
                    ]
                ]
            ]
        );
        $this->job->execute();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown component type
     */
    public function testExecuteUnknownType()
    {
        $this->componentUninstallerFactory->expects($this->never())->method($this->anything());
        $this->job = new JobComponentUninstall(
            $this->componentUninstallerFactory,
            $this->objectManagerProvider,
            $this->output,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_TYPE => 'unknown',
                        JobComponentUninstall::COMPONENT_NAME => 'moduleA',
                    ]
                ]
            ]
        );
        $this->job->execute();
    }

    /**
     * @param array $params
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Job parameter format is incorrect
     * @dataProvider executeWrongFormatDataProvider
     */
    public function testExecuteWrongFormat(array $params)
    {
        $this->componentUninstallerFactory->expects($this->never())->method($this->anything());
        $this->job = new JobComponentUninstall(
            $this->componentUninstallerFactory,
            $this->objectManagerProvider,
            $this->output,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            $params
        );
        $this->job->execute();
    }

    public function executeWrongFormatDataProvider()
    {
        return [
            'empty' => [[]],
            'no type' => [['components' => [[JobComponentUninstall::COMPONENT_NAME => 'name']]]],
            'no name' => [['components' => [[JobComponentUninstall::COMPONENT_TYPE => 'type']]]],
            'components not array' => [['components' => '']],
        ];
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage error
     */
    public function testExecuteUpdateFails()
    {
        $this->updater->expects($this->once())->method('createUpdaterTask')->willReturn('error');
        $uninstaller = $this->getMockForAbstractClass(
            'Magento\Framework\Composer\AbstractComponentUninstaller',
            [],
            '',
            false
        );
        $uninstaller->expects($this->once())->method('uninstall');
        $this->componentUninstallerFactory->expects($this->once())
            ->method('create')
            ->with(JobComponentUninstall::COMPONENT_MODULE)
            ->willReturn($uninstaller);

        $this->job = new JobComponentUninstall(
            $this->componentUninstallerFactory,
            $this->objectManagerProvider,
            $this->output,
            $this->status,
            $this->updater,
            'setup:component:uninstall',
            [
                'components' => [
                    [
                        JobComponentUninstall::COMPONENT_TYPE => JobComponentUninstall::COMPONENT_MODULE,
                        JobComponentUninstall::COMPONENT_NAME => 'moduleA',
                    ]
                ]
            ]
        );
        $this->job->execute();
    }
}
