<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\Code\Reader;

class Type
{
    /** @var array<string, bool> */
    private array $concreteCache = [];

    /**
     * Whether instance is concrete implementation
     *
     * @param string $type
     * @return bool
     */
    public function isConcrete($type)
    {
        if (!array_key_exists($type, $this->concreteCache)) {
            try {
                $instance = new \ReflectionClass($type);
                $this->concreteCache[$type] = !$instance->isAbstract() && !$instance->isInterface();
            } catch (\ReflectionException $e) {
                $this->concreteCache[$type] = false;
            }
        }
        return $this->concreteCache[$type];
    }
}
