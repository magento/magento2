<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Reader;

class Type
{
    /**
     * Whether instance is concrete implementation
     *
     * @param string $type
     * @return bool
     */
    public function isConcrete($type)
    {
        try {
            $instance = new \ReflectionClass($type);
        } catch (\ReflectionException $e) {
            return false;
        }
        return !$instance->isAbstract() && !$instance->isInterface();
    }
}
