<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel\Keyword;

use Magento\Framework\Exception\IntegrationException;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\KeywordInterfaceFactory;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Retrieve keywords of the media assets
 */
class GetAssetsKeywords implements GetAssetsKeywordsInterface
{
    private const TABLE_KEYWORD = 'media_gallery_keyword';
    private const TABLE_ASSET_KEYWORD = 'media_gallery_asset_keyword';
    private const FIELD_ASSET_ID = 'asset_id';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var KeywordInterfaceFactory
     */
    private $keywordFactory;

    /**
     * @var AssetKeywordsInterfaceFactory
     */
    private $assetKeywordsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AssetKeywordsInterfaceFactory $assetKeywordsFactory
     * @param ResourceConnection $resourceConnection
     * @param KeywordInterfaceFactory $keywordFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        AssetKeywordsInterfaceFactory $assetKeywordsFactory,
        ResourceConnection $resourceConnection,
        KeywordInterfaceFactory $keywordFactory,
        LoggerInterface $logger
    ) {
        $this->assetKeywordsFactory = $assetKeywordsFactory;
        $this->resourceConnection = $resourceConnection;
        $this->keywordFactory = $keywordFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $assetIds): array
    {
        try {
            return $this->getAssetKeywords($this->getKeywordsData($assetIds));
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new IntegrationException(__('Could not retrieve asset keywords.'), $exception);
        }
    }

    /**
     * Load keywords data
     *
     * @param array $assetIds
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getKeywordsData(array $assetIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['k' => $this->resourceConnection->getTableName(self::TABLE_KEYWORD)])
            ->join(['ak' => $this->resourceConnection->getTableName(self::TABLE_ASSET_KEYWORD)], 'k.id = ak.keyword_id')
            ->where('ak.asset_id IN (?)', $assetIds);
        return $connection->query($select)->fetchAll();
    }

    /**
     * Build AssetKeywords objects array
     *
     * @param array $keywordsData
     * @return AssetKeywordsInterface[]
     */
    private function getAssetKeywords(array $keywordsData): array
    {
        $keywordsByAsset = [];
        foreach ($keywordsData as $keywordData) {
            $keywordsByAsset[$keywordData[self::FIELD_ASSET_ID]][] = $this->keywordFactory->create(
                [
                    'id' => $keywordData['id'],
                    'keyword' => $keywordData['keyword'],
                ]
            );
        }

        $assetKeywords = [];
        foreach ($keywordsByAsset as $assetId => $keywords) {
            $assetKeywords[$assetId] = $this->assetKeywordsFactory->create(
                [
                    'assetId' => $assetId,
                    'keywords' => $keywords
                ]
            );
        }

        return $assetKeywords;
    }
}
