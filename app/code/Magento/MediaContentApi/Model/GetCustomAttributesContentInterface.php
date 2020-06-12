<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model;

/**
 * Get concatenated content from custom attributes.
 */
interface GetCustomAttributesContentInterface
{
    /**
     * Get product custom attributes content
     *
     * @param string $entityType
     * @param int $entityId
     */
    public function execute(string $entityType, int $entityId): array;
}
