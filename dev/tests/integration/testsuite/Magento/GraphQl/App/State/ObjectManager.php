<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\TestFramework\ObjectManager as TestFrameworkObjectManager;
use Weakmap;

/**
 * ObjectManager decorator used by GraphQlStateTest for resetting objects and getting initial properties from objects
 */
class ObjectManager extends TestFrameworkObjectManager
{
    /**
     * Constructs this instance by copying test framework's ObjectManager
     *
     * @param TestFrameworkObjectManager $testFrameworkObjectManager
     */
    public function __construct(TestFrameworkObjectManager $testFrameworkObjectManager)
    {
        /* Note: PHP doesn't have copy constructors, so we have to use get_object_vars,
         * but luckily all the properties in the superclass are protected. */
        $properties = get_object_vars($testFrameworkObjectManager);
        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
        $this->_factory = new DynamicFactoryDecorator($this->_factory, $this);
    }

    /**
     * Returns the WeakMap used by DynamicFactoryDecorator
     *
     * @return WeakMap
     */
    public function getWeakMap() : WeakMap
    {
        return $this->_factory->getWeakMap();
    }

    /**
     * Returns shared instances
     *
     * @return object[]
     */
    public function getSharedInstances() : array
    {
        return $this->_sharedInstances;
    }

    /**
     * Resets all factory objects that implement ResetAfterRequestInterface
     */
    public function resetStateWeakMapObjects() : void
    {
        $this->_factory->_resetState();
    }

    /**
     * Resets all objects sharing state & implementing ResetAfterRequestInterface
     */
    public function resetStateSharedInstances() : void
    {
        /** @var object $object */
        foreach ($this->_sharedInstances as $object) {
            if ($object instanceof ResetAfterRequestInterface) {
                $object->_resetState();
            }
        }
    }
}
