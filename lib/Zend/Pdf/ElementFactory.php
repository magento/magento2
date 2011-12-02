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
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ElementFactory.php 22797 2010-08-06 15:02:12Z alexander $
 */


/** Zend_Pdf_ElementFactory_Interface */
#require_once 'Zend/Pdf/ElementFactory/Interface.php';

/**
 * PDF element factory.
 * Responsibility is to log PDF changes
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_ElementFactory implements Zend_Pdf_ElementFactory_Interface
{
    /**
     * List of the modified objects.
     * Also contains new and removed objects
     *
     * Array: ojbectNumber => Zend_Pdf_Element_Object
     *
     * @var array
     */
    private $_modifiedObjects = array();

    /**
     * List of the removed objects
     *
     * Array: ojbectNumber => Zend_Pdf_Element_Object
     *
     * @var SplObjectStorage
     */
    private $_removedObjects;

    /**
     * List of registered objects.
     * Used for resources clean up when factory is destroyed.
     *
     * Array of Zend_Pdf_Element objects
     *
     * @var array
     */
    private $_registeredObjects = array();

    /**
     * PDF object counter.
     * Actually it's an object number for new PDF object
     *
     * @var integer
     */
    private $_objectCount;


    /**
     * List of the attached object factories.
     * Array of Zend_Pdf_ElementFactory_Interface objects
     *
     * @var array
     */
    private $_attachedFactories = array();


    /**
     * Factory internal id
     *
     * @var integer
     */
    private $_factoryId;

    /**
     * Identity, used for factory id generation
     *
     * @var integer
     */
    private static $_identity = 0;


    /**
     * Internal cache to save calculated shifts
     *
     * @var array
     */
    private $_shiftCalculationCache = array();


    /**
     * Object constructor
     *
     * @param integer $objCount
     */
    public function __construct($objCount)
    {
        $this->_objectCount    = (int)$objCount;
        $this->_factoryId      = self::$_identity++;
        $this->_removedObjects = new SplObjectStorage();
    }


    /**
     * Get factory
     *
     * @return Zend_Pdf_ElementFactory_Interface
     */
    public function getFactory()
    {
        return $this;
    }

    /**
     * Factory generator
     *
     * @param integer $objCount
     * @return Zend_Pdf_ElementFactory_Interface
     */
    static public function createFactory($objCount)
    {
        #require_once 'Zend/Pdf/ElementFactory/Proxy.php';
        return new Zend_Pdf_ElementFactory_Proxy(new Zend_Pdf_ElementFactory($objCount));
    }

    /**
     * Close factory and clean-up resources
     *
     * @internal
     */
    public function close()
    {
        $this->_modifiedObjects   = null;
        $this->_removedObjects    = null;
        $this->_attachedFactories = null;

        foreach ($this->_registeredObjects as $obj) {
            $obj->cleanUp();
        }
        $this->_registeredObjects = null;
    }

    /**
     * Get source factory object
     *
     * @return Zend_Pdf_ElementFactory
     */
    public function resolve()
    {
        return $this;
    }

    /**
     * Get factory ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_factoryId;
    }

    /**
     * Set object counter
     *
     * @param integer $objCount
     */
    public function setObjectCount($objCount)
    {
        $this->_objectCount = (int)$objCount;
    }

    /**
     * Get object counter
     *
     * @return integer
     */
    public function getObjectCount()
    {
        $count = $this->_objectCount;

        foreach ($this->_attachedFactories as $attached) {
            $count += $attached->getObjectCount() - 1; // -1 as "0" object is a special case and shared between factories
        }

        return $count;
    }


    /**
     * Attach factory to the current;
     *
     * @param Zend_Pdf_ElementFactory_Interface $factory
     */
    public function attach(Zend_Pdf_ElementFactory_Interface $factory)
    {
        if ( $factory === $this || isset($this->_attachedFactories[$factory->getId()])) {
            /**
             * Don't attach factory twice.
             * We do not check recusively because of nature of attach operation
             * (Pages are always attached to the Documents, Fonts are always attached
             * to the pages even if pages already use Document level object factory and so on)
             */
            return;
        }

        $this->_attachedFactories[$factory->getId()] = $factory;
    }


    /**
     * Calculate object enumeration shift.
     *
     * @param Zend_Pdf_ElementFactory_Interface $factory
     * @return integer
     */
    public function calculateShift(Zend_Pdf_ElementFactory_Interface $factory)
    {
        if ($factory === $this) {
            return 0;
        }

        if (isset($this->_shiftCalculationCache[$factory->_factoryId])) {
            return $this->_shiftCalculationCache[$factory->_factoryId];
        }

        $shift = $this->_objectCount - 1;

        foreach ($this->_attachedFactories as $subFactory) {
            $subFactoryShift = $subFactory->calculateShift($factory);

            if ($subFactoryShift != -1) {
                // context found
                $this->_shiftCalculationCache[$factory->_factoryId] = $shift + $subFactoryShift;
                return $shift + $subFactoryShift;
            } else {
                $shift += $subFactory->getObjectCount()-1;
            }
        }

        $this->_shiftCalculationCache[$factory->_factoryId] = -1;
        return -1;
    }

    /**
     * Clean enumeration shift cache.
     * Has to be used after PDF render operation to let followed updates be correct.
     */
    public function cleanEnumerationShiftCache()
    {
        $this->_shiftCalculationCache = array();

        foreach ($this->_attachedFactories as $attached) {
            $attached->cleanEnumerationShiftCache();
        }
    }

    /**
     * Retrive object enumeration shift.
     *
     * @param Zend_Pdf_ElementFactory_Interface $factory
     * @return integer
     * @throws Zend_Pdf_Exception
     */
    public function getEnumerationShift(Zend_Pdf_ElementFactory_Interface $factory)
    {
        if (($shift = $this->calculateShift($factory)) == -1) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Wrong object context');
        }

        return $shift;
    }

    /**
     * Mark object as modified in context of current factory.
     *
     * @param Zend_Pdf_Element_Object $obj
     * @throws Zend_Pdf_Exception
     */
    public function markAsModified(Zend_Pdf_Element_Object $obj)
    {
        if ($obj->getFactory() !== $this) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Object is not generated by this factory');
        }

        $this->_modifiedObjects[$obj->getObjNum()] = $obj;
    }


    /**
     * Remove object in context of current factory.
     *
     * @param Zend_Pdf_Element_Object $obj
     * @throws Zend_Pdf_Exception
     */
    public function remove(Zend_Pdf_Element_Object $obj)
    {
        if (!$obj->compareFactory($this)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Object is not generated by this factory');
        }

        $this->_modifiedObjects[$obj->getObjNum()] = $obj;
        $this->_removedObjects->attach($obj);
    }


    /**
     * Generate new Zend_Pdf_Element_Object
     *
     * @todo Reusage of the freed object. It's not a support of new feature, but only improvement.
     *
     * @param Zend_Pdf_Element $objectValue
     * @return Zend_Pdf_Element_Object
     */
    public function newObject(Zend_Pdf_Element $objectValue)
    {
        #require_once 'Zend/Pdf/Element/Object.php';
        $obj = new Zend_Pdf_Element_Object($objectValue, $this->_objectCount++, 0, $this);
        $this->_modifiedObjects[$obj->getObjNum()] = $obj;
        return $obj;
    }

    /**
     * Generate new Zend_Pdf_Element_Object_Stream
     *
     * @todo Reusage of the freed object. It's not a support of new feature, but only improvement.
     *
     * @param mixed $objectValue
     * @return Zend_Pdf_Element_Object_Stream
     */
    public function newStreamObject($streamValue)
    {
        #require_once 'Zend/Pdf/Element/Object/Stream.php';
        $obj = new Zend_Pdf_Element_Object_Stream($streamValue, $this->_objectCount++, 0, $this);
        $this->_modifiedObjects[$obj->getObjNum()] = $obj;
        return $obj;
    }


    /**
     * Enumerate modified objects.
     * Returns array of Zend_Pdf_UpdateInfoContainer
     *
     * @param Zend_Pdf_ElementFactory_Interface $rootFactory
     * @return array
     */
    public function listModifiedObjects($rootFactory = null)
    {
        if ($rootFactory == null) {
            $rootFactory = $this;
            $shift = 0;
        } else {
            $shift = $rootFactory->getEnumerationShift($this);
        }

        ksort($this->_modifiedObjects);

        $result = array();
        #require_once 'Zend/Pdf/UpdateInfoContainer.php';
        foreach ($this->_modifiedObjects as $objNum => $obj) {
            if ($this->_removedObjects->contains($obj)) {
                            $result[$objNum+$shift] = new Zend_Pdf_UpdateInfoContainer($objNum + $shift,
                                                                           $obj->getGenNum()+1,
                                                                           true);
            } else {
                $result[$objNum+$shift] = new Zend_Pdf_UpdateInfoContainer($objNum + $shift,
                                                                           $obj->getGenNum(),
                                                                           false,
                                                                           $obj->dump($rootFactory));
            }
        }

        foreach ($this->_attachedFactories as $factory) {
            $result += $factory->listModifiedObjects($rootFactory);
        }

        return $result;
    }

    /**
     * Register object in the factory
     *
     * It's used to clear "parent object" referencies when factory is closed and clean up resources
     *
     * @param string $refString
     * @param Zend_Pdf_Element_Object $obj
     */
    public function registerObject(Zend_Pdf_Element_Object $obj, $refString)
    {
        $this->_registeredObjects[$refString] = $obj;
    }

    /**
     * Fetch object specified by reference
     *
     * @param string $refString
     * @return Zend_Pdf_Element_Object|null
     */
    public function fetchObject($refString)
    {
        if (!isset($this->_registeredObjects[$refString])) {
            return null;
        }
        return $this->_registeredObjects[$refString];
    }


    /**
     * Check if PDF file was modified
     *
     * @return boolean
     */
    public function isModified()
    {
        if (count($this->_modifiedObjects) != 0) {
            return true;
        }

        foreach ($this->_attachedFactories as $subFactory) {
            if ($subFactory->isModified()) {
                return true;
            }
        }

        return false;
    }
}

