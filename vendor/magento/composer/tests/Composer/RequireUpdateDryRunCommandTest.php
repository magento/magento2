<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\InfoCommand;
use Magento\Composer\RequireUpdateDryRunCommand;

class RequireUpdateDryRunCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MagentoComposerApplication|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $application;

    /**
     * @var InfoCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $infoCommand;

    /**
     * @var RequireUpdateDryRunCommand
     */
    protected $requireUpdateDryRunCommand;

    /**
     * @var string
     */
    private $errorMessage = 'Loading composer repositories with package information
Updating dependencies (including require-dev)
Your requirements could not be resolved to an installable set of packages.

  Problem 1
    - 3rdp/e 1.0.0 requires 3rdp/d 1.0.0 -> no matching package found.
    - 3rdp/e 1.0.0 requires 3rdp/d 1.0.0 -> no matching package found.
    - 3rdp/e 1.0.0 requires 3rdp/d 1.0.0 -> no matching package found.
    - Installation request for 3rdp/e 1.0.0 -> satisfiable by 3rdp/e[1.0.0].

Potential causes:
 - A typo in the package name
 - The package is not available in a stable-enough version according to your minimum-stability setting
   see <https://groups.google.com/d/topic/composer-dev/_g3ASeIFlrc/discussion> for more details.

Read <https://getcomposer.org/doc/articles/troubleshooting.md> for further common problems.';

    /**
     * @var string
     */
    private $packageInfo = [
        'name' => '3rdp/d',
        'descrip.' => 'Plugin project A',
        'versions' => '* 1.0.0, 1.1.0, 1.2.0',
        'keywords' => '',
        'type' => 'library',
        'names' => '3rdp/d',
        'current_version' => '1.0.0',
        'available_versions' => [
            '1.1.0',
            '1.2.0'
        ]
    ];

    protected function setUp()
    {
        $this->application = $this->getMock('Magento\Composer\MagentoComposerApplication', [], [], '', false, false);
        $this->infoCommand = $this->getMock('Magento\Composer\InfoCommand', [], [], '', false, false);

        $this->requireUpdateDryRunCommand = new RequireUpdateDryRunCommand(
            $this->application,
            $this->infoCommand
        );
    }

    public function testRun()
    {
        $this->application->expects($this->exactly(2))->method('runComposerCommand');
        $this->requireUpdateDryRunCommand->run([], '');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage
     */
    public function testRunException()
    {
        $this->application->expects($this->at(1))
            ->method('runComposerCommand')
            ->willThrowException(new \RuntimeException($this->errorMessage));
        $this->infoCommand->expects($this->once())->method('run')->willReturn($this->packageInfo);
        $this->requireUpdateDryRunCommand->run(['3rdp/e 1.2.0'], '');
    }

}
