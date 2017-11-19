<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Configuration\Utility;

use Magento\Framework\App\ObjectManager;

/**
 * Object Instantiator
 *
 * Wrapper around static ObjectManager usage. Used where we cannot inject
 * ObjectManager directly as the object instantiation is done before the
 * ObjectManager is ready
 */
class ObjectInstantiator
{
    /**
     * Create Object Instance
     *
     * @param string $class
     * @param array $arguments
     * @return mixed
     */
    public function createInstance(string $class, array $arguments = [])
    {
        return ObjectManager::getInstance()->create($class, $arguments);
    }
}
