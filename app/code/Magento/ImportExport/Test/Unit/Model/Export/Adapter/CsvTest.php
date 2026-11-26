<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Export\Adapter;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\Write;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    public function testOrdinaryCompletionReturnsContents(): void
    {
        $directory = $this->createMock(WriteInterface::class);
        $file = $this->createMock(Write::class);
        $file->expects(self::once())->method('flush');
        $file->expects(self::once())->method('close');
        $directory->expects(self::once())->method('readFile')
            ->with('export/probe.csv.tmp')
            ->willReturn("sku\nprobe\n");
        $directory->expects(self::never())->method('stat');

        self::assertSame("sku\nprobe\n", $this->createWriter($directory, $file)->getContents());
    }

    #[DataProvider('fileCompletionDataProvider')]
    public function testFileCompletionDoesNotReadContents(int $size, string $expected): void
    {
        $directory = $this->createMock(WriteInterface::class);
        $file = $this->createMock(Write::class);
        $file->expects(self::once())->method('flush');
        $file->expects(self::once())->method('close');
        $directory->expects(self::never())->method('readFile');
        $directory->expects(self::once())->method('stat')
            ->with('export/probe.csv.tmp')
            ->willReturn(['size' => $size]);
        $writer = $this->createWriter($directory, $file, true);

        self::assertSame($expected, $writer->getContents());
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function fileCompletionDataProvider(): array
    {
        return [
            'empty' => [0, ''],
            'nonempty' => [10, '__RESULT_WRITTEN_TO_FILE__'],
            'larger than PHP memory' => [3221225472, '__RESULT_WRITTEN_TO_FILE__']
        ];
    }

    private function createWriter(WriteInterface $directory, Write $file, bool $fileExport = false): Csv
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($directory);
        $directory->method('isWritable')->willReturn(true);
        $directory->method('openFile')->with('export/probe.csv.tmp', 'w')->willReturn($file);

        return new Csv($filesystem, 'export/probe.csv.tmp', DirectoryList::VAR_IMPORT_EXPORT, $fileExport);
    }
}
