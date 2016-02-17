<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di;

/**
 * Registry of instantiated objects, their names and the parameters used to build them
 */
class InstanceManager /* implements InstanceManagerInterface */
{
    /**
     * Array of shared instances
     * @var array
     */
    protected $sharedInstances = array();

    /**
     * Array of shared instances with params
     * @var array
     */
    protected $sharedInstancesWithParams = array('hashShort' => array(), 'hashLong' => array());

    /**
     * Array of class aliases
     * @var array key: alias, value: class
     */
    protected $aliases = array();

    /**
     * The template to use for housing configuration information
     * @var array
     */
    protected $configurationTemplate = array(
        /**
         * alias|class => alias|class
         * interface|abstract => alias|class|object
         * name => value
         */
        'parameters' => array(),
        /**
         * injection type => array of ordered method params
         */
        'injections' => array(),
        /**
         * alias|class => bool
         */
        'shared' => true
    );

    /**
     * An array of instance configuration data
     * @var array
     */
    protected $configurations = array();

    /**
     * An array of globally preferred implementations for interfaces/abstracts
     * @var array
     */
    protected $typePreferences = array();

    /**
     * Does this instance manager have this shared instance
     * @param  string $classOrAlias
     * @return bool
     */
    public function hasSharedInstance($classOrAlias)
    {
        return isset($this->sharedInstances[$classOrAlias]);
    }

    /**
     * getSharedInstance()
     */
    public function getSharedInstance($classOrAlias)
    {
        return $this->sharedInstances[$classOrAlias];
    }

    /**
     * Add shared instance
     *
     * @param  object                             $instance
     * @param  string                             $classOrAlias
     * @throws Exception\InvalidArgumentException
     */
    public function addSharedInstance($instance, $classOrAlias)
    {
        if (!is_object($instance)) {
            throw new Exception\InvalidArgumentException('This method requires an object to be shared. Class or Alias given: ' . $classOrAlias);
        }

        $this->sharedInstances[$classOrAlias] = $instance;
    }

    /**
     * hasSharedInstanceWithParameters()
     *
     * @param  string      $classOrAlias
     * @param  array       $params
     * @param  bool        $returnFastHashLookupKey
     * @return bool|string
     */
    public function hasSharedInstanceWithParameters($classOrAlias, array $params, $returnFastHashLookupKey = false)
    {
        ksort($params);
        $hashKey = $this->createHashForKeys($classOrAlias, array_keys($params));
        if (isset($this->sharedInstancesWithParams['hashShort'][$hashKey])) {
            $hashValue = $this->createHashForValues($classOrAlias, $params);
            if (isset($this->sharedInstancesWithParams['hashLong'][$hashKey . '/' . $hashValue])) {
                return ($returnFastHashLookupKey) ? $hashKey . '/' . $hashValue : true;
            }
        }

        return false;
    }

    /**
     * addSharedInstanceWithParameters()
     *
     * @param  object $instance
     * @param  string $classOrAlias
     * @param  array  $params
     * @return void
     */
    public function addSharedInstanceWithParameters($instance, $classOrAlias, array $params)
    {
        ksort($params);
        $hashKey = $this->createHashForKeys($classOrAlias, array_keys($params));
        $hashValue = $this->createHashForValues($classOrAlias, $params);

        if (!isset($this->sharedInstancesWithParams[$hashKey])
            || !is_array($this->sharedInstancesWithParams[$hashKey])) {
            $this->sharedInstancesWithParams[$hashKey] = array();
        }

        $this->sharedInstancesWithParams['hashShort'][$hashKey] = true;
        $this->sharedInstancesWithParams['hashLong'][$hashKey . '/' . $hashValue] = $instance;
    }

    /**
     * Retrieves an instance by its name and the parameters stored at its instantiation
     *
     * @param  string      $classOrAlias
     * @param  array       $params
     * @param  bool|null   $fastHashFromHasLookup
     * @return object|bool false if no instance was found
     */
    public function getSharedInstanceWithParameters($classOrAlias, array $params, $fastHashFromHasLookup = null)
    {
        if ($fastHashFromHasLookup) {
            return $this->sharedInstancesWithParams['hashLong'][$fastHashFromHasLookup];
        }

        ksort($params);
        $hashKey = $this->createHashForKeys($classOrAlias, array_keys($params));
        if (isset($this->sharedInstancesWithParams['hashShort'][$hashKey])) {
            $hashValue = $this->createHashForValues($classOrAlias, $params);
            if (isset($this->sharedInstancesWithParams['hashLong'][$hashKey . '/' . $hashValue])) {
                return $this->sharedInstancesWithParams['hashLong'][$hashKey . '/' . $hashValue];
            }
        }

        return false;
    }

    /**
     * Check for an alias
     *
     * @param  string $alias
     * @return bool
     */
    public function hasAlias($alias)
    {
        return (isset($this->aliases[$alias]));
    }

    /**
     * Get aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * getClassFromAlias()
     *
     * @param string
     * @return string|bool
     * @throws Exception\RuntimeException
     */
    public function getClassFromAlias($alias)
    {
        if (!isset($this->aliases[$alias])) {
            return false;
        }
        $r = 0;
        while (isset($this->aliases[$alias])) {
            $alias = $this->aliases[$alias];
            $r++;
            if ($r > 100) {
                throw new Exception\RuntimeException(
                    sprintf('Possible infinite recursion in DI alias! Max recursion of 100 levels reached at alias "%s".', $alias)
                );
            }
        }

        return $alias;
    }

    /**
     * @param  string                     $alias
     * @return string|bool
     * @throws Exception\RuntimeException
     */
    protected function getBaseAlias($alias)
    {
        if (!$this->hasAlias($alias)) {
            return false;
        }
        $lastAlias = false;
        $r = 0;
        while (isset($this->aliases[$alias])) {
            $lastAlias = $alias;
            $alias = $this->aliases[$alias];
            $r++;
            if ($r > 100) {
                throw new Exception\RuntimeException(
                    sprintf('Possible infinite recursion in DI alias! Max recursion of 100 levels reached at alias "%s".', $alias)
                );
            }
        }

        return $lastAlias;
    }

    /**
     * Add alias
     *
     * @throws Exception\InvalidArgumentException
     * @param  string                             $alias
     * @param  string                             $class
     * @param  array                              $parameters
     * @return void
     */
    public function addAlias($alias, $class, array $parameters = array())
    {
        if (!preg_match('#^[a-zA-Z0-9-_]+$#', $alias)) {
            throw new Exception\InvalidArgumentException(
                'Aliases must be alphanumeric and can contain dashes and underscores only.'
            );
        }
        $this->aliases[$alias] = $class;
        if ($parameters) {
            $this->setParameters($alias, $parameters);
        }
    }

    /**
     * Check for configuration
     *
     * @param  string $aliasOrClass
     * @return bool
     */
    public function hasConfig($aliasOrClass)
    {
        $key = ($this->hasAlias($aliasOrClass)) ? 'alias:' . $this->getBaseAlias($aliasOrClass) : $aliasOrClass;
        if (!isset($this->configurations[$key])) {
            return false;
        }
        if ($this->configurations[$key] === $this->configurationTemplate) {
            return false;
        }

        return true;
    }

    /**
     * Sets configuration for a single alias/class
     *
     * @param string $aliasOrClass
     * @param array  $configuration
     * @param bool   $append
     */
    public function setConfig($aliasOrClass, array $configuration, $append = false)
    {
        $key = ($this->hasAlias($aliasOrClass)) ? 'alias:' . $this->getBaseAlias($aliasOrClass) : $aliasOrClass;
        if (!isset($this->configurations[$key]) || !$append) {
            $this->configurations[$key] = $this->configurationTemplate;
        }
        // Ignore anything but 'parameters' and 'injections'
        $configuration = array(
            'parameters' => isset($configuration['parameters']) ? $configuration['parameters'] : array(),
            'injections' => isset($configuration['injections']) ? $configuration['injections'] : array(),
            'shared'     => isset($configuration['shared'])     ? $configuration['shared']     : true
        );
        $this->configurations[$key] = array_replace_recursive($this->configurations[$key], $configuration);
    }

    /**
     * Get classes
     *
     * @return array
     */
    public function getClasses()
    {
        $classes = array();
        foreach ($this->configurations as $name => $data) {
            if (strpos($name, 'alias') === 0) {
                continue;
            }

            $classes[] = $name;
        }

        return $classes;
    }

    /**
     * @param  string $aliasOrClass
     * @return array
     */
    public function getConfig($aliasOrClass)
    {
        $key = ($this->hasAlias($aliasOrClass)) ? 'alias:' . $this->getBaseAlias($aliasOrClass) : $aliasOrClass;
        if (isset($this->configurations[$key])) {
            return $this->configurations[$key];
        }

        return $this->configurationTemplate;
    }

    /**
     * setParameters() is a convenience method for:
     *    setConfig($type, array('parameters' => array(...)), true);
     *
     * @param  string $aliasOrClass Alias or Class
     * @param  array  $parameters   Multi-dim array of parameters and their values
     * @return void
     */
    public function setParameters($aliasOrClass, array $parameters)
    {
        $this->setConfig($aliasOrClass, array('parameters' => $parameters), true);
    }

    /**
     * setInjections() is a convenience method for:
     *    setConfig($type, array('injections' => array(...)), true);
     *
     * @param  string $aliasOrClass Alias or Class
     * @param  array  $injections   Multi-dim array of methods and their parameters
     * @return void
     */
    public function setInjections($aliasOrClass, array $injections)
    {
        $this->setConfig($aliasOrClass, array('injections' => $injections), true);
    }

    /**
     * Set shared
     *
     * @param  string $aliasOrClass
     * @param  bool   $isShared
     * @return void
     */
    public function setShared($aliasOrClass, $isShared)
    {
        $this->setConfig($aliasOrClass, array('shared' => (bool) $isShared), true);
    }

    /**
     * Check for type preferences
     *
     * @param  string $interfaceOrAbstract
     * @return bool
     */
    public function hasTypePreferences($interfaceOrAbstract)
    {
        $key = ($this->hasAlias($interfaceOrAbstract)) ? 'alias:' . $interfaceOrAbstract : $interfaceOrAbstract;

        return (isset($this->typePreferences[$key]) && $this->typePreferences[$key]);
    }

    /**
     * Set type preference
     *
     * @param  string          $interfaceOrAbstract
     * @param  array           $preferredImplementations
     * @return InstanceManager
     */
    public function setTypePreference($interfaceOrAbstract, array $preferredImplementations)
    {
        $key = ($this->hasAlias($interfaceOrAbstract)) ? 'alias:' . $interfaceOrAbstract : $interfaceOrAbstract;
        foreach ($preferredImplementations as $preferredImplementation) {
            $this->addTypePreference($key, $preferredImplementation);
        }

        return $this;
    }

    /**
     * Get type preferences
     *
     * @param  string $interfaceOrAbstract
     * @return array
     */
    public function getTypePreferences($interfaceOrAbstract)
    {
        $key = ($this->hasAlias($interfaceOrAbstract)) ? 'alias:' . $interfaceOrAbstract : $interfaceOrAbstract;
        if (isset($this->typePreferences[$key])) {
            return $this->typePreferences[$key];
        }

        return array();
    }

    /**
     * Unset type preferences
     *
     * @param  string $interfaceOrAbstract
     * @return void
     */
    public function unsetTypePreferences($interfaceOrAbstract)
    {
        $key = ($this->hasAlias($interfaceOrAbstract)) ? 'alias:' . $interfaceOrAbstract : $interfaceOrAbstract;
        unset($this->typePreferences[$key]);
    }

    /**
     * Adds a type preference. A type preference is a redirection to a preferred alias or type when an abstract type
     * $interfaceOrAbstract is requested
     *
     * @param  string $interfaceOrAbstract
     * @param  string $preferredImplementation
     * @return self
     */
    public function addTypePreference($interfaceOrAbstract, $preferredImplementation)
    {
        $key = ($this->hasAlias($interfaceOrAbstract)) ? 'alias:' . $interfaceOrAbstract : $interfaceOrAbstract;
        if (!isset($this->typePreferences[$key])) {
            $this->typePreferences[$key] = array();
        }
        $this->typePreferences[$key][] = $preferredImplementation;

        return $this;
    }

    /**
     * Removes a previously set type preference
     *
     * @param  string    $interfaceOrAbstract
     * @param  string    $preferredType
     * @return bool|self
     */
    public function removeTypePreference($interfaceOrAbstract, $preferredType)
    {
        $key = ($this->hasAlias($interfaceOrAbstract)) ? 'alias:' . $interfaceOrAbstract : $interfaceOrAbstract;
        if (!isset($this->typePreferences[$key]) || !in_array($preferredType, $this->typePreferences[$key])) {
            return false;
        }
        unset($this->typePreferences[$key][array_search($key, $this->typePreferences)]);

        return $this;
    }

    /**
     * @param  string   $classOrAlias
     * @param  string[] $paramKeys
     * @return string
     */
    protected function createHashForKeys($classOrAlias, $paramKeys)
    {
        return $classOrAlias . ':' . implode('|', $paramKeys);
    }

    /**
     * @param  string $classOrAlias
     * @param  array  $paramValues
     * @return string
     */
    protected function createHashForValues($classOrAlias, $paramValues)
    {
        $hashValue = '';
        foreach ($paramValues as $param) {
            switch (gettype($param)) {
                case 'object':
                    $hashValue .= spl_object_hash($param) . '|';
                    break;
                case 'integer':
                case 'string':
                case 'boolean':
                case 'NULL':
                case 'double':
                    $hashValue .= $param . '|';
                    break;
                case 'array':
                    $hashValue .= 'Array|';
                    break;
                case 'resource':
                    $hashValue .= 'resource|';
                    break;
            }
        }

        return $hashValue;
    }
}
