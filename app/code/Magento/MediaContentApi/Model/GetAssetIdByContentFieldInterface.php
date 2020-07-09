<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model;

/**
 * Interface used to return Asset id by content field.
 */
interface GetAssetIdByContentFieldInterface
{
    /**
     * This function returns asset ids by content field
     *
     * @param string $value
     * @return int[]
     */
    public function execute(string $value): array;
}
