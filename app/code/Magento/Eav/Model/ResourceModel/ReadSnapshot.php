<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\Model\Entity\ScopeInterface;

/**
 * Class ReadSnapshot
 */
class ReadSnapshot extends ReadHandler
{
    /**
     * @param ScopeInterface $scope
     * @return array
     */
    protected function getContextVariables(ScopeInterface $scope)
    {
        $data[] = $scope->getValue();
        return $data;
    }
}
