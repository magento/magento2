<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Export\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Store\Model\Store;

class TemporaryRowStorage
{
    private const DIRECTORY = 'catalog-product-export';

    /**
     * @var WriteInterface
     */
    private WriteInterface $directory;

    /**
     * @var Serialize
     */
    private Serialize $serializer;

    /**
     * @var int
     */
    private int $writeBufferSize;

    /**
     * @var FileWriteInterface|null
     */
    private ?FileWriteInterface $stream = null;

    /**
     * @var array<int, array{startOffset: int, endOffset: int, readOffset: int}>
     */
    private array $sections = [];

    /**
     * @var int
     */
    private int $appendOffset = 0;

    /**
     * @var int[]
     */
    private array $defaultOffsets = [];

    /**
     * @var int|null
     */
    private ?int $bufferedStoreId = null;

    /**
     * @var string
     */
    private string $writeBuffer = '';

    /**
     * @param Filesystem $filesystem
     * @param Serialize $serializer
     * @param int $writeBufferSize
     */
    public function __construct(Filesystem $filesystem, Serialize $serializer, int $writeBufferSize)
    {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::TMP);
        $this->serializer = $serializer;
        $this->writeBufferSize = $writeBufferSize;
    }

    /**
     * Index default rows by entity ID while staging each store's scalar records.
     *
     * @param int $storeId
     * @param array $record
     * @return void
     */
    public function append(int $storeId, array $record): void
    {
        array_walk_recursive($record, static function ($value): void {
            if ($value !== null && !is_scalar($value)) {
                throw new \InvalidArgumentException('Temporary export records must contain only scalar values.');
            }
        });
        if ($this->bufferedStoreId !== $storeId) {
            if (isset($this->sections[$storeId])) {
                throw new \LogicException('Temporary export stores must be staged sequentially.');
            }
            $this->flushWriteBuffer();
            $this->bufferedStoreId = $storeId;
            $this->sections[$storeId] = [
                'startOffset' => $this->appendOffset,
                'endOffset' => $this->appendOffset,
                'readOffset' => $this->appendOffset,
            ];
        }
        $this->stream ??= $this->openStream();
        if ($storeId === Store::DEFAULT_STORE_ID) {
            $this->defaultOffsets[$record['row']['product_id']] = $this->appendOffset + strlen($this->writeBuffer);
        }
        $payload = $this->serializer->serialize($record);
        $this->writeBuffer .= pack('J', strlen($payload)) . $payload;
        if (strlen($this->writeBuffer) >= $this->writeBufferSize) {
            $this->flushWriteBuffer();
        }
    }

    /**
     * Complete partial writes before releasing the only pending store buffer.
     *
     * @return void
     */
    private function flushWriteBuffer(): void
    {
        $offset = 0;
        $length = strlen($this->writeBuffer);
        if ($length === 0) {
            return;
        }
        // Default-row lookups and merge reads move the shared file position.
        $this->stream->seek($this->appendOffset);
        while ($offset < $length) {
            $written = $this->stream->write(substr($this->writeBuffer, $offset));
            if ($written < 1) {
                throw new \RuntimeException('Unable to write a temporary export record.');
            }
            $offset += $written;
        }
        $this->appendOffset += $length;
        $this->sections[$this->bufferedStoreId]['endOffset'] = $this->appendOffset;
        $this->writeBuffer = '';
    }

    /**
     * Read a default row by entity ID even when its frame is still buffered.
     *
     * @param int $productId
     * @return array|null
     */
    public function getDefaultRecord(int $productId): ?array
    {
        if (!isset($this->defaultOffsets[$productId])) {
            return null;
        }
        if ($this->bufferedStoreId === Store::DEFAULT_STORE_ID) {
            $this->flushWriteBuffer();
        }
        $this->stream->seek($this->defaultOffsets[$productId]);
        return $this->readRecord($this->stream, $this->sections[Store::DEFAULT_STORE_ID]['endOffset']);
    }

    /**
     * Merge by metadata link ID while retaining only one head record per store.
     *
     * @return \Generator
     */
    public function getProducts(): \Generator
    {
        $this->flushWriteBuffer();
        $heads = [];
        if ($this->stream === null) {
            return;
        }
        $this->stream->flush();
        foreach ($this->sections as $storeId => $section) {
            $this->sections[$storeId]['readOffset'] = $section['startOffset'];
            $record = $this->readStoreRecord($storeId);
            if ($record !== null) {
                $heads[$storeId] = $record;
            }
        }
        while ($heads) {
            $linkId = min(array_map(
                static fn(array $record): int => (int)$record['row']['product_link_id'],
                $heads
            ));
            $product = [];
            foreach ($heads as $storeId => $record) {
                if ((int)$record['row']['product_link_id'] !== $linkId) {
                    continue;
                }
                $product[$storeId] = $record;
                $next = $this->readStoreRecord($storeId);
                if ($next === null) {
                    unset($heads[$storeId]);
                } else {
                    $heads[$storeId] = $next;
                }
            }
            yield $product;
        }
    }

    /**
     * Read only within the selected store's section of the shared stream.
     *
     * @param int $storeId
     * @return array|null
     */
    private function readStoreRecord(int $storeId): ?array
    {
        $section = $this->sections[$storeId];
        $this->stream->seek($section['readOffset']);
        $record = $this->readRecord($this->stream, $section['endOffset']);
        $this->sections[$storeId]['readOffset'] = $this->stream->tell();
        return $record;
    }

    /**
     * Release staging without flushing records that will no longer be consumed.
     *
     * @return void
     */
    public function close(): void
    {
        try {
            $this->stream?->close();
        } finally {
            $this->stream = null;
            $this->sections = [];
            $this->appendOffset = 0;
            $this->bufferedStoreId = null;
            $this->defaultOffsets = [];
            $this->writeBuffer = '';
        }
    }

    /**
     * Unlink before writing so external termination cannot leave product data behind.
     *
     * @return FileWriteInterface
     */
    private function openStream(): FileWriteInterface
    {
        $this->directory->create(self::DIRECTORY, 0770);
        $lockPath = self::DIRECTORY . '/.lock';
        $lock = $this->directory->openFile($lockPath, 'c+');
        try {
            $this->directory->changePermissions($lockPath, 0660);
            $lock->lock();
            $this->removeAbandonedFiles();
            $path = self::DIRECTORY . '/row-' . bin2hex(random_bytes(16));
            $stream = $this->directory->openFile($path, 'x+');
            try {
                $this->directory->changePermissions($path, 0600);
                $this->directory->delete($path);
            } catch (\Exception $exception) {
                $stream->close();
                throw $exception;
            }
            return $stream;
        } finally {
            $lock->close();
        }
    }

    /**
     * Under the creation lock, named empty records cannot belong to an active writer.
     *
     * @return void
     */
    private function removeAbandonedFiles(): void
    {
        foreach ($this->directory->read(self::DIRECTORY) as $path) {
            if (preg_match('#^' . self::DIRECTORY . '/row-[a-f0-9]{32}$#D', $path)
                && $this->directory->isFile($path)
                && $this->directory->stat($path)['size'] === 0
            ) {
                $this->directory->delete($path);
            }
        }
    }

    /**
     * Reject incomplete frames instead of passing partial products to row enrichment.
     *
     * @param ReadInterface $stream
     * @param int $endOffset
     * @return array|null
     * @throws \RuntimeException
     */
    private function readRecord(ReadInterface $stream, int $endOffset): ?array
    {
        $remaining = $endOffset - $stream->tell();
        if ($remaining === 0) {
            return null;
        }
        if ($remaining < 8) {
            throw new \RuntimeException('Incomplete temporary export record header.');
        }
        $header = $stream->read(8);
        if (strlen($header) !== 8) {
            throw new \RuntimeException('Incomplete temporary export record header.');
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged -- Decode the fixed binary frame length.
        $length = unpack('Jlength', $header)['length'];
        if ($length < 1) {
            throw new \RuntimeException('Invalid temporary export record length.');
        }
        if ($length > $endOffset - $stream->tell()) {
            throw new \RuntimeException('Incomplete temporary export record.');
        }
        $payload = '';
        while (strlen($payload) < $length) {
            $part = $stream->read($length - strlen($payload));
            if ($part === '') {
                throw new \RuntimeException('Incomplete temporary export record.');
            }
            $payload .= $part;
        }
        $record = $this->serializer->unserialize($payload);
        if (!is_array($record)) {
            throw new \RuntimeException('Invalid temporary export record.');
        }
        return $record;
    }
}
