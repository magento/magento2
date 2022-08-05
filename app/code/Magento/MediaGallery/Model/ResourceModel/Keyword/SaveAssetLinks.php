<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel\Keyword;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Psr\Log\LoggerInterface;

/**
 * Save links between asset and keyword to media_gallery_asset_keyword table
 */
class SaveAssetLinks
{
    private const TABLE_ASSET_KEYWORD = 'media_gallery_asset_keyword';
    private const FIELD_ASSET_ID = 'asset_id';
    private const FIELD_KEYWORD_ID = 'keyword_id';
    private const TABLE_MEDIA_ASSET = 'media_gallery_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetsKeywords;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GetAssetsKeywordsInterface $getAssetsKeywords
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetAssetsKeywordsInterface $getAssetsKeywords,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->getAssetsKeywords = $getAssetsKeywords;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Process insert and deletion of asset keywords links
     *
     * @param int $assetId
     * @param KeywordInterface[] $keywordIds
     *
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     */
    public function execute(int $assetId, array $keywordIds): void
    {
        $currentKeywordIds = $this->getCurrentKeywordIds($assetId);

        $obsoleteKeywordIds = array_diff($currentKeywordIds, $keywordIds);
        $newKeywordIds = array_diff($keywordIds, $currentKeywordIds);

        $this->deleteAssetKeywords($assetId, $obsoleteKeywordIds);
        $this->insertAssetKeywords($assetId, $newKeywordIds);

        if ($obsoleteKeywordIds || $newKeywordIds) {
            $this->setAssetUpdatedAt($assetId);
        }
    }

    /**
     * Save new asset keyword links
     *
     * @param int $assetId
     * @param int[] $keywordIds
     *
     * @throws CouldNotSaveException
     */
    private function insertAssetKeywords(int $assetId, array $keywordIds): void
    {
        if (empty($keywordIds)) {
            return;
        }
        try {
            $values = [];

            foreach ($keywordIds as $keywordId) {
                $values[] = [$assetId, $keywordId];
            }

            /** @var Mysql $connection */
            $connection = $this->resourceConnection->getConnection();
            $connection->insertArray(
                $this->resourceConnection->getTableName(self::TABLE_ASSET_KEYWORD),
                [self::FIELD_ASSET_ID, self::FIELD_KEYWORD_ID],
                $values,
                AdapterInterface::INSERT_IGNORE
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(
                __('Could not save asset keyword links'),
                $exception
            );
        }
    }

    /**
     * Delete obsolete asset keyword links
     *
     * @param int $assetId
     * @param int[] $obsoleteKeywordIds
     * @throws CouldNotDeleteException
     */
    private function deleteAssetKeywords(int $assetId, array $obsoleteKeywordIds): void
    {
        if (empty($obsoleteKeywordIds)) {
            return;
        }
        try {
            /** @var Mysql $connection */
            $connection = $this->resourceConnection->getConnection();
            $connection->delete(
                $this->resourceConnection->getTableName(
                    self::TABLE_ASSET_KEYWORD
                ),
                [
                    self::FIELD_KEYWORD_ID . ' in (?)' => $obsoleteKeywordIds,
                    self::FIELD_ASSET_ID . ' = ?' => $assetId
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotDeleteException(
                __('Could not delete obsolete asset keyword links'),
                $exception
            );
        }
    }

    /**
     * Get current keyword ids of an asset
     *
     * @param int $assetId
     * @return int[]
     */
    private function getCurrentKeywordIds(int $assetId): array
    {
        $currentKeywordsData = $this->getAssetsKeywords->execute([$assetId]);

        if (empty($currentKeywordsData)) {
            return [];
        }

        return $this->getKeywordIdsFromKeywordData(
            $currentKeywordsData[$assetId]->getKeywords()
        );
    }

    /**
     * Get keyword ids from keyword data
     *
     * @param KeywordInterface[] $keywordsData
     * @return int[]
     */
    private function getKeywordIdsFromKeywordData(array $keywordsData): array
    {
        return array_map(
            function (KeywordInterface $keyword): int {
                return $keyword->getId();
            },
            $keywordsData
        );
    }

    /**
     * Updates modified date of media asset
     *
     * @param int $assetId
     * @throws CouldNotSaveException
     */
    private function setAssetUpdatedAt(int $assetId): void
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $connection->update(
                $this->resourceConnection->getTableName(self::TABLE_MEDIA_ASSET),
                ['updated_at' => null],
                ['id =?' => $assetId]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(
                __('Could not update assets modified date'),
                $exception
            );
        }
    }
}
