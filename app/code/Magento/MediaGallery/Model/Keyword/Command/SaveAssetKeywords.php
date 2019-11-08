<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Keyword\Command;

use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Model\Keyword\Command\SaveAssetKeywordsInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class SaveAssetKeywords
 */
class SaveAssetKeywords implements SaveAssetKeywordsInterface
{
    private const TABLE_KEYWORD = 'media_gallery_keyword';
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
     * SaveAssetKeywords constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param SaveAssetLinks $saveAssetLinks
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SaveAssetLinks $saveAssetLinks
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->saveAssetLinks = $saveAssetLinks;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $keywords, int $assetId): void
    {
        try {
            $data = [];
            /** @var KeywordInterface $keyword */
            foreach ($keywords as $keyword) {
                $data[] = $keyword->getKeyword();
            }

            if (!empty($data)) {
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
        } catch (\Exception $exception) {
            $message = __('An error occurred during save asset keyword: %1', $exception->getMessage());
            throw new CouldNotSaveException($message, $exception);
        }
    }

    /**
     * Select keywords by names
     *
     * @param string[] $keywords
     *
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
}
