<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Keyword\Command;

use Magento\Framework\Exception\IntegrationException;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterfaceFactory;
use Magento\MediaGalleryApi\Model\Keyword\Command\GetAssetKeywordsInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Retrieve keywords for the media asset
 * @deprecated 100.4.0 use \Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface instead
 */
class GetAssetKeywords implements GetAssetKeywordsInterface
{
    private const TABLE_KEYWORD = 'media_gallery_keyword';
    private const TABLE_ASSET_KEYWORD = 'media_gallery_asset_keyword';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var KeywordInterfaceFactory
     */
    private $assetKeywordFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetAssetKeywords constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param KeywordInterfaceFactory $assetKeywordFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        KeywordInterfaceFactory $assetKeywordFactory,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->assetKeywordFactory = $assetKeywordFactory;
        $this->logger = $logger;
    }

    /**
     * Get asset related keywords.
     *
     * @param int $assetId
     *
     * @return KeywordInterface[]
     * @throws IntegrationException
     */
    public function execute(int $assetId): array
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableAssetKeyword = $this->resourceConnection->getTableName(self::TABLE_ASSET_KEYWORD);

            $select = $connection->select()
                ->from(['k' => $this->resourceConnection->getTableName(self::TABLE_KEYWORD)])
                ->join(['ak' => $tableAssetKeyword], 'k.id = ak.keyword_id')
                ->where('ak.asset_id = ?', $assetId);
            $data = $connection->query($select)->fetchAll();

            $keywords = [];
            foreach ($data as $keywordData) {
                $keywords[] = $this->assetKeywordFactory->create(
                    [
                        'id' => $keywordData['id'],
                        'keyword' => $keywordData['keyword'],
                    ]
                );
            }

            return $keywords;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('An error occurred during get asset keywords: %1', $exception->getMessage());
            throw new IntegrationException($message, $exception);
        }
    }
}
