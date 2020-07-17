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
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsKeywordsInterface;
use Psr\Log\LoggerInterface;

/**
 * Save keywords of media assets
 */
class SaveAssetsKeywords implements SaveAssetsKeywordsInterface
{
    private const TABLE_KEYWORD = 'media_gallery_keyword';
    private const TABLE_ASSET_KEYWORD = 'media_gallery_asset_keyword';
    private const ID = 'id';
    private const KEYWORD = 'keyword';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SaveAssetLinks
     */
    private $saveAssetLinks;

    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetsKeywordsInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SaveAssetKeywords constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SaveAssetLinks $saveAssetLinks
     * @param GetAssetsKeywordsInterface $getAssetsKeywordsInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SaveAssetLinks $saveAssetLinks,
        GetAssetsKeywordsInterface $getAssetsKeywordsInterface,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->saveAssetLinks = $saveAssetLinks;
        $this->getAssetsKeywordsInterface = $getAssetsKeywordsInterface;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $assetKeywords): void
    {
        $failedAssetIds = [];
        foreach ($assetKeywords as $assetKeyword) {
            try {
                $this->saveAssetKeywords($assetKeyword->getKeywords(), $assetKeyword->getAssetId());
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $failedAssetIds[] = $assetKeyword->getAssetId();
            }
        }

        if (!empty($failedAssetIds)) {
            throw new CouldNotSaveException(
                __('Could not save keywords for asset ids: %ids', ['ids' => implode(' ,', $failedAssetIds)])
            );
        }
    }

    /**
     * Save asset keywords.
     *
     * @param KeywordInterface[] $keywords
     * @param int $assetId
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws \Zend_Db_Exception
     */
    private function saveAssetKeywords(array $keywords, int $assetId): void
    {
        $data = [];
        foreach ($keywords as $keyword) {
            $data[] = $keyword->getKeyword();
        }

        if (empty($data)) {
            return;
        }

        $this->deleteObsoleteAssetKeywords($data, $assetId);

        /** @var Mysql $connection */
        $connection = $this->resourceConnection->getConnection();
        $connection->insertArray(
            $this->resourceConnection->getTableName(self::TABLE_KEYWORD),
            [self::KEYWORD],
            $data,
            AdapterInterface::INSERT_IGNORE
        );

        $this->saveAssetLinks->execute($assetId, $this->getKeywordIds($data));
    }

    /**
     * Select keywords by names
     *
     * @param string[] $keywords
     * @return int[]
     */
    private function getKeywordIds(array $keywords): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['k' => $this->resourceConnection->getTableName(self::TABLE_KEYWORD)])
            ->columns(self::ID)
            ->where('k.' . self::KEYWORD . ' in (?)', $keywords);

        return $connection->fetchCol($select);
    }

    /**
     * Deletes obsolete asset keywords links
     *
     * @param array $newKeywords
     * @param int $assetId
     * @throws CouldNotDeleteException
     */
    private function deleteObsoleteAssetKeywords(array $newKeywords, int $assetId): void
    {
        $oldKeywordData = $this->getAssetsKeywordsInterface->execute([$assetId]);

        if (empty($newKeywords) || empty($oldKeywordData)) {
            return;
        }

        $oldKeywordData = $this->getAssetsKeywordsInterface->execute([$assetId]);
        $oldKeywords = $oldKeywordData[$assetId]->getKeywords();

        foreach ($oldKeywords as $oldKeyword) {
            if (!in_array($oldKeyword->getKeyword(), $newKeywords)) {
                $obsoleteKeywords[] = $oldKeyword->getKeyword();
            }
        }

        if (empty($obsoleteKeywords)) {
            return;
        }

        $obsoleteKeywordIds = $this->getKeywordIds($obsoleteKeywords);

        try {
            /** @var Mysql $connection */
            $connection  = $this->resourceConnection->getConnection();
            $connection->delete(
                $connection->getTableName(
                    self::TABLE_ASSET_KEYWORD
                ),
                [
                    'keyword_id in (?)' => $obsoleteKeywordIds,
                    'asset_id = ?' => $assetId
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $failedAssetId = $assetId;
        }

        if (!empty($failedAssetId)) {
            throw new CouldNotDeleteException(
                __('Could not delete obsolete keyword relation for asset id: %id', ['id' => $assetId])
            );
        }
    }
}
