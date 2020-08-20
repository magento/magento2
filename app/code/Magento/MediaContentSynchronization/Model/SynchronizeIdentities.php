<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Model;

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
     * @var array
     */
    private $fields;

    /**
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param GetEntityContentsInterface $getEntityContents
     * @param array $fields
     */
    public function __construct(
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        GetEntityContentsInterface $getEntityContents,
        array $fields = []
    ) {
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->getEntityContents = $getEntityContents;
        $this->fields = $fields;
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
                $content = (string) $identity[self::MEDIA_CONTENT_FIELD];
            } else {
                $content = implode(PHP_EOL, $this->getEntityContents->execute($contentIdentity));
            }

            $this->updateContentAssetLinks->execute(
                $contentIdentity,
                $content
            );
        }
    }
}
