<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Reader;

/**
 * Class \Magento\Setup\Module\Di\Code\Reader\Type
 *
 * @since 2.0.0
 */
class Type
{
    /**
     * Whether instance is concrete implementation
     *
     * @param string $type
     * @return bool
     * @since 2.0.0
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
