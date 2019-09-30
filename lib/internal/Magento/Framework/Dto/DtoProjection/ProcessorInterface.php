<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\DtoProjection;

/**
 * DTO projection processor
 */
interface ProcessorInterface
{
    /**
     * Perform processor mapping
     *
     * @param array $source
     * @return array
     */
    public function execute(array $source): array;
}
