<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Shell;

use Magento\Cron\Shell\CommandRendererBackground;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\OsInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cron\Shell\CommandRendererBackground
 */
class CommandRendererBackgroundTest extends TestCase
{
    /**
     * Test path to Magento's var/log directory
     *
     * @var string
     */
    protected $logPath = '/path/to/magento/var/log/';

    /**
     * Test data for command
     *
     * @var string
     */
    protected $testCommand = 'php -r test.php';

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var OsInfo|MockObject
     */
    protected $osInfo;

    protected function setUp(): void
    {
        $this->osInfo = $this->getMockBuilder(OsInfo::class)
            ->getMock();

        $directoryMock = $this->getMockBuilder(ReadInterface::class)
            ->getMock();
        $directoryMock->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturn($this->logPath);

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($directoryMock);
    }

    /**
     * @covers ::render
     * @dataProvider commandPerOsTypeDataProvider
     *
     * @param bool $isWindows
     * @param string $expectedResults
     * @param string[] $arguments
     */
    public function testRender($isWindows, $expectedResults, $arguments)
    {
        $this->osInfo->expects($this->once())
            ->method('isWindows')
            ->willReturn($isWindows);

        $commandRenderer = new CommandRendererBackground($this->filesystem, $this->osInfo);
        $this->assertEquals(
            $expectedResults,
            $commandRenderer->render($this->testCommand, $arguments)
        );
    }

    /**
     * Data provider for each os type
     *
     * @return array
     */
    public function commandPerOsTypeDataProvider()
    {
        return [
            'windows' => [
                true,
                'start /B "magento background task" ' . $this->testCommand . ' 2>&1',
                [],
            ],
            'unix-without-group-name' => [
                false,
                $this->testCommand . ' >> /dev/null 2>&1 &',
                [],
            ],
            'unix-with-group-name' => [
                false,
                $this->testCommand . " >> '{$this->logPath}magento.cron.group-name.log' 2>&1 &",
                ['php-executable', 'script-path', 'group-name'],
            ],
        ];
    }
}
