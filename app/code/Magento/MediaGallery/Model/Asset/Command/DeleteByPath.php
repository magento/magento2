<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Asset\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Delete media asset by path
 *
 * @deprecated 100.4.0 use \Magento\MediaGalleryApi\Api\DeleteAssetsByPathInterface instead
 * @see \Magento\MediaGalleryApi\Api\DeleteAssetsByPathInterface
 */
class DeleteByPath implements DeleteByPathInterface
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    private const MEDIA_GALLERY_ASSET_PATH = 'path';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DeleteById constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Delete media asset by path
     *
     * @param string $mediaAssetPath
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(string $mediaAssetPath): void
    {
        try {
            /** @var AdapterInterface $connection */
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);
            $connection->delete($tableName, [self::MEDIA_GALLERY_ASSET_PATH . ' = ?' => $mediaAssetPath]);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __(
                'Could not delete media asset with path %path: %error',
                ['path' => $mediaAssetPath, 'error' => $exception->getMessage()]
            );
            throw new CouldNotDeleteException($message, $exception);
        }
    }
}
