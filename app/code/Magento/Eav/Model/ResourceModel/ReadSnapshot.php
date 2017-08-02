<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\Model\Entity\ScopeInterface;

/**
 * Class ReadSnapshot
 * @since 2.1.0
 */
class ReadSnapshot extends ReadHandler
{
    /**
     * @param ScopeInterface $scope
     * @return array
     * @since 2.1.0
     */
    protected function getContextVariables(ScopeInterface $scope)
    {
        $data[] = $scope->getValue();
        return $data;
    }
}
