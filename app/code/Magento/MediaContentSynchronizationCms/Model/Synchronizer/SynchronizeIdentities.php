<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCms\Model\Synchronizer;

use Magento\Framework\App\ResourceConnection;
use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;

class SynchronizeIdentities implements SynchronizeIdentitiesInterface
{
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
     * @var UpdateContentAssetLinksInterface
     */
    private $updateContentAssetLinks;

    /**
     * @var GetEntityContentsInterface
     */
    private $getEntityContents;

    /**
     * @param ResourceConnection $resourceConnection
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param GetEntityContentsInterface $getEntityContents
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        GetEntityContentsInterface $getEntityContents
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->getEntityContents = $getEntityContents;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $mediaContentIdentities): void
    {
        foreach ($mediaContentIdentities as $identity) {
            if ($identity->getEntityType() === self::FIELD_CMS_PAGE
                || $identity->getEntityType() === self::FIELD_CMS_BLOCK
            ) {
                $this->updateContentAssetLinks->execute(
                    $identity,
                    $this->getCmsMediaContent($identity->getEntityType(), (int)$identity->getEntityId())
                );
            }
        }
    }

    /**
     * Get cms media content from database
     *
     * @param string $tableName
     * @param int $cmsId
     * @return string
     */
    private function getCmsMediaContent(string $tableName, int $cmsId): string
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
