<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Test\Unit\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\SampleData\Console\Command\SampleDataDeployCommand;
use Magento\Setup\Model\PackagesAuth;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\SampleData\Model\Dependency;
use Symfony\Component\Console\Input\ArrayInputFactory;
use Composer\Console\ApplicationFactory;
use Composer\Console\Application;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SampleDataDeployCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryReadMock;

    /**
     * @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryWriteMock;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var Dependency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sampleDataDependencyMock;

    /**
     * @var ArrayInputFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $arrayInputFactoryMock;

    /**
     * @var Application|\PHPUnit_Framework_MockObject_MockObject
     */
    private $applicationMock;

    /**
     * @var ApplicationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $applicationFactoryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->directoryReadMock = $this->createMock(ReadInterface::class);
        $this->directoryWriteMock = $this->createMock(WriteInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->sampleDataDependencyMock = $this->createMock(Dependency::class);
        $this->arrayInputFactoryMock = $this->createMock(ArrayInputFactory::class);
        $this->applicationMock = $this->createMock(Application::class);
        $this->applicationFactoryMock = $this->createPartialMock(ApplicationFactory::class, ['create']);
    }

    /**
     * @param array     $sampleDataPackages
     * @param int       $appRunResult - int 0 if everything went fine, or an error code
     * @param string    $expectedMsg
     * @param bool      $authExist
     * @return          void
     *
     * @dataProvider processDataProvider
     */
    public function testExecute(array $sampleDataPackages, $appRunResult, $expectedMsg, $authExist)
    {
        $pathToComposerJson = '/path/to/composer.json';

        $this->directoryReadMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn($pathToComposerJson);
        $this->directoryWriteMock->expects($this->once())
            ->method('isExist')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE)
            ->willReturn($authExist);
        $this->directoryWriteMock->expects($authExist ? $this->never() : $this->once())
            ->method('writeFile')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE, '{}');
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($this->directoryReadMock);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::COMPOSER_HOME)
            ->willReturn($this->directoryWriteMock);
        $this->sampleDataDependencyMock->expects($this->any())
            ->method('getSampleDataPackages')
            ->willReturn($sampleDataPackages);
        $this->arrayInputFactoryMock->expects($this->never())
            ->method('create');

        array_walk($sampleDataPackages, function (&$v, $k) {
            $v = "$k:$v";
        });

        $packages = array_values($sampleDataPackages);

        $requireArgs = [
            'command'       => 'require',
            '--working-dir' => $pathToComposerJson,
            '--no-progress' => 1,
            'packages'      => $packages,
        ];
        $commandInput = new \Symfony\Component\Console\Input\ArrayInput($requireArgs);

        $this->applicationMock->expects($this->any())
            ->method('run')
            ->with($commandInput, $this->anything())
            ->willReturn($appRunResult);

        if (($appRunResult !== 0) && !empty($sampleDataPackages)) {
            $this->applicationMock->expects($this->once())->method('resetComposer')->willReturnSelf();
        }

        $this->applicationFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->applicationMock);

        $commandTester = new CommandTester(
            new SampleDataDeployCommand(
                $this->filesystemMock,
                $this->sampleDataDependencyMock,
                $this->arrayInputFactoryMock,
                $this->applicationFactoryMock
            )
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
                'authExist' => true,
            ],
            [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 1,
                'expectedMsg' => 'There is an error during sample data deployment. Composer file will be reverted.'
                    . PHP_EOL,
                'authExist' => false,
            ],
            [
                'sampleDataPackages' => [
                    'magento/module-cms-sample-data' => '1.0.0-beta',
                ],
                'appRunResult' => 0,
                'expectedMsg' => '',
                'authExist' => true,
            ],
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Error in writing Auth file path/to/auth.json. Please check permissions for writing.
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->directoryWriteMock->expects($this->once())
            ->method('isExist')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE)
            ->willReturn(false);
        $this->directoryWriteMock->expects($this->once())
            ->method('writeFile')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE, '{}')
            ->willThrowException(new \Exception('Something went wrong...'));
        $this->directoryWriteMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with(PackagesAuth::PATH_TO_AUTH_FILE)
            ->willReturn('path/to/auth.json');
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::COMPOSER_HOME)
            ->willReturn($this->directoryWriteMock);

        $commandTester = new CommandTester(
            new SampleDataDeployCommand(
                $this->filesystemMock,
                $this->sampleDataDependencyMock,
                $this->arrayInputFactoryMock,
                $this->applicationFactoryMock
            )
        );
        $commandTester->execute([]);
    }
}
