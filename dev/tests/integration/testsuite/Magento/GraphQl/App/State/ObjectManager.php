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
 * Collects shared objects from ObjectManager and clones properties for later comparison
 */
class ObjectManager extends TestFrameworkObjectManager
{

    private WeakMap $weakMap;
    private Collector $collector;

    /**
     * Constructs this instance by copying test framework's ObjectManager
     *
     * @param TestFrameworkObjectManager $testFrameworkObjectManager
     */
    public function __construct(TestFrameworkObjectManager $testFrameworkObjectManager)
    {
        $this->weakMap = new WeakMap();
        $this->collector = new Collector($this);
        /* Note: PHP doesn't have copy constructors, so we have to use get_object_vars,
         * but luckily all the properties in the superclass are protected. */
        $properties = get_object_vars($testFrameworkObjectManager);
        foreach($properties as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function create($type, array $arguments = [])
    {

        $object = parent::create($type, $arguments);
        $this->weakMap[$object] = $this->collector->getPropertiesFromObject($object, false);
        return $object;
    }

    /**
     * @inheritDoc
     */
    public function get($requestedType)
    {
        $object = parent::get($requestedType);
        if (null === ($this->weakMap[$object] ?? null)) {
            if ($object instanceof ResetAfterRequestInterface) {
                /* Note: some service classes get added to weakMap after they are already used, so
                 * we need to make sure to reset them to get proper initial state after construction for comparison */
                $object->_resetState();
            }
            $this->weakMap[$object] = $this->collector->getPropertiesFromObject($object, false);
        }
        return $object;
    }

    public function getWeakMap() : WeakMap
    {
        return $this->weakMap;
    }
}
