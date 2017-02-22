<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Test\Unit\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\SampleData\Console\Command\SampleDataDeployCommand;
use Symfony\Component\Console\Tester\CommandTester;

class SampleDataDeployCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $this->markTestSkipped('MAGETWO-43636: This test should be fixed by NORD team');
        $directoryRead = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $directoryRead->expects($this->atLeastOnce())->method('getAbsolutePath')->willReturn('/path/to/composer.json');

        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->atLeastOnce())->method('getDirectoryRead')->with(DirectoryList::ROOT)
            ->willReturn($directoryRead);

        $sampleDataDependency = $this->getMock('Magento\SampleData\Model\Dependency', [], [], '', false);
        $sampleDataDependency->expects($this->atLeastOnce())->method('getSampleDataPackages')->willReturn(
            [
                'magento/module-cms-sample-data' => '1.0.0-beta'
            ]
        );

        $arrayInput = $this->getMock('Symfony\Component\Console\Input\ArrayInput', [], [], '', false);

        $arrayInputFactory = $this
            ->getMock('Symfony\Component\Console\Input\ArrayInputFactory', ['create'], [], '', false);
        $requireArgs = [
            'command' => 'require',
            '--working-dir' => '/path/to/composer.json',
            '--no-interaction' => 1,
            '--no-progress' => 1,
            'packages' => ['magento/module-cms-sample-data:1.0.0-beta']
        ];
        $arrayInputFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(['parameters' => $requireArgs])
            ->willReturn($arrayInput);
        $application = $this->getMock('Composer\Console\Application', [], [], '', false);
        $application->expects($this->atLeastOnce())->method('run')->with($arrayInput, $this->anything())
            ->willReturnCallback(function ($input, $output) {
                $input->getFirstArgument();
                $output->writeln('./composer.json has been updated');
            });

        $applicationFactory = $this->getMock('Composer\Console\ApplicationFactory', ['create'], [], '', false);
        $applicationFactory->expects($this->atLeastOnce())->method('create')->willReturn($application);

        $commandTester = new CommandTester(
            new SampleDataDeployCommand($filesystem, $sampleDataDependency, $arrayInputFactory, $applicationFactory)
        );
        $commandTester->execute([]);

        $expectedMsg = './composer.json has been updated' . PHP_EOL;

        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }
}
