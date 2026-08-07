<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Image\RemoveDeletedImagesFromCache;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LoggerInterface;

/**
 * Removes product media gallery images during import replace.
 *
 * @internal
 */
class MediaGalleryCleanup
{
    /**
     * @var AdapterInterface
     */
    private AdapterInterface $connection;

    /**
     * @var WriteInterface
     */
    private WriteInterface $mediaDirectory;

    /**
     * @var string
     */
    private string $mediaGalleryTableName;

    /**
     * @var string
     */
    private string $mediaGalleryValueTableName;

    /**
     * @var string
     */
    private string $mediaGalleryEntityToValueTableName;

    /**
     * @var string|null
     */
    private ?string $productEntityLinkField = null;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Filesystem $filesystem
     * @param MediaConfig $mediaConfig
     * @param RemoveDeletedImagesFromCache $removeDeletedImagesFromCache
     * @param LoggerInterface $logger
     * @param MetadataPool $metadataPool
     * @throws FileSystemException
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Filesystem $filesystem,
        private readonly MediaConfig $mediaConfig,
        private readonly RemoveDeletedImagesFromCache $removeDeletedImagesFromCache,
        private readonly LoggerInterface $logger,
        private readonly MetadataPool $metadataPool
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaGalleryTableName = $resourceConnection->getTableName(
            'catalog_product_entity_media_gallery'
        );
        $this->mediaGalleryValueTableName = $resourceConnection->getTableName(
            'catalog_product_entity_media_gallery_value'
        );
        $this->mediaGalleryEntityToValueTableName = $resourceConnection->getTableName(
            'catalog_product_entity_media_gallery_value_to_entity'
        );
    }

    /**
     * Unlink gallery images, drop orphan main rows, optionally delete unused files.
     *
     * @param array $removals
     * @param bool $deleteUnusedFiles
     * @return void
     */
    public function removeProductImages(array $removals, bool $deleteUnusedFiles = false): void
    {
        if ($removals === []) {
            return;
        }

        $linkField = $this->getProductEntityLinkField();
        $pairs = [];
        $valueIdToFile = [];
        foreach ($removals as $removal) {
            if (!isset($removal['value_id'], $removal[$linkField])) {
                continue;
            }
            $valueId = (int)$removal['value_id'];
            $productId = (int)$removal[$linkField];
            $key = $valueId . ':' . $productId;
            if (isset($pairs[$key])) {
                continue;
            }
            $pairs[$key] = [$valueId, $productId];
            if (isset($removal['value']) && $removal['value'] !== '') {
                $valueIdToFile[$valueId] = (string)$removal['value'];
            }
        }
        if ($pairs === []) {
            return;
        }

        $conditions = [];
        foreach ($pairs as [$valueId, $productId]) {
            $conditions[] = sprintf(
                '(%s AND %s)',
                $this->connection->quoteInto('value_id = ?', $valueId),
                $this->connection->quoteInto($linkField . ' = ?', $productId)
            );
        }
        $where = implode(' OR ', $conditions);
        $this->connection->delete($this->mediaGalleryEntityToValueTableName, $where);
        $this->connection->delete($this->mediaGalleryValueTableName, $where);

        foreach ($pairs as [$valueId]) {
            if (!isset($valueIdToFile[$valueId])) {
                $valueIdToFile[$valueId] = '';
            }
        }
        $this->cleanupOrphansAndFiles($valueIdToFile, $deleteUnusedFiles);
    }

    /**
     * Remove orphan main gallery rows; optionally delete unused media files.
     *
     * @param array $valueIdToFile value_id => path
     * @param bool $deleteUnusedFiles
     * @return void
     */
    private function cleanupOrphansAndFiles(array $valueIdToFile, bool $deleteUnusedFiles): void
    {
        $valueIds = array_map('intval', array_keys($valueIdToFile));
        $linkedValueIds = $this->connection->fetchCol(
            $this->connection->select()
                ->from($this->mediaGalleryEntityToValueTableName, ['value_id'])
                ->distinct(true)
                ->where('value_id IN (?)', $valueIds)
        );
        $linkedValueIds = array_map('intval', $linkedValueIds);
        $orphanValueIds = array_values(array_diff($valueIds, $linkedValueIds));
        if ($orphanValueIds === []) {
            return;
        }

        $pathsByValueId = $this->resolvePathsForValueIds($orphanValueIds, $valueIdToFile);

        $this->connection->delete(
            $this->mediaGalleryTableName,
            $this->connection->quoteInto('value_id IN (?)', $orphanValueIds)
        );

        if (!$deleteUnusedFiles) {
            return;
        }

        $filesToDelete = $this->collectUnusedFilePaths($pathsByValueId);
        if ($filesToDelete === []) {
            return;
        }

        $this->deletePhysicalFilesAndCache($filesToDelete);
    }

    /**
     * Resolve paths for orphan value_ids.
     *
     * @param int[] $orphanValueIds
     * @param array $valueIdToFile
     * @return array
     */
    private function resolvePathsForValueIds(array $orphanValueIds, array $valueIdToFile): array
    {
        $pathsByValueId = [];
        foreach ($orphanValueIds as $valueId) {
            if (!empty($valueIdToFile[$valueId])) {
                $pathsByValueId[$valueId] = (string)$valueIdToFile[$valueId];
            }
        }

        $missingIds = array_values(array_diff($orphanValueIds, array_keys($pathsByValueId)));
        if ($missingIds === []) {
            return $pathsByValueId;
        }

        $rows = $this->connection->fetchPairs(
            $this->connection->select()
                ->from($this->mediaGalleryTableName, ['value_id', 'value'])
                ->where('value_id IN (?)', $missingIds)
        ) ?: [];
        foreach ($rows as $valueId => $path) {
            $pathsByValueId[(int)$valueId] = (string)$path;
        }

        return $pathsByValueId;
    }

    /**
     * Collect relative paths with no remaining gallery references.
     *
     * @param array $pathsByValueId
     * @return string[] Relative paths without leading slash
     */
    private function collectUnusedFilePaths(array $pathsByValueId): array
    {
        $candidates = [];
        foreach ($pathsByValueId as $path) {
            $relative = $this->normalizeGalleryRelativePath((string)$path);
            if ($relative === null) {
                continue;
            }
            $candidates[$relative] = true;
        }
        if ($candidates === []) {
            return [];
        }

        $usageByRelative = $this->countImageUsesByRelativePath(array_keys($candidates));
        $filesToDelete = [];
        foreach (array_keys($candidates) as $relative) {
            if (($usageByRelative[$relative] ?? 0) < 1) {
                $filesToDelete[] = $relative;
            }
        }

        return $filesToDelete;
    }

    /**
     * Batch-count remaining gallery rows per relative path.
     *
     * @param string[] $relativePaths Paths without leading slash
     * @return array<string, int> relative path => remaining uses
     */
    private function countImageUsesByRelativePath(array $relativePaths): array
    {
        $usageByRelative = array_fill_keys($relativePaths, 0);
        $variants = [];
        $variantToRelative = [];
        foreach ($relativePaths as $relative) {
            foreach (['/' . $relative, $relative] as $variant) {
                $variants[] = $variant;
                $variantToRelative[$variant] = $relative;
            }
        }
        $variants = array_values(array_unique($variants));
        if ($variants === []) {
            return $usageByRelative;
        }

        $rows = $this->connection->fetchPairs(
            $this->connection->select()
                ->from(
                    $this->mediaGalleryTableName,
                    ['value', 'cnt' => new \Zend_Db_Expr('COUNT(value_id)')]
                )
                ->where('value IN (?)', $variants)
                ->group('value')
        ) ?: [];

        foreach ($rows as $value => $count) {
            $value = (string)$value;
            $relative = $variantToRelative[$value]
                ?? $this->normalizeGalleryRelativePath($value);
            if ($relative === null || !array_key_exists($relative, $usageByRelative)) {
                continue;
            }
            $usageByRelative[$relative] += (int)$count;
        }

        return $usageByRelative;
    }

    /**
     * Delete media files and resized cache under the catalog media path only.
     *
     * @param string[] $filesToDelete Relative paths without leading slash
     * @return void
     */
    private function deletePhysicalFilesAndCache(array $filesToDelete): void
    {
        $catalogPath = rtrim(str_replace('\\', '/', $this->mediaConfig->getBaseMediaPath()), '/');
        $safeFiles = [];
        try {
            foreach ($filesToDelete as $filePath) {
                $relative = $this->normalizeGalleryRelativePath((string)$filePath);
                if ($relative === null) {
                    continue;
                }
                $relativePath = $catalogPath . '/' . $relative;
                if (!$this->isPathInsideBase($catalogPath, $relativePath)) {
                    continue;
                }
                if ($this->mediaDirectory->isFile($relativePath)) {
                    $this->mediaDirectory->delete($relativePath);
                }
                $safeFiles[] = $relative;
            }
            if ($safeFiles !== []) {
                $this->removeDeletedImagesFromCache->removeDeletedImagesFromCache($safeFiles);
            }
        } catch (FileSystemException $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Normalize gallery file path to a safe relative path under media (no traversal).
     *
     * @param string $path
     * @return string|null Relative path without leading slash, or null if unsafe/empty
     */
    private function normalizeGalleryRelativePath(string $path): ?string
    {
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, "\0")) {
            return null;
        }
        if (preg_match('#^[a-zA-Z]:#', $path) === 1) {
            return null;
        }
        foreach (explode('/', $path) as $segment) {
            if ($segment === '..') {
                return null;
            }
        }

        return $path;
    }

    /**
     * Whether $path stays under $base (both media-relative, forward slashes).
     *
     * @param string $base
     * @param string $path
     * @return bool
     */
    private function isPathInsideBase(string $base, string $path): bool
    {
        $base = rtrim(str_replace('\\', '/', $base), '/') . '/';
        $path = str_replace('\\', '/', $path);

        return str_starts_with($path, $base);
    }

    /**
     * Get product entity link field.
     *
     * @return string
     */
    private function getProductEntityLinkField(): string
    {
        if ($this->productEntityLinkField === null) {
            $this->productEntityLinkField = $this->metadataPool
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }
        return $this->productEntityLinkField;
    }
}
