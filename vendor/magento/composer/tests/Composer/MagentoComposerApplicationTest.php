<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Composer\Console\Application;
use Magento\Composer\MagentoComposerApplication;
use Magento\Composer\ConsoleArrayInputFactory;
use Symfony\Component\Console\Output\BufferedOutput;

class MagentoComposerApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MagentoComposerApplication
     */
    protected $application;

    /**
     * @var Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $composerApplication;

    /**
     * @var ConsoleArrayInputFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inputFactory;

    /**
     * @var BufferedOutput|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $consoleOutput;

    protected function setUp()
    {
        $this->composerApplication = $this->getMock(
            'Composer\Console\Application',
            [
                'resetComposer',
                'create',
                'run'
            ],
            [],
            '',
            false,
            false
        );
        $this->inputFactory = $this->getMock('Magento\Composer\ConsoleArrayInputFactory', [], [], '', false);
        $this->consoleOutput = $this->getMock('Symfony\Component\Console\Output\BufferedOutput', [], [], '', false);

        $this->application = new MagentoComposerApplication(
            'path1',
            'path2',
            $this->composerApplication,
            $this->inputFactory,
            $this->consoleOutput
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Command "update" failed
     */
    function testWrongExitCode()
    {
        $this->composerApplication->expects($this->once())->method('run')->willReturn(1);

        $this->application->runComposerCommand(['command'=>'update']);
    }

    function testRunCommand()
    {
        $inputData = ['command' => 'update', MagentoComposerApplication::COMPOSER_WORKING_DIR => '.'];

        $this->composerApplication->expects($this->once())->method('resetComposer');

        $this->inputFactory->expects($this->once())->method('create')->with($inputData);

        $this->consoleOutput->expects($this->once())->method('fetch')->willReturn('Nothing to update');

        $this->composerApplication->expects($this->once())->method('run')->willReturn(0);

        $message = $this->application->runComposerCommand($inputData);
        $this->assertEquals('Nothing to update', $message);
    }
}
