<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class ScopeProviderInterface
 * @since 2.1.0
 */
interface ScopeProviderInterface
{
    /**
     * @param string $entityType
     * @param array $entityData
     * @return mixed
     * @since 2.1.0
     */
    public function getContext($entityType, $entityData = []);
}
