<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCatalog\Model\Synchronizer;

use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;

class SynchronizeIdentities implements SynchronizeIdentitiesInterface
{
    private const FIELD_CATALOG_PRODUCT = 'catalog_product';
    private const FIELD_CATALOG_CATEGORY = 'catalog_category';

    /**
     * @var UpdateContentAssetLinksInterface
     */
    private $updateContentAssetLinks;

    /**
     * @var GetEntityContentsInterface
     */
    private $getEntityContents;

    /**
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param GetEntityContentsInterface $getEntityContents
     */
    public function __construct(
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        GetEntityContentsInterface $getEntityContents
    ) {
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->getEntityContents = $getEntityContents;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $mediaContentIdentities): void
    {
        foreach ($mediaContentIdentities as $identity) {
            if ($identity->getEntityType() === self::FIELD_CATALOG_PRODUCT
                || $identity->getEntityType() === self::FIELD_CATALOG_CATEGORY
            ) {
                $this->updateContentAssetLinks->execute(
                    $identity,
                    implode(PHP_EOL, $this->getEntityContents->execute($identity))
                );
            }
        }
    }
}
