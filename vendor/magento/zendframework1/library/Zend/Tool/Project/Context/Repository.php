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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

#require_once 'Zend/Tool/Project/Context/System/Interface.php';
#require_once 'Zend/Tool/Project/Context/System/TopLevelRestrictable.php';
#require_once 'Zend/Tool/Project/Context/System/NotOverwritable.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Context_Repository implements Countable
{

    protected static $_instance = null;
    protected static $_isInitialized = false;

    protected $_shortContextNames = array();
    protected $_contexts          = array();

    /**
     * Enter description here...
     *
     * @return Zend_Tool_Project_Context_Repository
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function resetInstance()
    {
        self::$_instance = null;
        self::$_isInitialized = false;
    }

    protected function __construct()
    {
        if (self::$_isInitialized == false) {
            $this->addContextClass('Zend_Tool_Project_Context_System_ProjectDirectory')
                 ->addContextClass('Zend_Tool_Project_Context_System_ProjectProfileFile')
                 ->addContextClass('Zend_Tool_Project_Context_System_ProjectProvidersDirectory');
            self::$_isInitialized = true;
        }
    }

    public function addContextsFromDirectory($directory, $prefix)
    {
        $prefix = trim($prefix, '_') . '_';
        foreach (new DirectoryIterator($directory) as $directoryItem) {
            if ($directoryItem->isDot() || (substr($directoryItem->getFilename(), -4) !== '.php')) {
                continue;
            }
            $class = $prefix . substr($directoryItem->getFilename(), 0, -4);
            $this->addContextClass($class);
        }
    }


    public function addContextClass($contextClass)
    {
        if (!class_exists($contextClass)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($contextClass);
        }
        $reflectionContextClass = new ReflectionClass($contextClass);
        if ($reflectionContextClass->isInstantiable()) {
            $context = new $contextClass();
            return $this->addContext($context);
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param Zend_Tool_Project_Context_Interface $context
     * @return Zend_Tool_Project_Context_Repository
     */
    public function addContext(Zend_Tool_Project_Context_Interface $context)
    {
        $isSystem       = ($context instanceof Zend_Tool_Project_Context_System_Interface);
        $isTopLevel     = ($context instanceof Zend_Tool_Project_Context_System_TopLevelRestrictable);
        $isOverwritable = !($context instanceof Zend_Tool_Project_Context_System_NotOverwritable);

        $index = (count($this->_contexts)) ? max(array_keys($this->_contexts)) + 1 : 1;

        $normalName = $this->_normalizeName($context->getName());

        if (isset($this->_shortContextNames[$normalName]) && ($this->_contexts[$this->_shortContextNames[$normalName]]['isOverwritable'] === false) ) {
            #require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('Context ' . $context->getName() . ' is not overwriteable.');
        }

        $this->_shortContextNames[$normalName] = $index;
        $this->_contexts[$index] = array(
            'isTopLevel'     => $isTopLevel,
            'isSystem'       => $isSystem,
            'isOverwritable' => $isOverwritable,
            'normalName'     => $normalName,
            'context'        => $context
            );

        return $this;
    }

    public function getContext($name)
    {
        if (!$this->hasContext($name)) {
            #require_once 'Zend/Tool/Project/Context/Exception.php';
            throw new Zend_Tool_Project_Context_Exception('Context by name ' . $name . ' does not exist in the registry.');
        }

        $name = $this->_normalizeName($name);
        return clone $this->_contexts[$this->_shortContextNames[$name]]['context'];
    }

    public function hasContext($name)
    {
        $name = $this->_normalizeName($name);
        return (isset($this->_shortContextNames[$name]) ? true : false);
    }

    public function isSystemContext($name)
    {
        if (!$this->hasContext($name)) {
            return false;
        }

        $name = $this->_normalizeName($name);
        $index = $this->_shortContextNames[$name];
        return $this->_contexts[$index]['isSystemContext'];
    }

    public function isTopLevelContext($name)
    {
        if (!$this->hasContext($name)) {
            return false;
        }
        $name = $this->_normalizeName($name);
        $index = $this->_shortContextNames[$name];
        return $this->_contexts[$index]['isTopLevel'];
    }

    public function isOverwritableContext($name)
    {
        if (!$this->hasContext($name)) {
            return false;
        }
        $name = $this->_normalizeName($name);
        $index = $this->_shortContextNames[$name];
        return $this->_contexts[$index]['isOverwritable'];
    }

    public function count()
    {
        return count($this->_contexts);
    }

    protected function _normalizeName($name)
    {
        return strtolower($name);
    }

}
