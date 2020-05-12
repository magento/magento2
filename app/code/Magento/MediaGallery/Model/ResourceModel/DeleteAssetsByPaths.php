<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Psr\Log\LoggerInterface;

/**
 * Remove asset(s) that correspond the provided path
 */
class DeleteAssetsByPaths implements DeleteAssetsByPathsInterface
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
     * DeleteAssetsByPaths constructor.
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
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $failedPaths = [];

        foreach ($paths as $path) {
            try {
                $this->validateDirectoryPath($path);
                $this->deleteAssetsByDirectoryPath($path);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $failedPaths[] = $path;
            }
        }

        if (!empty($failedPaths)) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete media assets by paths: %paths',
                    [
                        'paths' => implode(' ,', $failedPaths)
                    ]
                )
            );
        }
    }

    /**
     * Delete assets from database based on the first part (beginning) of the path
     *
     * @param string $path
     */
    private function deleteAssetsByDirectoryPath(string $path): void
    {
        /** @var AdapterInterface $connection */
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);
        $connection->delete($tableName, [self::MEDIA_GALLERY_ASSET_PATH . ' LIKE ?' => $path . '%']);
    }

    /**
     * Validate the directory path
     *
     * @param string $path
     * @throws CouldNotDeleteException
     */
    private function validateDirectoryPath(string $path): void
    {
        if (!$path || trim($path) === '') {
            throw new CouldNotDeleteException(__('Cannot remove assets, the directory path does not exist'));
        }
    }
}
