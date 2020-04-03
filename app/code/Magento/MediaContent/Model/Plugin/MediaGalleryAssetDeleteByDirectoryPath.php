<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaContent\Model\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByDirectoryPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Remove media content record after media gallery asset removal.
 */
class MediaGalleryAssetDeleteByDirectoryPath
{
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
     * @param DeleteByDirectoryPathInterface $subject
     * @param \Closure $proceed
     * @param string $directoryPath
     * @throws CouldNotDeleteException
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        DeleteByDirectoryPathInterface $subject,
        \Closure $proceed,
        string $directoryPath
    ) : void {
        /** @var AdapterInterface $connection */
        $connection = $this->resourceConnection->getConnection();
        $galleryAssetTableName = $this->resourceConnection->getTableName('media_gallery_asset');
        $mediaContentAssetTableName = $this->resourceConnection->getTableName('media_content_asset');

        $select = $connection->select();
        $select->from($galleryAssetTableName, ['id']);
        $select->where('path LIKE ?', $directoryPath);
        $galleryAssetIds = $connection->fetchCol($select);

        $proceed();

        try {
            $connection->delete(
                $mediaContentAssetTableName,
                ['asset_id IN(?)' => implode(', ', $galleryAssetIds)]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __(
                'Could not delete media content assets for media gallery asset with path %path: %error',
                ['path' => $directoryPath, 'error' => $exception->getMessage()]
            );
            throw new CouldNotDeleteException($message, $exception);
        }
    }
}
