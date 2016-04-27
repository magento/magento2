<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Test\Unit\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\SampleData\Console\Command\SampleDataDeployCommand;
use Symfony\Component\Console\Tester\CommandTester;

class SampleDataDeployCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array     $sampleDataPackages
     * @param int       $appRunResult - int 0 if everything went fine, or an error code
     * @param string    $expectedMsg
     * @return          void
     *
     * @dataProvider processDataProvider
     */
    public function testExecute(array $sampleDataPackages, $appRunResult, $expectedMsg)
    {
        $directoryRead = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $directoryRead->expects($this->any())->method('getAbsolutePath')->willReturn('/path/to/composer.json');

        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::ROOT)
            ->willReturn($directoryRead);

        $sampleDataDependency = $this->getMock('Magento\SampleData\Model\Dependency', [], [], '', false);
        $sampleDataDependency
            ->expects($this->any())
            ->method('getSampleDataPackages')
            ->willReturn($sampleDataPackages);

        $arrayInputFactory = $this
            ->getMock('Symfony\Component\Console\Input\ArrayInputFactory', ['create'], [], '', false);
        $arrayInputFactory->expects($this->never())->method('create');

        array_walk($sampleDataPackages, function (&$v, $k) {
            $v = "$k:$v";
        });

        $packages = array_values($sampleDataPackages);

        $requireArgs = [
            'command'       => 'require',
            '--working-dir' => '/path/to/composer.json',
            '--no-progress' => 1,
            'packages'      => $packages,
        ];
        $commandInput = new \Symfony\Component\Console\Input\ArrayInput($requireArgs);

        $application = $this->getMock('Composer\Console\Application', [], [], '', false);
        $application->expects($this->any())->method('run')
            ->with($commandInput, $this->anything())
            ->willReturn($appRunResult);
        if (($appRunResult !== 0) && !empty($sampleDataPackages)) {
            $application->expects($this->once())->method('resetComposer')->willReturnSelf();
        }
        $applicationFactory = $this->getMock('Composer\Console\ApplicationFactory', ['create'], [], '', false);
        $applicationFactory->expects($this->any())->method('create')->willReturn($application);

        $commandTester = new CommandTester(
            new SampleDataDeployCommand($filesystem, $sampleDataDependency, $arrayInputFactory, $applicationFactory)
        );
        $commandTester->execute([]);

        $this->assertEquals($expectedMsg, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'sampleDataPackages' => [],
                'appRunResult' => 1,
                'expectedMsg' => 'There is no sample data for current set of modules.' . PHP_EOL,
            ],
            [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 1,
                'expectedMsg' => 'There is an error during sample data deployment. Composer file will be reverted.'
                    . PHP_EOL,
            ],
            [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 0,
                'expectedMsg' => '',
            ],
        ];
    }
}
