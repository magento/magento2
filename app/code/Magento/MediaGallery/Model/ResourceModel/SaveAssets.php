<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Psr\Log\LoggerInterface;

/**
 * Save media asset to the database
 */
class SaveAssets implements SaveAssetsInterface
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DataObjectProcessor
     */
    private $objectProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param DataObjectProcessor $objectProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DataObjectProcessor $objectProcessor,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->objectProcessor = $objectProcessor;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $assets): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);

        $failedAssets = [];
        foreach ($assets as $asset) {
            try {
                $connection->insertOnDuplicate(
                    $tableName,
                    array_filter($this->objectProcessor->buildOutputDataArray($asset, AssetInterface::class))
                );
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $failedAssets[] = $asset;
            }
        }

        if (!empty($failedAssets)) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the media assets: %assets',
                    [
                        'assets' => implode(' ,', $failedAssets)
                    ]
                )
            );
        }
    }
}
