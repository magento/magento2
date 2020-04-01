<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\AssignAssetInterface;
use Magento\MediaContentApi\Api\ExtractAssetFromContentInterface;
use Magento\MediaContentApi\Api\GetAssetsUsedInContentInterface;
use Magento\MediaContentApi\Api\UnassignAssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Psr\Log\LoggerInterface;

/**
 * Process relation managing between media asset and content: assign or unassign relation if exists.
 */
interface UpdateRelationsInterface
{
    /**
     * Create new relation between media asset and content or updated existing
     *
     * @param string $type
     * @param string $field
     * @param string $entityId
     * @param string $data
     */
    public function execute(string $type, string $field, string $entityId, string $data): void;
}
