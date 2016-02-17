<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

use SplDoublyLinkedList;
use Zend\Di\Definition\RuntimeDefinition;

/**
 * Class definition based on multiple definitions
 */
class DefinitionList extends SplDoublyLinkedList implements Definition\DefinitionInterface
{
    protected $classes = array();
    protected $runtimeDefinitions;

    /**
     * @param Definition\DefinitionInterface|Definition\DefinitionInterface[] $definitions
     */
    public function __construct($definitions)
    {
        $this->runtimeDefinitions = new SplDoublyLinkedList();
        if (!is_array($definitions)) {
            $definitions = array($definitions);
        }
        foreach ($definitions as $definition) {
            $this->addDefinition($definition, true);
        }
    }

    /**
     * Add definitions
     *
     * @param  Definition\DefinitionInterface $definition
     * @param  bool                           $addToBackOfList
     * @return void
     */
    public function addDefinition(Definition\DefinitionInterface $definition, $addToBackOfList = true)
    {
        if ($addToBackOfList) {
            $this->push($definition);
        } else {
            $this->unshift($definition);
        }
    }

    protected function getDefinitionClassMap(Definition\DefinitionInterface $definition)
    {
        $definitionClasses = $definition->getClasses();
        if (empty($definitionClasses)) {
            return array();
        }
        return array_combine(array_values($definitionClasses), array_fill(0, count($definitionClasses), $definition));
    }

    public function unshift($definition)
    {
        $result = parent::unshift($definition);
        if ($definition instanceof RuntimeDefinition) {
            $this->runtimeDefinitions->unshift($definition);
        }
        $this->classes = array_merge($this->classes, $this->getDefinitionClassMap($definition));
        return $result;
    }

    public function push($definition)
    {
        $result = parent::push($definition);
        if ($definition instanceof RuntimeDefinition) {
            $this->runtimeDefinitions->push($definition);
        }
        $this->classes = array_merge($this->getDefinitionClassMap($definition), $this->classes);
        return $result;
    }

    /**
     * @param  string       $type
     * @return Definition\DefinitionInterface[]
     */
    public function getDefinitionsByType($type)
    {
        $definitions = array();
        foreach ($this as $definition) {
            if ($definition instanceof $type) {
                $definitions[] = $definition;
            }
        }

        return $definitions;
    }

    /**
     * Get definition by type
     *
     * @param  string                         $type
     * @return Definition\DefinitionInterface
     */
    public function getDefinitionByType($type)
    {
        foreach ($this as $definition) {
            if ($definition instanceof $type) {
                return $definition;
            }
        }

        return false;
    }

    /**
     * @param  string                              $class
     * @return bool|Definition\DefinitionInterface
     */
    public function getDefinitionForClass($class)
    {
        if (array_key_exists($class, $this->classes)) {
            return $this->classes[$class];
        }
        /** @var $definition Definition\DefinitionInterface */
        foreach ($this->runtimeDefinitions as $definition) {
            if ($definition->hasClass($class)) {
                return $definition;
            }
        }

        return false;
    }

    /**
     * @param  string                              $class
     * @return bool|Definition\DefinitionInterface
     */
    public function forClass($class)
    {
        return $this->getDefinitionForClass($class);
    }

    /**
     * {@inheritDoc}
     */
    public function getClasses()
    {
        return array_keys($this->classes);
    }

    /**
     * {@inheritDoc}
     */
    public function hasClass($class)
    {
        if (array_key_exists($class, $this->classes)) {
            return true;
        }
        /** @var $definition Definition\DefinitionInterface */
        foreach ($this->runtimeDefinitions as $definition) {
            if ($definition->hasClass($class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassSupertypes($class)
    {
        if (false === ($classDefinition = $this->getDefinitionForClass($class))) {
            return array();
        }
        $supertypes = $classDefinition->getClassSupertypes($class);
        if (! $classDefinition instanceof Definition\PartialMarker) {
            return $supertypes;
        }
        /** @var $definition Definition\DefinitionInterface */
        foreach ($this as $definition) {
            if ($definition === $classDefinition) {
                continue;
            }
            if ($definition->hasClass($class)) {
                $supertypes = array_merge($supertypes, $definition->getClassSupertypes($class));
                if ($definition instanceof Definition\PartialMarker) {
                    continue;
                }

                return $supertypes;
            }
        }
        return $supertypes;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstantiator($class)
    {
        if (! $classDefinition = $this->getDefinitionForClass($class)) {
            return false;
        }
        $value = $classDefinition->getInstantiator($class);
        if (!is_null($value)) {
            return $value;
        }
        if (! $classDefinition instanceof Definition\PartialMarker) {
            return false;
        }
        /** @var $definition Definition\DefinitionInterface */
        foreach ($this as $definition) {
            if ($definition === $classDefinition) {
                continue;
            }
            if ($definition->hasClass($class)) {
                $value = $definition->getInstantiator($class);
                if ($value === null && $definition instanceof Definition\PartialMarker) {
                    continue;
                }

                return $value;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethods($class)
    {
        if (! $classDefinition = $this->getDefinitionForClass($class)) {
            return false;
        }
        if (false !== ($methods = $classDefinition->hasMethods($class))) {
            return $methods;
        }
        if (! $classDefinition instanceof Definition\PartialMarker) {
            return false;
        }
        /** @var $definition Definition\DefinitionInterface */
        foreach ($this as $definition) {
            if ($definition === $classDefinition) {
                continue;
            }
            if ($definition->hasClass($class)) {
                if ($definition->hasMethods($class) === false && $definition instanceof Definition\PartialMarker) {
                    continue;
                }

                return $definition->hasMethods($class);
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethod($class, $method)
    {
        if (!$this->hasMethods($class)) {
            return false;
        }
        $classDefinition = $this->getDefinitionForClass($class);
        if ($classDefinition->hasMethod($class, $method)) {
            return true;
        }
        /** @var $definition Definition\DefinitionInterface */
        foreach ($this->runtimeDefinitions as $definition) {
            if ($definition === $classDefinition) {
                continue;
            }
            if ($definition->hasClass($class) && $definition->hasMethod($class, $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods($class)
    {
        if (false === ($classDefinition = $this->getDefinitionForClass($class))) {
            return array();
        }
        $methods = $classDefinition->getMethods($class);
        if (! $classDefinition instanceof Definition\PartialMarker) {
            return $methods;
        }
        /** @var $definition Definition\DefinitionInterface */
        foreach ($this as $definition) {
            if ($definition === $classDefinition) {
                continue;
            }
            if ($definition->hasClass($class)) {
                if (!$definition instanceof Definition\PartialMarker) {
                    return array_merge($definition->getMethods($class), $methods);
                }

                $methods = array_merge($definition->getMethods($class), $methods);
            }
        }

        return $methods;
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethodParameters($class, $method)
    {
        $methodParameters = $this->getMethodParameters($class, $method);

        return ($methodParameters !== array());
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodParameters($class, $method)
    {
        if (false === ($classDefinition = $this->getDefinitionForClass($class))) {
            return array();
        }
        if ($classDefinition->hasMethod($class, $method) && $classDefinition->hasMethodParameters($class, $method)) {
            return $classDefinition->getMethodParameters($class, $method);
        }
        /** @var $definition Definition\DefinitionInterface */
        foreach ($this as $definition) {
            if ($definition === $classDefinition) {
                continue;
            }
            if ($definition->hasClass($class)
                && $definition->hasMethod($class, $method)
                && $definition->hasMethodParameters($class, $method)
            ) {
                return $definition->getMethodParameters($class, $method);
            }
        }

        return array();
    }
}
