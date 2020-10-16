<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\EntitySnapshot;

/**
 * Interface AttributeProviderInterface
 */
interface AttributeProviderInterface
{
    /**
     * Returns array of fields
     *
     * @param string $entityType
     * @return string[]
     */
    public function getAttributes($entityType);
}
