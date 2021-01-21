<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Api;

use Magento\Framework\Exception\InvalidArgumentException;

/**
 * Interface used to return Asset id by content field.
 */
interface GetAssetIdsByContentFieldInterface
{
    /**
     * This function returns asset ids by content field
     *
     * @param string $field
     * @param string $value
     * @throws InvalidArgumentException
     * @return int[]
     */
    public function execute(string $field, string $value): array;
}
