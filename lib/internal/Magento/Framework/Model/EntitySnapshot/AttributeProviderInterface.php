<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\EntitySnapshot;

/**
 * Interface AttributeProviderInterface
 */
interface AttributeProviderInterface
{
    /**
     * @param string $entityType
     * @return array
     */
    public function getAttributes($entityType);
}
