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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Tool_Framework_Registry_EnabledInterface
 */
#require_once 'Zend/Tool/Framework/Registry/EnabledInterface.php';

#require_once 'Zend/Tool/Framework/Loader/Interface.php';
#require_once 'Zend/Tool/Framework/Manifest/Interface.php';
#require_once 'Zend/Tool/Framework/Provider/Interface.php';


/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Tool_Framework_Loader_Abstract 
    implements Zend_Tool_Framework_Loader_Interface, Zend_Tool_Framework_Registry_EnabledInterface
{
    /**
     * @var Zend_Tool_Framework_Repository_Interface
     */
    protected $_registry = null;

    /**
     * @var array
     */
    private $_retrievedFiles = array();

    /**
     * @var array
     */
    private $_loadedClasses  = array();

    /**
     * _getFiles
     *
     * @return array Array Of Files
     */
    abstract protected function _getFiles();

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
     * load() - called by the client initialize routine to load files
     *
     */
    public function load()
    {
        $this->_retrievedFiles = $this->getRetrievedFiles();
        $this->_loadedClasses  = array();

        $manifestRepository = $this->_registry->getManifestRepository();
        $providerRepository = $this->_registry->getProviderRepository();

        $loadedClasses = array();

        // loop through files and find the classes declared by loading the file
        foreach ($this->_retrievedFiles as $file) {
            if(is_dir($file)) {
                continue;
            }

            $classesLoadedBefore = get_declared_classes();
            $oldLevel = error_reporting(E_ALL | ~E_STRICT); // remove strict so that other packages wont throw warnings
            // should we lint the files here? i think so
            include_once $file;
            error_reporting($oldLevel); // restore old error level
            $classesLoadedAfter = get_declared_classes();
            $loadedClasses = array_merge($loadedClasses, array_diff($classesLoadedAfter, $classesLoadedBefore));
        }

        // loop through the loaded classes and ensure that
        foreach ($loadedClasses as $loadedClass) {

            // reflect class to see if its something we want to load
            $reflectionClass = new ReflectionClass($loadedClass);
            if ($reflectionClass->implementsInterface('Zend_Tool_Framework_Manifest_Interface')
                && !$reflectionClass->isAbstract())
            {
                $manifestRepository->addManifest($reflectionClass->newInstance());
                $this->_loadedClasses[] = $loadedClass;
            }

            if ($reflectionClass->implementsInterface('Zend_Tool_Framework_Provider_Interface')
                && !$reflectionClass->isAbstract()
                && !$providerRepository->hasProvider($reflectionClass->getName(), false))
            {
                $providerRepository->addProvider($reflectionClass->newInstance());
                $this->_loadedClasses[] = $loadedClass;
            }

        }

        return $this->_loadedClasses;
    }

    /**
     * getRetrievedFiles()
     *
     * @return array Array of Files Retrieved
     */
    public function getRetrievedFiles()
    {
        if ($this->_retrievedFiles == null) {
            $this->_retrievedFiles = $this->_getFiles();
        }

        return $this->_retrievedFiles;
    }

    /**
     * getLoadedClasses()
     *
     * @return array Array of Loaded Classes
     */
    public function getLoadedClasses()
    {
        return $this->_loadedClasses;
    }


}
