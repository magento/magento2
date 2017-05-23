<?php
/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

use Magento\Framework\ObjectManagerInterface;

/**
 * A simple substitution for the object manager that just creates instances via 'new'
 *
 * This class does not rely on di.xml and creates instances of exactly specified type
 */
class ObjectManager implements ObjectManagerInterface
{
    /**
     * Create an instance of specified type
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     */
    public function create($type, array $arguments = [])
    {
        $argsList = [];
        $construct = new \ReflectionMethod($type, '__construct');
        foreach ($construct->getParameters() as $parameter) {
            if (isset($arguments[$parameter->getName()])) {
                $argsList[] = $arguments[$parameter->getName()];
            } else {
                $argsList[] = $parameter->getDefaultValue();
            }
        }
        return new $type(...array_values($argsList));
    }

    /**
     * This implementation does not keep references to created objects
     *
     * @param string $type
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($type)
    {
        return null;
    }

    /**
     * This implementation does not support configuration
     *
     * @param array $configuration
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function configure(array $configuration)
    {
    }
}
