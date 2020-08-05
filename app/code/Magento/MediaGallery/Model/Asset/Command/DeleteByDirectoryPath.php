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
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByDirectoryPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Remove asset(s) that correspond the provided directory path
 * @deprecated 100.4.0 use \Magento\MediaGalleryApi\Api\DeleteAssetsByPathInterface instead
 * @see \Magento\MediaGalleryApi\Api\DeleteAssetsByPathInterfac
 */
class DeleteByDirectoryPath implements DeleteByDirectoryPathInterface
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
     * Delete media asset(s) by path
     *
     * @param string $directoryPath
     *
     * @return void
     *
     * @throws CouldNotDeleteException
     */
    public function execute(string $directoryPath): void
    {
        $this->validateDirectoryPath($directoryPath);
        try {
            // Make sure that the path has a trailing slash
            $directoryPath = rtrim($directoryPath, '/') . '/';

            /** @var AdapterInterface $connection */
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);
            $connection->delete($tableName, [self::MEDIA_GALLERY_ASSET_PATH . ' LIKE ?' => $directoryPath . '%']);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __(
                'Could not delete media assets by path %path: %error',
                ['path' => $directoryPath, 'error' => $exception->getMessage()]
            );
            throw new CouldNotDeleteException($message, $exception);
        }
    }

    /**
     * Validate the directory path
     *
     * @param string $directoryPath
     *
     * @throws CouldNotDeleteException
     */
    private function validateDirectoryPath(string $directoryPath): void
    {
        if (!$directoryPath || trim($directoryPath) === '') {
            throw new CouldNotDeleteException(__('Cannot remove assets, the directory path does not exist'));
        }
    }
}
