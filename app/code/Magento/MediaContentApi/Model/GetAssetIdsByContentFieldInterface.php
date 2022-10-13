<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface used to return Asset id by content field.
 * @api
 */
interface GetAssetIdsByContentFieldInterface
{
    /**
     * This function returns asset ids by content field
     *
     * @param string $value
     * @return int[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(string $value): array;
}
