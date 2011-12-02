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
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DynamicTableEntity.php 23167 2010-10-19 17:53:31Z mabe $
 */


/**
 * @see Zend_Service_WindowsAzure_Exception
 */
#require_once 'Zend/Service/WindowsAzure/Exception.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_TableEntity
 */
#require_once 'Zend/Service/WindowsAzure/Storage/TableEntity.php';


/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_DynamicTableEntity extends Zend_Service_WindowsAzure_Storage_TableEntity
{   
    /**
     * Dynamic properties
     * 
     * @var array
     */
    protected $_dynamicProperties = array();
    
    /**
     * Magic overload for setting properties
     * 
     * @param string $name     Name of the property
     * @param string $value    Value to set
     */
    public function __set($name, $value) {      
        $this->setAzureProperty($name, $value, null);
    }

    /**
     * Magic overload for getting properties
     * 
     * @param string $name     Name of the property
     */
    public function __get($name) {
        return $this->getAzureProperty($name);
    }
    
    /**
     * Set an Azure property
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     * @param string $type Property type (Edm.xxxx)
     * @return Zend_Service_WindowsAzure_Storage_DynamicTableEntity
     */
    public function setAzureProperty($name, $value = '', $type = null)
    {
        if (strtolower($name) == 'partitionkey') {
            $this->setPartitionKey($value);
        } else if (strtolower($name) == 'rowkey') {
            $this->setRowKey($value);
        } else if (strtolower($name) == 'etag') {
            $this->setEtag($value);
        } else {
            if (!array_key_exists(strtolower($name), $this->_dynamicProperties)) {
                // Determine type?
                if ($type === null) {
                    $type = 'Edm.String';
                    if (is_int($value)) {
                        $type = 'Edm.Int32';
                    } else if (is_float($value)) {
                        $type = 'Edm.Double';
                    } else if (is_bool($value)) {
                        $type = 'Edm.Boolean';
                    }
                }
                
                // Set dynamic property
                $this->_dynamicProperties[strtolower($name)] = (object)array(
                        'Name'  => $name,
                    	'Type'  => $type,
                    	'Value' => $value,
                    );
            }
    
            $this->_dynamicProperties[strtolower($name)]->Value = $value;
        }
        return $this;
    }
    
    /**
     * Set an Azure property type
     * 
     * @param string $name Property name
     * @param string $type Property type (Edm.xxxx)
     * @return Zend_Service_WindowsAzure_Storage_DynamicTableEntity
     */
    public function setAzurePropertyType($name, $type = 'Edm.String')
    {
        if (!array_key_exists(strtolower($name), $this->_dynamicProperties)) {
            $this->setAzureProperty($name, '', $type);            
        } else {
            $this->_dynamicProperties[strtolower($name)]->Type = $type;   
        }
        return $this;
    }
    
    /**
     * Get an Azure property
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     * @param string $type Property type (Edm.xxxx)
     * @return Zend_Service_WindowsAzure_Storage_DynamicTableEntity
     */
    public function getAzureProperty($name)
    {
        if (strtolower($name) == 'partitionkey') {
            return $this->getPartitionKey();
        }
        if (strtolower($name) == 'rowkey') {
            return $this->getRowKey();
        }
        if (strtolower($name) == 'etag') {
            return $this->getEtag();
        }

        if (!array_key_exists(strtolower($name), $this->_dynamicProperties)) {
            $this->setAzureProperty($name);            
        }

        return $this->_dynamicProperties[strtolower($name)]->Value;
    }
    
    /**
     * Get an Azure property type
     * 
     * @param string $name Property name
     * @return string Property type (Edm.xxxx)
     */
    public function getAzurePropertyType($name)
    {
        if (!array_key_exists(strtolower($name), $this->_dynamicProperties)) {
            $this->setAzureProperty($name, '', $type);            
        }
        
        return $this->_dynamicProperties[strtolower($name)]->Type;
    }
    
    /**
     * Get Azure values
     * 
     * @return array
     */
    public function getAzureValues()
    {
        return array_merge(array_values($this->_dynamicProperties), parent::getAzureValues());
    }
    
    /**
     * Set Azure values
     * 
     * @param array $values
     * @param boolean $throwOnError Throw Zend_Service_WindowsAzure_Exception when a property is not specified in $values?
     * @throws Zend_Service_WindowsAzure_Exception
     */
    public function setAzureValues($values = array(), $throwOnError = false)
    {
        // Set parent values
        parent::setAzureValues($values, false);
        
        // Set current values
        foreach ($values as $key => $value) 
        {
            $this->$key = $value;
        }
    }
}
