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
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SaveAssetKeywords constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SaveAssetLinks $saveAssetLinks
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SaveAssetLinks $saveAssetLinks,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->saveAssetLinks = $saveAssetLinks;
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

        $this->deleteObsoleteKeywords();

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
     * Delete keywords which has
     * no relation to any asset
     *
     * @return void
     */
    private function deleteObsoleteKeywords(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                ['k' => self::TABLE_KEYWORD],
                ['k.id']
            )
            ->joinLeft(
                ['ak' => self::TABLE_ASSET_KEYWORD],
                'k.id = ak.keyword_id'
            )
            ->where('ak.asset_id IS NULL');

        $obsoleteKeywords = $connection->fetchCol($select);

        if (!empty($obsoleteKeywords)) {
            try {
                $this->deleteKeywordsByIds($obsoleteKeywords);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }
    }

    /**
     * Delete keywords by ids
     *
     * @param array $keywordIds
     * @return  void
     */
    private function deleteKeywordsByIds(array $keywordIds): void
    {
        $connection  = $this->resourceConnection->getConnection();

        $whereConditions = [
            $connection->prepareSqlCondition(
                self::ID,
                ['in' => [$keywordIds]]
            ),
        ];

        $connection->delete(
            $connection->getTableName(
                self::TABLE_KEYWORD
            ),
            $whereConditions
        );
    }
}
