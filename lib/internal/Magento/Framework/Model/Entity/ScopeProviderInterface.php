<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class ScopeProviderInterface
 */
interface ScopeProviderInterface
{
    /**
     * @param string $entityType
     * @param array $entityData
     * @return mixed
     */
    public function getContext($entityType, $entityData = []);
}
