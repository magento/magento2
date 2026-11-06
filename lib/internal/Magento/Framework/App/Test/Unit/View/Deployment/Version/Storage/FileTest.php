<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\View\Deployment\Version\Storage;

use Magento\Framework\App\View\Deployment\Version\Storage\File;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $object;

    /**
     * @var MockObject
     */
    private $directory;

    protected function setUp(): void
    {
        $this->directory = $this->createMock(WriteInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->with('fixture_dir')
            ->willReturn($this->directory);
        $this->object = new File($filesystem, 'fixture_dir', 'fixture_file.txt');
    }

    public function testLoad()
    {
        $this->directory->expects($this->once())
            ->method('isReadable')
            ->with('fixture_file.txt')
            ->willReturn(true);
        $this->directory->expects($this->once())
            ->method('readFile')
            ->with('fixture_file.txt')
            ->willReturn('123');
        $this->assertEquals('123', $this->object->load());
    }

    /**
     * Whitespace around the version in deployed_version.txt must be stripped (URLs / JSON embed this value).
     *
     * @param string $rawFromFile
     * @param string $expectedVersion
     * @dataProvider loadTrimsWhitespaceDataProvider
     */
    public function testLoadTrimsWhitespaceFromFileContents($rawFromFile, $expectedVersion)
    {
        $this->directory->expects($this->once())
            ->method('isReadable')
            ->with('fixture_file.txt')
            ->willReturn(true);
        $this->directory->expects($this->once())
            ->method('readFile')
            ->with('fixture_file.txt')
            ->willReturn($rawFromFile);
        $this->assertSame($expectedVersion, $this->object->load());
    }

    /**
     * Cases match PHP trim(): spaces, tabs, CR/LF, NUL, vertical tab at start/end only.
     *
     * @return array
     */
    public static function loadTrimsWhitespaceDataProvider()
    {
        $v = '1774318753872';

        return [
            'trailing LF' => [$v . "\n", $v],
            'trailing CRLF' => [$v . "\r\n", $v],
            'leading LF' => ["\n" . $v, $v],
            'leading and trailing CR' => ["\r" . $v . "\r", $v],
            'spaces around' => ['  ' . $v . '  ', $v],
            'tabs around' => ["\t" . $v . "\t", $v],
            'mixed space tab newline' => [" \t\n\r" . $v . "\r\n\t ", $v],
            'vertical tabs' => ["\x0B" . $v . "\x0B", $v],
            'null bytes on edges' => ["\0" . $v . "\0", $v],
        ];
    }

    public function testSave()
    {
        $this->directory
            ->expects($this->once())
            ->method('writeFile')
            ->with('fixture_file.txt', 'input_data', 'w');
        $this->object->save('input_data');
    }
}
