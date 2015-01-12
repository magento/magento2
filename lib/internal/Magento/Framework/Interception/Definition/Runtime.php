<?php
/**
 * \Reflection based plugin method list. Uses reflection to retrieve list of interception methods defined in plugin.
 * Should be only used in development mode, because it reads method list on every request which is expensive.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Definition;

use Magento\Framework\Interception\DefinitionInterface;

class Runtime implements DefinitionInterface
{
    /**
     * @var array
     */
    protected $_typesByPrefixes = [
        'befor' => self::LISTENER_BEFORE,
        'aroun' => self::LISTENER_AROUND,
        'after' => self::LISTENER_AFTER,
    ];

    /**
     * Plugin method service prefix lengths
     *
     * @var array
     */
    protected $prefixLengths = [
        self::LISTENER_BEFORE => 6,
        self::LISTENER_AROUND => 6,
        self::LISTENER_AFTER => 5,
    ];

    /**
     * Retrieve list of methods
     *
     * @param string $type
     * @return string[]
     */
    public function getMethodList($type)
    {
        $methods = [];
        $allMethods = get_class_methods($type);
        if ($allMethods) {
            foreach ($allMethods as $method) {
                $prefix = substr($method, 0, 5);
                if (isset($this->_typesByPrefixes[$prefix])) {
                    $methodName = \lcfirst(substr($method, $this->prefixLengths[$this->_typesByPrefixes[$prefix]]));
                    $methods[$methodName] = isset(
                        $methods[$methodName]
                    ) ? $methods[$methodName] | $this->_typesByPrefixes[$prefix] : $this->_typesByPrefixes[$prefix];
                }
            }
        }
        return $methods;
    }
}
