<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Framework_Loader_Abstract
 */
#require_once 'Zend/Tool/Framework/Loader/Interface.php';

/**
 * @see Zend_Tool_Framework_Registry_EnabledInterface
 */
#require_once 'Zend/Tool/Framework/Registry/EnabledInterface.php';

/**
 * @see Zend_Loader
 */
#require_once 'Zend/Loader.php';
#require_once 'Zend/Tool/Framework/Manifest/Interface.php';
#require_once 'Zend/Tool/Framework/Provider/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Loader_BasicLoader
    implements Zend_Tool_Framework_Loader_Interface, Zend_Tool_Framework_Registry_EnabledInterface
{
    /**
     * @var Zend_Tool_Framework_Repository_Interface
     */
    protected $_registry = null;

    /**
     * @var array
     */
    protected $_classesToLoad = array();

    public function __construct($options = array())
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    public function setOptions(Array $options)
    {
        foreach ($options as $optionName => $optionValue) {
            $setMethod = 'set' . $optionName;
            if (method_exists($this, $setMethod)) {
                $this->{$setMethod}($optionValue);
            }
        }
    }

    /**
     * setRegistry() - required by the enabled interface to get an instance of
     * the registry
     *
     * @param Zend_Tool_Framework_Registry_Interface $registry
     * @return Zend_Tool_Framework_Loader_Abstract
     */
    public function setRegistry(Zend_Tool_Framework_Registry_Interface $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * @param  array $classesToLoad
     * @return Zend_Tool_Framework_Loader_Abstract
     */
    public function setClassesToLoad(array $classesToLoad)
    {
        $this->_classesToLoad = $classesToLoad;
        return $this;
    }

    public function load()
    {
        $manifestRegistry = $this->_registry->getManifestRepository();
        $providerRegistry = $this->_registry->getProviderRepository();

        $loadedClasses = array();

        // loop through the loaded classes and ensure that
        foreach ($this->_classesToLoad as $class) {

            if (!class_exists($class)) {
                Zend_Loader::loadClass($class);
            }

            // reflect class to see if its something we want to load
            $reflectionClass = new ReflectionClass($class);
            if ($this->_isManifestImplementation($reflectionClass)) {
                $manifestRegistry->addManifest($reflectionClass->newInstance());
                $loadedClasses[] = $class;
            }

            if ($this->_isProviderImplementation($reflectionClass)) {
                $providerRegistry->addProvider($reflectionClass->newInstance());
                $loadedClasses[] = $class;
            }

        }

        return $loadedClasses;
    }

    /**
     * @param  ReflectionClass $reflectionClass
     * @return bool
     */
    private function _isManifestImplementation($reflectionClass)
    {
        return (
            $reflectionClass->implementsInterface('Zend_Tool_Framework_Manifest_Interface')
                && !$reflectionClass->isAbstract()
        );
    }

    /**
     * @param  ReflectionClass $reflectionClass
     * @return bool
     */
    private function _isProviderImplementation($reflectionClass)
    {
        $providerRegistry = $this->_registry->getProviderRepository();

        return (
            $reflectionClass->implementsInterface('Zend_Tool_Framework_Provider_Interface')
                && !$reflectionClass->isAbstract()
                && !$providerRegistry->hasProvider($reflectionClass->getName(), false)
        );
    }

}
