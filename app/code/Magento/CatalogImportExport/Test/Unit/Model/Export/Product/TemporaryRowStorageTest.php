<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Export\Product;

use Magento\CatalogImportExport\Model\Export\Product\TemporaryRowStorage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Covers anonymous export staging without accessing the filesystem.
 */
class TemporaryRowStorageTest extends TestCase
{
    /**
     * @var WriteInterface|MockObject
     */
    private WriteInterface $directory;

    /**
     * @var TemporaryRowStorage|null
     */
    private ?TemporaryRowStorage $storage = null;

    protected function setUp(): void
    {
        $this->directory = $this->createMock(WriteInterface::class);
    }

    protected function tearDown(): void
    {
        $this->storage?->close();
    }

    public function testLosslessRecordsMergeByLinkIdWithDefaultStoreFirst(): void
    {
        $storage = $this->createStorage([$this->createMemoryStream()]);
        $first = $this->record(202, 900, [
            'binary' => "\0\xff\n,\"",
            'null' => null,
            'false' => false,
            'true' => true,
            'zero' => 0,
            'float' => 1.25,
            'numeric_string' => '00123',
            'empty' => '',
            'multiselect' => ['color' => ['01', '2'], 'size' => []]
        ]);
        $second = $this->record(101, 1000, ['sku' => 'second']);
        $firstStore = $this->record(202, 900, ['sku' => 'localized']);
        $storeOnly = $this->record(303, 1100, ['sku' => 'store-only']);
        $secondStore = $this->record(101, 1000, ['sku' => 'other-store']);
        $storage->append(0, $first);
        $storage->append(0, $second);
        $storage->append(7, $firstStore);
        $storage->append(7, $storeOnly);
        $storage->append(3, $secondStore);

        self::assertSame($first, $storage->getDefaultRecord(202));
        self::assertNull($storage->getDefaultRecord(900));
        self::assertNull($storage->getDefaultRecord(303));
        self::assertSame([
            [0 => $first, 7 => $firstStore],
            [0 => $second, 3 => $secondStore],
            [7 => $storeOnly]
        ], iterator_to_array($storage->getProducts(), false));
    }

    public function testDefaultLookupAcrossBufferedOffsetsAndPartialWrites(): void
    {
        $storage = $this->createStorage([$this->createMemoryStream(4093)]);
        $records = [
            $this->record(11, 101, ['value' => str_repeat("\0a", 20000)]),
            $this->record(22, 202, ['value' => str_repeat("\xffb", 20000)]),
            $this->record(33, 303, ['value' => 'buffered-tail'])
        ];
        foreach ($records as $record) {
            $storage->append(0, $record);
        }

        foreach ([2, 0, 1, 2] as $index) {
            self::assertSame($records[$index], $storage->getDefaultRecord(($index + 1) * 11));
        }
        self::assertSame(
            array_map(static fn (array $record): array => [0 => $record], $records),
            iterator_to_array($storage->getProducts(), false)
        );
    }

    #[DataProvider('writeBufferSizeProvider')]
    public function testFlushesAtConfiguredBufferSize(int $bufferedRecords, array $writtenRecords): void
    {
        $stream = $this->createMemoryStream();
        $first = $this->record(11, 101);
        $second = $this->record(22, 202);
        $frameLength = 8 + strlen((new Serialize())->serialize($first));
        $storage = $this->createStorage([$stream], [], $bufferedRecords * $frameLength);

        $storage->append(0, $first);
        self::assertSame($writtenRecords[0] * $frameLength, $stream->tell());
        $storage->append(0, $second);
        self::assertSame($writtenRecords[1] * $frameLength, $stream->tell());
        self::assertSame(
            [[0 => $first], [0 => $second]],
            iterator_to_array($storage->getProducts(), false)
        );
    }

    public static function writeBufferSizeProvider(): array
    {
        return [
            'each record' => [1, [1, 2]],
            'two records' => [2, [0, 2]],
            'pending tail' => [3, [0, 0]]
        ];
    }

    #[DataProvider('seekBufferSizeProvider')]
    public function testDefaultReadsDoNotChangeAppendOrStoreReadOffsets(int $writeBufferSize): void
    {
        $storage = $this->createStorage([$this->createMemoryStream(17)], [], $writeBufferSize);
        $first = $this->record(11, 101, ['value' => "\0first\xff"]);
        $second = $this->record(22, 202, ['value' => 'second']);
        $localized = $this->record(11, 101, ['value' => 'localized']);
        $storeOnly = $this->record(33, 303, ['value' => 'store-only']);
        $lastStore = $this->record(22, 202, ['value' => 'last-store']);

        $storage->append(0, $first);
        self::assertSame($first, $storage->getDefaultRecord(11));
        $storage->append(0, $second);
        self::assertSame($first, $storage->getDefaultRecord(11));
        $storage->append(1, $localized);
        self::assertSame($second, $storage->getDefaultRecord(22));
        $storage->append(1, $storeOnly);
        self::assertSame($first, $storage->getDefaultRecord(11));
        $storage->append(3, $lastStore);

        $expected = [
            [0 => $first, 1 => $localized],
            [0 => $second, 3 => $lastStore],
            [1 => $storeOnly]
        ];
        self::assertSame($expected, iterator_to_array($storage->getProducts(), false));
        self::assertSame($first, $storage->getDefaultRecord(11));
        self::assertSame($expected, iterator_to_array($storage->getProducts(), false));
    }

    public static function seekBufferSizeProvider(): array
    {
        return [
            'flush before every lookup' => [1],
            'flush across records and seeks' => [200],
            'buffered store tail' => [65536]
        ];
    }

    public function testManyStoresShareOneStream(): void
    {
        $storage = $this->createStorage([$this->createMemoryStream()]);
        $expected = [];
        for ($storeId = 0; $storeId < 160; ++$storeId) {
            $expected[$storeId] = $this->record(11, 101, ['value' => $storeId]);
            $storage->append($storeId, $expected[$storeId]);
        }

        self::assertSame([$expected], iterator_to_array($storage->getProducts(), false));
    }

    public function testEmptyStorageDoesNotOpenAStream(): void
    {
        $storage = $this->createStorage([]);
        $this->directory->expects(self::never())->method('openFile');

        self::assertSame([], iterator_to_array($storage->getProducts(), false));
        self::assertNull($storage->getDefaultRecord(11));
        $storage->close();
    }

    public function testFrameCannotReadIntoTheNextStoreSection(): void
    {
        $stream = $this->createMemoryStream();
        $storage = $this->createStorage([$stream], [], 1);
        $storage->append(0, $this->record(11, 101));
        $storage->append(1, $this->record(11, 101, ['value' => str_repeat('a', 1000)]));
        $stream->seek(0);
        $stream->write(pack('J', 1000));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Incomplete temporary export record.');

        iterator_to_array($storage->getProducts(), false);
    }

    public function testReturningToACompletedStoreIsRejected(): void
    {
        $storage = $this->createStorage([$this->createMemoryStream()]);
        $storage->append(0, $this->record(11, 101));
        $storage->append(1, $this->record(11, 101));
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Temporary export stores must be staged sequentially.');

        $storage->append(0, $this->record(22, 202));
    }

    public function testRejectsObjectValuesBeforeOpeningAStream(): void
    {
        $storage = $this->createStorage([]);
        $this->directory->expects(self::never())->method('openFile');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Temporary export records must contain only scalar values.');

        $storage->append(0, $this->record(1, 10, ['nested' => [new \stdClass()]]));
    }

    #[DataProvider('failedWriteProvider')]
    public function testFailedWritesAreSignaled(int|bool $written): void
    {
        $stream = $this->createMock(FileWriteInterface::class);
        $stream->expects(self::once())->method('write')->willReturn($written);
        $stream->expects(self::once())->method('close');
        $storage = $this->createStorage([$stream]);
        $storage->append(0, $this->record(1, 10));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to write a temporary export record.');

        try {
            iterator_to_array($storage->getProducts());
        } finally {
            $storage->close();
        }
    }

    public static function failedWriteProvider(): array
    {
        return ['zero' => [0], 'false' => [false], 'negative' => [-1]];
    }

    #[DataProvider('invalidFrameProvider')]
    public function testInvalidFramesAreSignaled(array $reads, string $message): void
    {
        $stream = $this->createMock(FileWriteInterface::class);
        $stream->method('write')->willReturnCallback(static fn (string $data): int => strlen($data));
        $stream->expects(self::exactly(count($reads)))->method('read')->willReturnOnConsecutiveCalls(...$reads);
        $stream->method('eof')->willReturn(false);
        $storage = $this->createStorage([$stream]);
        $storage->append(0, $this->record(1, 10));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($message);

        iterator_to_array($storage->getProducts());
    }

    public static function invalidFrameProvider(): array
    {
        return [
            'truncated header' => [['short'], 'Incomplete temporary export record header.'],
            'truncated payload' => [[pack('J', 20), 'short', ''], 'Incomplete temporary export record.'],
            'zero length' => [[pack('J', 0)], 'Invalid temporary export record length.'],
            'non-array payload' => [[pack('J', 2), 'N;'], 'Invalid temporary export record.']
        ];
    }

    public function testUnlinksBeforeWritingAndRemovesOnlyAbandonedEmptyFiles(): void
    {
        $prefix = 'catalog-product-export/';
        $empty = $prefix . 'row-' . str_repeat('a', 32);
        $nonempty = $prefix . 'row-' . str_repeat('b', 32);
        $directory = $prefix . 'row-' . str_repeat('c', 32);
        $unrelated = $prefix . 'row-not-a-generated-name';
        $deleted = [];
        $stream = $this->createMock(FileWriteInterface::class);
        $stream->method('read')->willReturn('');
        $stream->method('eof')->willReturn(true);
        $stream->expects(self::once())->method('write')->willReturnCallback(
            static function (string $data) use (&$deleted): int {
                self::assertCount(2, $deleted);
                return strlen($data);
            }
        );
        $stream->expects(self::once())->method('close');
        $this->directory->method('isFile')->willReturnMap([
            [$empty, true],
            [$nonempty, true],
            [$directory, false]
        ]);
        $this->directory->expects(self::exactly(2))->method('stat')->willReturnMap([
            [$empty, ['size' => 0]],
            [$nonempty, ['size' => 1]]
        ]);
        $this->directory->expects(self::exactly(2))->method('delete')->willReturnCallback(
            static function (string $path) use (&$deleted, $empty): bool {
                if ($deleted === []) {
                    self::assertSame($empty, $path);
                } else {
                    self::assertMatchesRegularExpression(
                        '#^catalog-product-export/row-[a-f0-9]{32}$#D',
                        $path
                    );
                }
                $deleted[] = $path;
                return true;
            }
        );
        $storage = $this->createStorage([$stream], [$empty, $nonempty, $directory, $unrelated], 1);
        $storage->append(0, $this->record(1, 10));
        $storage->close();
        $storage->close();
        self::assertNull($storage->getDefaultRecord(1));
        self::assertSame([], iterator_to_array($storage->getProducts()));
    }

    #[DataProvider('creationFailureProvider')]
    public function testCreationFailuresReleaseHandles(string $method, int $streamCloses): void
    {
        $stream = $this->createMock(FileWriteInterface::class);
        $stream->expects(self::exactly($streamCloses))->method('close');
        $stream->expects(self::never())->method('write');
        $storage = $this->createStorage([$stream]);
        $this->directory->method($method)->willThrowException(new \RuntimeException('creation failed'));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('creation failed');

        $storage->append(0, $this->record(1, 10));
    }

    public static function creationFailureProvider(): array
    {
        return ['directory creation' => ['create', 0], 'unlink' => ['delete', 1]];
    }

    private function createStorage(
        array $streams,
        array $abandoned = [],
        int $writeBufferSize = 65536
    ): TemporaryRowStorage {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects(self::once())->method('getDirectoryWrite')->with(DirectoryList::TMP)
            ->willReturn($this->directory);
        $this->directory->expects(self::exactly(count($streams)))
            ->method('create')
            ->with('catalog-product-export', 0770);
        $this->directory->method('read')->willReturn($abandoned);
        $this->directory->method('openFile')->willReturnCallback(
            function (string $path, string $mode) use (&$streams): FileWriteInterface {
                if ($mode === 'c+') {
                    self::assertSame('catalog-product-export/.lock', $path);
                    $lock = $this->createMock(FileWriteInterface::class);
                    $lock->expects(self::once())->method('lock');
                    $lock->expects(self::once())->method('close');
                    return $lock;
                }
                self::assertSame('x+', $mode);
                self::assertNotEmpty($streams);
                return array_shift($streams);
            }
        );
        $this->storage = new TemporaryRowStorage($filesystem, new Serialize(), $writeBufferSize);
        return $this->storage;
    }

    private function createMemoryStream(int $writeLimit = PHP_INT_MAX): FileWriteInterface
    {
        $bytes = '';
        $position = 0;
        $stream = $this->createStub(FileWriteInterface::class);
        $stream->method('tell')->willReturnCallback(static function () use (&$position): int {
            return $position;
        });
        $stream->method('seek')->willReturnCallback(static function (int $offset) use (&$position): int {
            $position = $offset;
            return 0;
        });
        $stream->method('write')->willReturnCallback(
            static function (string $data) use (&$bytes, &$position, $writeLimit): int {
                $length = min(strlen($data), $writeLimit);
                $bytes = substr_replace($bytes, substr($data, 0, $length), $position, $length);
                $position += $length;
                return $length;
            }
        );
        $stream->method('read')->willReturnCallback(
            static function (int $length) use (&$bytes, &$position): string {
                $part = substr($bytes, $position, min($length, 4093));
                $position += strlen($part);
                return $part;
            }
        );
        $stream->method('eof')->willReturnCallback(static function () use (&$bytes, &$position): bool {
            return $position >= strlen($bytes);
        });
        return $stream;
    }

    private function record(int $entityId, int $linkId, array $values = []): array
    {
        return ['row' => ['product_id' => $entityId, 'product_link_id' => $linkId] + $values];
    }
}
