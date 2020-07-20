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

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetsKeywordsInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GetAssetsKeywordsInterface $getAssetsKeywordsInterface
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetAssetsKeywordsInterface $getAssetsKeywordsInterface,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->getAssetsKeywordsInterface = $getAssetsKeywordsInterface;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Process insert and deletion of asset keywords links
     *
     * @param int $assetId
     * @param KeywordInterface[] $keywordIds
     *
     * @throws CouldNotSaveException
     */
    public function execute(int $assetId, array $keywordIds): void
    {
        try {
            $this->deleteAssetKeywords($assetId, $keywordIds);
            $this->insertAssetKeywords($assetId, $keywordIds);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(
                __('Could not process asset keyword links'),
                $exception
            );
        }
    }

    /**
     * Save new asset keyword links
     *
     * @param int $assetId
     * @param array $keywordIds
     * @throws CouldNotSaveException
     */
    private function insertAssetKeywords(int $assetId, array $keywordIds): void
    {
        try {
            if (!empty($keywordIds)) {
                $values = [];
                $keywordsToInsert = array_diff($keywordIds, $this->getCurrentKeywords($assetId));

                foreach ($keywordsToInsert as $keywordId) {
                    $values[] = [$assetId, $keywordId];
                }

                if (!empty($values)) {
                    /** @var Mysql $connection */
                    $connection = $this->resourceConnection->getConnection();
                    $connection->insertArray(
                        $this->resourceConnection->getTableName(self::TABLE_ASSET_KEYWORD),
                        [self::FIELD_ASSET_ID, self::FIELD_KEYWORD_ID],
                        $values,
                        AdapterInterface::INSERT_IGNORE
                    );
                }
            }
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
     * @param array $keywords
     * @throws CouldNotDeleteException
     */
    private function deleteAssetKeywords(int $assetId, array $keywords): void
    {
        try {
            $obsoleteKeywordIds = array_diff($this->getCurrentKeywords($assetId), $keywords);

            if (!empty($obsoleteKeywordIds)) {
                /** @var Mysql $connection */
                $connection = $this->resourceConnection->getConnection();
                $connection->delete(
                    $connection->getTableName(
                        self::TABLE_ASSET_KEYWORD
                    ),
                    [
                        self::FIELD_KEYWORD_ID . ' in (?)' => $obsoleteKeywordIds,
                        self::FIELD_ASSET_ID . ' = ?' => $assetId
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotDeleteException(
                __('Could not delete obsolete asset keyword links'),
                $exception
            );
        }
    }

    /**
     * Get current keyword data of an asset
     *
     * @param int $assetId
     * @return array
     */
    private function getCurrentKeywords(int $assetId): array
    {
        $currentKeywordsData = $this->getAssetsKeywordsInterface->execute([$assetId]);

        if (!empty($currentKeywordsData)) {
            $currentKeywords = $this->getKeywordIdsFromKeywordData(
                $currentKeywordsData[$assetId]->getKeywords()
            );

            return $currentKeywords;
        }

        return [];
    }

    /**
     * Get keyword ids from keyword data
     *
     * @param array $keywordsData
     * @return array
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
}
