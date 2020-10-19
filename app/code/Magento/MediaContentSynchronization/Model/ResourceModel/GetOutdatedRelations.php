<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterface;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterfaceFactory;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * Returns asset links which entities has been deleted.
 */
class GetOutdatedRelations
{
    private const MEDIA_CONTENT_ASSET_TABLE = 'media_content_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContentAssetLinkInterfaceFactory
     */
    private $contentAssetLinkFactory;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param ContentAssetLinkInterfaceFactory $contentAssetLinkFactory
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        ContentAssetLinkInterfaceFactory $contentAssetLinkFactory,
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->contentAssetLinkFactory = $contentAssetLinkFactory;
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Returns content asset links wichs entity_id not exist anymore.
     *
     * @param string $entityType
     * @throws CouldNotDeleteException
     * @return ContentAssetLinkInterface[]
     */
    public function execute(string $entityType): array
    {
        $contentAssetLinks= [];
        try {
            $entityData = $this->metadataPool->getMetadata($entityType);
            $connection = $this->resourceConnection->getConnection();
            $mediaContentTable = $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE);
            $select = $connection->select();

            $select->from(['mca' => $mediaContentTable], ['asset_id', 'entity_id',  'entity_type', 'field']);
            $select->joinLeft(
                ['et' => $entityData->getEntityTable()],
                'et.' . $entityData->getIdentifierField() . ' =  mca.entity_id ',
                [$entityData->getIdentifierField() . ' AS entity_identifier']
            );
            $select->where('et.' . $entityData->getIdentifierField() . ' IS NULL');
            $select->where('mca.entity_type = ?', $entityData->getEavEntityType() ?? $entityData->getEntityTable());
            $assets = $connection->fetchAll($select);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(__('Could not fetch media content links data'), $exception);
        }

        foreach ($assets as $asset) {
            $contentIdentity = $this->contentIdentityFactory->create(
                [
                    'entityType' => $asset['entity_type'],
                    'entityId' => $asset['entity_id'],
                    'field' => $asset['field']
                ]
            );
            $contentAssetLinks[] = $this->contentAssetLinkFactory->create(
                [
                    'assetId' => $asset['asset_id'],
                    'contentIdentity' => $contentIdentity
                ]
            );
        }

        return $contentAssetLinks;
    }
}
