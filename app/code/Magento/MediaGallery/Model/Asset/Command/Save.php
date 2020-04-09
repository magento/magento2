<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Asset\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\SaveInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 * @deprecated use \Magento\MediaGalleryApi\Api\SaveAssetsInterface instead
 * @see \Magento\MediaGalleryApi\Api\SaveAssetsInterface
 */
class Save implements SaveInterface
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
     * Save media assets
     *
     * @param AssetInterface $mediaAsset
     *
     * @return int
     * @throws CouldNotSaveException
     */
    public function execute(AssetInterface $mediaAsset): int
    {
        try {
            /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $connection */
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);

            $connection->insertOnDuplicate(
                $tableName,
                $this->filterData($this->objectProcessor->buildOutputDataArray($mediaAsset, AssetInterface::class))
            );
            return (int) $connection->lastInsertId($tableName);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('An error occurred during media asset save: %1', $exception->getMessage());
            throw new CouldNotSaveException($message, $exception);
        }
    }

    /**
     * Filter data to get flat array without null values
     *
     * @param array $data
     * @return array
     */
    private function filterData(array $data): array
    {
        $filteredData = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            if (is_array($value)) {
                continue;
            }
            if (is_object($value)) {
                continue;
            }
            $filteredData[$key] = $value;
        }
        return $filteredData;
    }
}
