<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Test\Helper;

use PHPUnit\Framework\Attributes\DataProvider;

class MemoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $_shell;

    protected function setUp(): void
    {
        $this->_shell = $this->createPartialMock(\Magento\Framework\Shell::class, ['execute']);
    }

    public function testGetRealMemoryUsageUnix()
    {
        $object = $this->createMemory(null);
        $this->_shell->expects(
            $this->once()
        )->method(
            'execute'
        )->with(
            $this->stringStartsWith('ps '),
            [getmypid()]
        )->willReturn(
            '26321'
        );
        $this->assertEquals(26952704, $object->getRealMemoryUsage());
    }

    public function testGetRealMemoryUsageWin()
    {
        $this->_shell
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturnCallback(
                function ($arg1, $arguments) {
                    if (strpos($arg1, 'ps ') === 0) {
                        $this->assertSame([getmypid()], $arguments);
                        throw new \Magento\Framework\Exception\LocalizedException(__('command not found'));
                    }
                    $this->assertSame('tasklist.exe /fi %s /fo CSV /nh', $arg1);
                    $this->assertSame(['PID eq ' . getmypid()], $arguments);
                    return '"php.exe","12345","N/A","0","26,321 K"';
                }
            );

        $object = $this->createMemory(null);
        $this->assertEquals(26952704, $object->getRealMemoryUsage());
    }

    #[DataProvider('unusableProcStatusDataProvider')]
    public function testGetRealMemoryUsageFallsBackToUnix(?string $status): void
    {
        $this->_shell->expects($this->once())->method('execute')
            ->with($this->stringStartsWith('ps '), [getmypid()])
            ->willReturn('26321');

        $object = $this->createMemory($status);
        $this->assertSame(26952704, $object->getRealMemoryUsage());
    }

    public static function unusableProcStatusDataProvider(): array
    {
        return [
            'unavailable' => [null],
            'empty' => [''],
            'missing VmRSS' => ["Name:\tphp\nVmSize:\t12345 kB\n"],
            'invalid VmRSS' => ["VmRSS:\tunknown kB\n"],
            'invalid unit' => ["VmRSS:\t26321 MB\n"],
            'incomplete value' => ["VmRSS:\n26321 kB\n"],
            'different field' => ["OtherVmRSS:\t26321 kB\n"]
        ];
    }

    #[DataProvider('procStatusDataProvider')]
    public function testGetRealMemoryUsageProc(string $status, int $expected): void
    {
        $this->_shell->expects($this->never())->method('execute');
        $object = $this->createMemory($status);

        $this->assertSame($expected, $object->getRealMemoryUsage());
    }

    public static function procStatusDataProvider(): array
    {
        return [
            'kilobytes' => ["Name:\tphp\nVmRSS:\t26321 kB\nVmData:\t12345 kB\n", 26952704],
            'spaces' => ["VmRSS:   42 kB  \n", 43008],
            'zero' => ["VmRSS:\t0 kB\n", 0]
        ];
    }

    public function testReadProcStatusForMissingProcess(): void
    {
        $object = new \Magento\TestFramework\Helper\Memory($this->_shell);
        $method = new \ReflectionMethod(\Magento\TestFramework\Helper\Memory::class, 'readProcStatus');
        $errors = [];
        set_error_handler(static function (int $severity, string $message) use (&$errors): bool {
            $errors[] = [$severity, $message];
            return true;
        });
        try {
            $this->assertNull($method->invoke($object, -1));
        } finally {
            restore_error_handler();
        }
        $this->assertSame([], $errors);
    }

    public function testReadProcStatusForCurrentProcess(): void
    {
        if (!is_readable('/proc/self/status')) {
            $this->markTestSkipped('Readable procfs process status is required.');
        }

        $object = new \Magento\TestFramework\Helper\Memory($this->_shell);
        $method = new \ReflectionMethod(\Magento\TestFramework\Helper\Memory::class, 'readProcStatus');
        $status = $method->invoke($object, getmypid());

        $this->assertIsString($status);
        $this->assertStringContainsString('VmRSS:', $status);
    }

    private function createMemory(?string $status): \Magento\TestFramework\Helper\Memory
    {
        $memory = $this->getMockBuilder(\Magento\TestFramework\Helper\Memory::class)
            ->setConstructorArgs([$this->_shell])
            ->onlyMethods(['readProcStatus'])
            ->getMock();
        $memory->expects($this->once())->method('readProcStatus')->with(getmypid())->willReturn($status);

        return $memory;
    }

    /**
     * @param string $number
     * @param string $expected
     */
    #[DataProvider('convertToBytesDataProvider')]
    public function testConvertToBytes($number, $expected)
    {
        $this->assertEquals($expected, \Magento\TestFramework\Helper\Memory::convertToBytes($number));
    }

    /**
     * @return array
     */
    public static function convertToBytesDataProvider()
    {
        return [
            'B' => ['1B', '1'],
            'KB' => ['3K', '3072'],
            'MB' => ['2M', '2097152'],
            'GB' => ['1G', '1073741824'],
            'regular spaces' => ['1 234 K', '1263616'],
            'no-break spaces' => ["1\xA0234\xA0K", '1263616'],
            'tab' => ["1\x09234\x09K", '1263616'],
            'coma' => ['1,234K', '1263616'],
            'dot' => ['1.234 K', '1263616']
        ];
    }

    /**
     * @param string $number
     */
    #[DataProvider('convertToBytesBadFormatDataProvider')]
    public function testConvertToBytesBadFormat($number)
    {
        $this->expectException(\InvalidArgumentException::class);

        \Magento\TestFramework\Helper\Memory::convertToBytes($number);
    }

    /**
     * @return array
     */
    public static function convertToBytesBadFormatDataProvider()
    {
        return [
            'more than one unit of measure' => ['1234KB'],
            'unknown unit of measure' => ['1234Z'],
            'non-integer value' => ['1,234.56 K']
        ];
    }

    /**
     * @param string $number
     * @param string $expected
     */
    #[DataProvider('convertToBytes64DataProvider')]
    public function testConvertToBytes64($number, $expected)
    {
        if (PHP_INT_SIZE <= 4) {
            $this->markTestSkipped('A 64-bit system is required to perform this test.');
        }
        $this->assertEquals($expected, \Magento\TestFramework\Helper\Memory::convertToBytes($number));
    }

    /**
     * @return array
     */
    public static function convertToBytes64DataProvider()
    {
        return [
            ['2T', '2199023255552'],
            ['1P', '1125899906842624'],
            ['2E', '2305843009213693952']
        ];
    }

    /**
     */
    public function testConvertToBytesInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Magento\TestFramework\Helper\Memory::convertToBytes('3Z');
    }

    /**
     */
    public function testConvertToBytesOutOfBounds()
    {
        $this->expectException(\OutOfBoundsException::class);

        if (PHP_INT_SIZE > 4) {
            $this->markTestSkipped('A 32-bit system is required to perform this test.');
        }
        \Magento\TestFramework\Helper\Memory::convertToBytes('2P');
    }
}
