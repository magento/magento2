<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Asset\Command;

use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByIdInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class GetById
 */
class GetById implements GetByIdInterface
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AssetInterface
     */
    private $assetFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetById constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param AssetInterfaceFactory $assetFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AssetInterfaceFactory $assetFactory,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->assetFactory = $assetFactory;
        $this->logger = $logger;
    }

    /**
     * Get media asset.
     *
     * @param int $mediaAssetId
     *
     * @return AssetInterface
     * @throws NoSuchEntityException
     * @throws IntegrationException
     */
    public function execute(int $mediaAssetId): AssetInterface
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from(['amg' => $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET)])
                ->where('amg.id = ?', $mediaAssetId);
            $data = $connection->query($select)->fetch();

            if (empty($data)) {
                $message = __('There is no such media asset with id "%1"', $mediaAssetId);
                throw new NoSuchEntityException($message);
            }

            return $this->assetFactory->create(['data' => $data]);
        } catch (\Exception $exception) {
            $message = __(
                'En error occurred during get media asset with id %id by id: %error',
                ['id' => $mediaAssetId, 'error' => $exception->getMessage()]
            );
            $this->logger->critical($message);
            throw new IntegrationException($message, $exception);
        }
    }
}
