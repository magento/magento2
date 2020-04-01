<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

/**
 * Saving data represents relation between the media asset and media content
 * @api
 */
interface AssignAssetInterface
{
    /**
     * @param int $assetId
     * @param string $contentType
     * @param string $contentEntityId
     * @param string $contentField
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(int $assetId, string $contentType, string $contentEntityId, string $contentField): void;
}
