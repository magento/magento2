<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Shell\Test\Unit;

use Magento\Framework\OsInfo;
use Magento\Framework\Shell\CommandRendererBackground;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CommandRendererBackgroundTest extends TestCase
{
    /**
     * Test data for command
     *
     * @var string
     */
    protected $testCommand = 'php -r test.php';

    /**
     * @var OsInfo|MockObject
     */
    protected $osInfo;

    protected function setUp(): void
    {
        $this->osInfo = $this->getMockBuilder(OsInfo::class)
            ->getMock();
    }

    /**
     * @dataProvider commandPerOsTypeDataProvider
     * @param bool $isWindows
     * @param string $expectedResults
     */
    public function testRender($isWindows, $expectedResults)
    {
        $this->osInfo->expects($this->once())
            ->method('isWindows')
            ->willReturn($isWindows);

        $commandRenderer = new CommandRendererBackground($this->osInfo);
        $this->assertEquals(
            $expectedResults,
            $commandRenderer->render($this->testCommand)
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
            'windows' => [true, 'start /B "magento background task" ' . $this->testCommand . ' 2>&1'],
            'unix'    => [false, $this->testCommand . ' > /dev/null &'],
        ];
    }
}
