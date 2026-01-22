<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Decorator;

use Magento\Framework\Cache\FrontendInterface;

/**
 * Compression decorator for Symfony cache frontend
 *
 * Compresses cache data before storing to reduce memory usage and network bandwidth.
 * Compatible with legacy Zend cache compression format for seamless migration.
 */
class Compression extends Bare
{
    /**
     * Compression prefix to identify compressed data
     */
    private const COMPRESSION_PREFIX = 'CACHE_COMPRESSION';

    /**
     * @var int
     */
    private int $threshold;

    /**
     * @var string
     */
    private string $compressionLib;

    /**
     * @var int
     */
    private int $compressionLevel;

    /**
     * Constructor
     *
     * @param FrontendInterface $frontend
     * @param int $threshold Minimum data size in bytes to trigger compression (default: 2048)
     * @param string $compressionLib Compression library: gzip, snappy, lzf, lz4, zstd (default: gzip)
     * @param int $compressionLevel Compression level 1-9, higher = better compression but slower (default: 6)
     */
    public function __construct(
        FrontendInterface $frontend,
        int $threshold = 2048,
        string $compressionLib = 'gzip',
        int $compressionLevel = 6
    ) {
        parent::__construct($frontend);
        $this->threshold = max(1, $threshold);
        $this->compressionLib = strtolower($compressionLib);
        $this->compressionLevel = max(1, min(9, $compressionLevel));
    }

    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null): bool
    {
        // Only compress string data that exceeds threshold
        if (is_string($data) && strlen($data) > $this->threshold) {
            $compressed = $this->compressData($data);

            // Only use compressed version if it's actually smaller
            if ($compressed !== false && strlen($compressed) < strlen($data)) {
                $data = self::COMPRESSION_PREFIX . $compressed;
            }
        }

        return parent::save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        $data = parent::load($identifier);

        // Decompress if data has compression prefix
        if (is_string($data) && $this->isCompressed($data)) {
            $decompressed = $this->decompressData($data);
            if ($decompressed !== false) {
                return $decompressed;
            }
        }

        return $data;
    }

    /**
     * Compress data using configured compression library
     *
     * @param string $data
     * @return string|false Compressed data or false on failure
     */
    private function compressData(string $data)
    {
        try {
            return match ($this->compressionLib) {
                'snappy' => $this->compressSnappy($data),
                'lzf' => $this->compressLzf($data),
                'lz4' => $this->compressLz4($data),
                'zstd' => $this->compressZstd($data),
                'gzip', '' => $this->compressGzip($data),
                default => $this->compressGzip($data), // Fallback to gzip
            };
        } catch (\Throwable $e) {
            // Silently fallback to uncompressed on any compression error
            return false;
        }
    }

    /**
     * Decompress data by auto-detecting compression method
     *
     * @param string $data
     * @return string|false Decompressed data or false on failure
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function decompressData(string $data)
    {
        // Remove compression prefix
        $data = substr($data, strlen(self::COMPRESSION_PREFIX));

        try {
            // Auto-detect compression method by trying each in order
            // Try gzip first (most common)
            if (($result = $this->decompressGzip($data)) !== false) {
                return $result;
            }

            // Try other formats
            if (extension_loaded('snappy') && ($result = $this->decompressSnappy($data)) !== false) {
                return $result;
            }

            if (extension_loaded('lzf') && ($result = $this->decompressLzf($data)) !== false) {
                return $result;
            }

            if (extension_loaded('lz4') && ($result = $this->decompressLz4($data)) !== false) {
                return $result;
            }

            if (extension_loaded('zstd') && ($result = $this->decompressZstd($data)) !== false) {
                return $result;
            }

            return false;
        } catch (\Throwable $e) {
            // Return false on any decompression error
            return false;
        }
    }

    /**
     * Check if data is compressed
     *
     * @param string $data
     * @return bool
     */
    private function isCompressed(string $data): bool
    {
        return str_starts_with($data, self::COMPRESSION_PREFIX);
    }

    /**
     * Compress using gzip
     *
     * @param string $data
     * @return string|false
     */
    private function compressGzip(string $data)
    {
        return gzcompress($data, $this->compressionLevel);
    }

    /**
     * Decompress using gzip
     *
     * @param string $data
     * @return string|false
     */
    private function decompressGzip(string $data)
    {
        return @gzuncompress($data);
    }

    /**
     * Compress using Snappy
     *
     * @param string $data
     * @return string|false
     */
    private function compressSnappy(string $data)
    {
        if (!extension_loaded('snappy')) {
            return false;
        }
        return snappy_compress($data);
    }

    /**
     * Decompress using Snappy
     *
     * @param string $data
     * @return string|false
     */
    private function decompressSnappy(string $data)
    {
        if (!extension_loaded('snappy')) {
            return false;
        }
        return @snappy_uncompress($data);
    }

    /**
     * Compress using LZF
     *
     * @param string $data
     * @return string|false
     */
    private function compressLzf(string $data)
    {
        if (!extension_loaded('lzf')) {
            return false;
        }
        return lzf_compress($data);
    }

    /**
     * Decompress using LZF
     *
     * @param string $data
     * @return string|false
     */
    private function decompressLzf(string $data)
    {
        if (!extension_loaded('lzf')) {
            return false;
        }
        return @lzf_decompress($data);
    }

    /**
     * Compress using LZ4
     *
     * @param string $data
     * @return string|false
     */
    private function compressLz4(string $data)
    {
        if (!extension_loaded('lz4')) {
            return false;
        }
        return lz4_compress($data, $this->compressionLevel); // @phpstan-ignore-line
    }

    /**
     * Decompress using LZ4
     *
     * @param string $data
     * @return string|false
     */
    private function decompressLz4(string $data)
    {
        if (!extension_loaded('lz4')) {
            return false;
        }
        return @lz4_uncompress($data); // @phpstan-ignore-line
    }

    /**
     * Compress using Zstd
     *
     * @param string $data
     * @return string|false
     */
    private function compressZstd(string $data)
    {
        if (!extension_loaded('zstd')) {
            return false;
        }
        return zstd_compress($data, $this->compressionLevel);
    }

    /**
     * Decompress using Zstd
     *
     * @param string $data
     * @return string|false
     */
    private function decompressZstd(string $data)
    {
        if (!extension_loaded('zstd')) {
            return false;
        }
        return @zstd_uncompress($data);
    }
}
