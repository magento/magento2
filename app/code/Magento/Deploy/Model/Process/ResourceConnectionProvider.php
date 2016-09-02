<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Process;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class ResourceConnectionProvider
{
    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function get()
    {
        return ObjectManager::getInstance()->get(ResourceConnection::class);
    }
}
