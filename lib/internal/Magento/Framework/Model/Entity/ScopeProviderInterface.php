<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class ScopeProviderInterface
 */
interface ScopeProviderInterface
{
    /**
     * @param $entityType
     * @param array $entityData
     * @return mixed
     */
    public function getContext($entityType, $entityData = []);
}
