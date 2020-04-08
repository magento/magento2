<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Asset\Command;

use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByPathInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class GetListByIds
 */
class GetByPath implements GetByPathInterface
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    private const MEDIA_GALLERY_ASSET_PATH = 'path';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AssetInterface
     */
    private $mediaAssetFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetByPath constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param AssetInterfaceFactory $mediaAssetFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AssetInterfaceFactory $mediaAssetFactory,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->mediaAssetFactory = $mediaAssetFactory;
        $this->logger = $logger;
    }

    /**
     * Return media asset asset list
     *
     * @param string $mediaFilePath
     *
     * @return AssetInterface
     * @throws IntegrationException
     */
    public function execute(string $mediaFilePath): AssetInterface
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET))
                ->where(self::MEDIA_GALLERY_ASSET_PATH . ' = ?', $mediaFilePath);
            $data = $connection->query($select)->fetch();

            if (empty($data)) {
                $message = __('There is no such media asset with path "%1"', $mediaFilePath);
                throw new NoSuchEntityException($message);
            }

            $mediaAssets = $this->mediaAssetFactory->create(['data' => $data]);

            return $mediaAssets;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('An error occurred during get media asset list: %1', $exception->getMessage());
            throw new IntegrationException($message, $exception);
        }
    }
}
