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
     * Collect paths with no remaining gallery references.
     *
     * @param array $pathsByValueId
     * @return string[]
     */
    private function collectUnusedFilePaths(array $pathsByValueId): array
    {
        $candidatePaths = [];
        foreach ($pathsByValueId as $path) {
            $path = (string)$path;
            if ($path === '') {
                continue;
            }
            $normalized = '/' . ltrim($path, '/\\');
            $candidatePaths[$normalized] = true;
        }

        $filesToDelete = [];
        foreach (array_keys($candidatePaths) as $path) {
            if ($this->countImageUses($path) < 1) {
                $filesToDelete[] = ltrim($path, '/');
            }
        }

        return $filesToDelete;
    }

    /**
     * Delete media files and resized cache.
     *
     * @param string[] $filesToDelete
     * @return void
     */
    private function deletePhysicalFilesAndCache(array $filesToDelete): void
    {
        try {
            $catalogPath = $this->mediaConfig->getBaseMediaPath();
            foreach ($filesToDelete as $filePath) {
                $relativePath = $catalogPath . '/' . $filePath;
                if ($this->mediaDirectory->isFile($relativePath)) {
                    $this->mediaDirectory->delete($relativePath);
                }
            }
            $this->removeDeletedImagesFromCache->removeDeletedImagesFromCache($filesToDelete);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Count gallery rows for the image path.
     *
     * @param string $image
     * @return int
     */
    private function countImageUses(string $image): int
    {
        $variants = array_values(array_unique(array_filter([
            $image,
            '/' . ltrim($image, '/\\'),
            ltrim($image, '/\\'),
        ])));
        if ($variants === []) {
            return 0;
        }

        $select = $this->connection->select()
            ->from($this->mediaGalleryTableName, ['cnt' => new \Zend_Db_Expr('COUNT(value_id)')])
            ->where('value IN (?)', $variants);

        return (int)$this->connection->fetchOne($select);
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
