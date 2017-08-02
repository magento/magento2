<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\EntitySnapshot;

/**
 * Interface AttributeProviderInterface
 * @since 2.1.0
 */
interface AttributeProviderInterface
{
    /**
     * @param string $entityType
     * @return array
     * @since 2.1.0
     */
    public function getAttributes($entityType);
}
