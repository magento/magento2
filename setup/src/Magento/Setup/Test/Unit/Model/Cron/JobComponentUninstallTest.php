<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

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
    }

    public function testExecuteModule()
    {
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
            $this->output,
            $this->status,
            'setup:component:uninstall',
            [
                JobComponentUninstall::COMPONENT_TYPE => JobComponentUninstall::COMPONENT_MODULE,
                JobComponentUninstall::COMPONENT_NAME => ['moduleA'],
            ]
        );
        $this->job->execute();
    }

    public function testExecuteLanguage()
    {
        $uninstaller = $this->getMockForAbstractClass(
            'Magento\Framework\Composer\AbstractComponentUninstaller',
            [],
            '',
            false
        );
        $uninstaller->expects($this->once())->method('uninstall');
        $this->componentUninstallerFactory->expects($this->once())
            ->method('create')
            ->with(JobComponentUninstall::COMPONENT_LANGUAGE)
            ->willReturn($uninstaller);

        $this->job = new JobComponentUninstall(
            $this->componentUninstallerFactory,
            $this->output,
            $this->status,
            'setup:component:uninstall',
            [
                JobComponentUninstall::COMPONENT_TYPE => JobComponentUninstall::COMPONENT_LANGUAGE,
                JobComponentUninstall::COMPONENT_NAME => ['languageA'],
            ]
        );
        $this->job->execute();
    }

    public function testExecuteTheme()
    {
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
            $this->output,
            $this->status,
            'setup:component:uninstall',
            [
                JobComponentUninstall::COMPONENT_TYPE => JobComponentUninstall::COMPONENT_THEME,
                JobComponentUninstall::COMPONENT_NAME => ['themeA'],
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
            $this->output,
            $this->status,
            'setup:component:uninstall',
            [
                JobComponentUninstall::COMPONENT_TYPE => 'unknown',
                JobComponentUninstall::COMPONENT_NAME => ['moduleA'],
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
            $this->output,
            $this->status,
            'setup:component:uninstall',
            $params
        );
        $this->job->execute();
    }

    public function executeWrongFormatDataProvider()
    {
        return [
            'empty' => [[]],
            'no type' => [[JobComponentUninstall::COMPONENT_NAME => ['name']]],
            'no name' => [[JobComponentUninstall::COMPONENT_TYPE => 'type']],
            'name not array' => [
                [
                    JobComponentUninstall::COMPONENT_TYPE => 'type',
                    JobComponentUninstall::COMPONENT_NAME => 'name',
                ]
            ],
        ];
    }
}
