<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class for basic object retrieving, such as blocks, models etc...
 */
class Magento_Test_Helper_ObjectManager
{
    /**#@+
     * Supported entities keys.
     */
    const BLOCK_ENTITY = 'block';
    const MODEL_ENTITY = 'model';
    /**#@-*/

    /**
     * List of supported entities which can be initialized with their dependencies
     * Example:
     * array(
     *     'entityName' => array(
     *         'paramName' => 'Mage_Class_Name' or 'callbackMethod'
     *     )
     * );
     *
     * @var array
     */
    protected $_supportedEntities = array(
        self::BLOCK_ENTITY => array(
            'request'            => 'Mage_Core_Controller_Request_Http',
            'layout'             => 'Mage_Core_Model_Layout',
            'eventManager'       => 'Mage_Core_Model_Event_Manager',
            'urlBuilder'         => 'Mage_Core_Model_Url',
            'translator'         => 'Mage_Core_Model_Translate',
            'cache'              => 'Mage_Core_Model_Cache',
            'designPackage'      => 'Mage_Core_Model_Design_Package',
            'session'            => 'Mage_Core_Model_Session',
            'storeConfig'        => 'Mage_Core_Model_Store_Config',
            'frontController'    => 'Mage_Core_Controller_Varien_Front',
            'helperFactory'      => 'Mage_Core_Model_Factory_Helper'
        ),
        self::MODEL_ENTITY => array(
            'eventDispatcher'    => 'Mage_Core_Model_Event_Manager',
            'cacheManager'       => 'Mage_Core_Model_Cache',
            'resource'           => '_getResourceModelMock',
            'resourceCollection' => 'Varien_Data_Collection_Db',
        )
    );

    /**
     * Test object
     *
     * @var PHPUnit_Framework_TestCase
     */
    protected $_testObject;

    /**
     * Class constructor
     *
     * @param PHPUnit_Framework_TestCase $testObject
     */
    public function __construct(PHPUnit_Framework_TestCase $testObject)
    {
        $this->_testObject = $testObject;
    }

    /**
     * Get block instance
     *
     * @param string $className
     * @param array $arguments
     * @return Mage_Core_Block_Abstract
     */
    public function getBlock($className, array $arguments = array())
    {
        $arguments = $this->getConstructArguments(self::BLOCK_ENTITY, $className, $arguments);
        return $this->_getInstanceViaConstructor($className, $arguments);
    }

    /**
     * Get model instance
     *
     * @param string $className
     * @param array $arguments
     * @return Mage_Core_Model_Abstract
     */
    public function getModel($className, array $arguments = array())
    {
        $arguments = $this->getConstructArguments(self::MODEL_ENTITY, $className, $arguments);
        return $this->_getInstanceViaConstructor($className, $arguments);
    }

    /**
     * Retrieve list of arguments that used for new block instance creation
     *
     * @param string $entityName
     * @param string $className
     * @param array $arguments
     * @throws InvalidArgumentException
     * @return array
     */
    public function getConstructArguments($entityName, $className = '', array $arguments = array())
    {
        if (!array_key_exists($entityName, $this->_supportedEntities)) {
            throw new InvalidArgumentException('Unsupported entity type');
        }

        $constructArguments = array();
        foreach ($this->_supportedEntities[$entityName] as $propertyName => $propertyType) {
            if (!isset($arguments[$propertyName])) {
                if (method_exists($this, $propertyType)) {
                    $constructArguments[$propertyName] = $this->$propertyType();
                } else {
                    $constructArguments[$propertyName] = $this->_getMockWithoutConstructorCall($propertyType);
                }
            }
        }
        $constructArguments = array_merge($constructArguments, $arguments);

        if ($className) {
            return $this->_sortConstructorArguments($className, $constructArguments);
        } else {
            return $constructArguments;
        }
    }

    /**
     * Retrieve specific mock of core resource model
     *
     * @return Mage_Core_Model_Resource_Resource|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getResourceModelMock()
    {
        $resourceMock = $this->_testObject->getMock('Mage_Core_Model_Resource_Resource', array('getIdFieldName'),
            array(), '', false
        );
        $resourceMock->expects($this->_testObject->any())
            ->method('getIdFieldName')
            ->will($this->_testObject->returnValue('id'));

        return $resourceMock;
    }

    /**
     * Sort constructor arguments array as is defined for current class interface
     *
     * @param string $className
     * @param array $arguments
     * @return array
     */
    protected function _sortConstructorArguments($className, array $arguments)
    {
        $constructArguments = array();
        $method = new ReflectionMethod($className, '__construct');
        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            if (isset($arguments[$parameterName])) {
                $constructArguments[$parameterName] = $arguments[$parameterName];
            } else {
                if ($parameter->isDefaultValueAvailable()) {
                    $constructArguments[$parameterName] = $parameter->getDefaultValue();
                } else {
                    $constructArguments[$parameterName] = null;
                }
            }
        }

        return $constructArguments;
    }

    /**
     * Get mock without call of original constructor
     *
     * @param string $className
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMockWithoutConstructorCall($className)
    {
        return $this->_testObject->getMock($className, array(), array(), '', false);
    }

    /**
     * Get class instance via constructor
     *
     * @param string $className
     * @param array $arguments
     * @return object
     */
    protected function _getInstanceViaConstructor($className, array $arguments = array())
    {
        $reflectionClass = new ReflectionClass($className);
        return $reflectionClass->newInstanceArgs($arguments);
    }
}
