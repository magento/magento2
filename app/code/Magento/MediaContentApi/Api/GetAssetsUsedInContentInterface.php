<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

/**
 * Get media asset ids used in the content
 * @api
 */
interface GetAssetsUsedInContentInterface
{
    /**
     * Get media asset ids used in the content
     *
     * @param string $contentType
     * @param string|null $contentEntityId
     * @param string|null $contentField
     *
     * @return int[]
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function execute(string $contentType, string $contentEntityId = null, string $contentField = null): array;
}
