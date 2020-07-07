<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model;

/**
 * Interface used to return Asset id by content status.
 */
interface GetAssetIdByContentStatusInterface
{
    /**
     * This function returns asset ids by entity status
     *
     * @param string $status
     * @return int[]
     */
    public function execute(string $status): array;
}
