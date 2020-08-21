<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;

class SynchronizeIdentities implements SynchronizeIdentitiesInterface
{
    private const ENTITY_TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';

    private const MEDIA_CONTENT_TYPE = 'entity_type';
    private const MEDIA_CONTENT_ENTITY_ID = 'entity_id';
    private const MEDIA_CONTENT_FIELD = 'field';

    private const FIELD_CMS_PAGE = 'cms_page';
    private const FIELD_CMS_BLOCK = 'cms_block';

    private const ID_CMS_PAGE = 'page_id';
    private const ID_CMS_BLOCK = 'block_id';

    private const COLUMN_CMS_CONTENT = 'content';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @var UpdateContentAssetLinksInterface
     */
    private $updateContentAssetLinks;

    /**
     * @var GetEntityContentsInterface
     */
    private $getEntityContents;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param GetEntityContentsInterface $getEntityContents
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        GetEntityContentsInterface $getEntityContents
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->getEntityContents = $getEntityContents;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $mediaContentIdentities): void
    {
        foreach ($mediaContentIdentities as $identity) {
            $contentIdentity = $this->contentIdentityFactory->create(
                [
                    self::ENTITY_TYPE => $identity[self::MEDIA_CONTENT_TYPE],
                    self::ENTITY_ID => $identity[self::MEDIA_CONTENT_ENTITY_ID],
                    self::FIELD => $identity[self::MEDIA_CONTENT_FIELD]
                ]
            );

            if ($identity[self::MEDIA_CONTENT_TYPE] === self::FIELD_CMS_PAGE
                || $identity[self::MEDIA_CONTENT_TYPE] === self::FIELD_CMS_BLOCK
            ) {
                $content = $this->getCmsMediaContent(
                    $identity[self::MEDIA_CONTENT_TYPE],
                    $identity[self::MEDIA_CONTENT_ENTITY_ID]
                );
            } else {
                $content = implode(PHP_EOL, $this->getEntityContents->execute($contentIdentity));
            }

            $this->updateContentAssetLinks->execute(
                $contentIdentity,
                $content
            );
        }
    }

    /**
     * Get cms media content from database
     *
     * @param string $tableName
     * @param string $cmsId
     * @return string
     */
    private function getCmsMediaContent(string $tableName, string $cmsId): string
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName($tableName);
        $idField = $tableName == self::FIELD_CMS_BLOCK ? $idField = self::ID_CMS_BLOCK : self::ID_CMS_PAGE;

        $select = $connection->select()
            ->from($tableName, self::COLUMN_CMS_CONTENT)
            ->where($idField . '= ?', $cmsId);
        $data = $connection->fetchOne($select);

        return (string)$data;
    }
}
