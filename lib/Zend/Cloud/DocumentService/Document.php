<?php
/**
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
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Class encapsulating documents. Fields are stored in a name/value
 * array. Data are represented as strings.
 *
 * TODO Can fields be large enough to warrant support for streams?
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Document 
    implements ArrayAccess, IteratorAggregate, Countable
{
    /** key in document denoting identifier */
    const KEY_FIELD = '_id';

    /**
     * ID of this document.
     * @var mixed
     */
    protected $_id;

    /**
     * Name/value array of field names to values.
     * @var array
     */
    protected $_fields;

    /**
     * Construct an instance of Zend_Cloud_DocumentService_Document.
     *
     * If no identifier is provided, but a field matching KEY_FIELD is present,
     * then that field's value will be used as the document identifier.
     *
     * @param  array $fields
     * @param  mixed $id Document identifier
     * @return void
     */
    public function __construct($fields, $id = null)
    {
        if (!is_array($fields) && !$fields instanceof ArrayAccess) {
            #require_once 'Zend/Cloud/DocumentService/Exception.php';
            throw new Zend_Cloud_DocumentService_Exception('Fields must be an array or implement ArrayAccess');
        }

        if (isset($fields[self::KEY_FIELD])) {
            $id = $fields[self::KEY_FIELD];
            unset($fields[self::KEY_FIELD]);
        }

        $this->_fields = $fields;
        $this->setId($id);
    }

    /**
     * Set document identifier
     * 
     * @param  mixed $id 
     * @return Zend_Cloud_DocumentService_Document
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Get ID name.
     *
     * @return string
     */
    public function getId() 
    {
        return $this->_id;
    }

    /**
     * Get fields as array.
     *
     * @return array
     */
    public function getFields() 
    {
        return $this->_fields;
    }

    /**
     * Get field by name.
     *
     * @param  string $name
     * @return mixed
     */
    public function getField($name)
    {
        if (isset($this->_fields[$name])) {
            return $this->_fields[$name];
        }
        return null;
    }
    
    /**
     * Set field by name.
     *
     * @param  string $name
     * @param  mixed $value
     * @return Zend_Cloud_DocumentService_Document
     */
    public function setField($name, $value) 
    {
        $this->_fields[$name] = $value;
        return $this;
    }
    
    /**
     * Overloading: get value
     * 
     * @param  string $name 
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getField($name);
    }

    /**
     * Overloading: set field
     * 
     * @param  string $name 
     * @param  mixed $value 
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setField($name, $value);
    }
    
    /**
     * ArrayAccess: does field exist?
     * 
     * @param  string $name 
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->_fields[$name]);
    }
    
    /**
     * ArrayAccess: get field by name
     * 
     * @param  string $name 
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->getField($name);
    }
    
    /**
     * ArrayAccess: set field to value
     * 
     * @param  string $name 
     * @param  mixed $value 
     * @return void
     */
    public function offsetSet($name, $value)
    {
        $this->setField($name, $value);
    }
    
    /**
     * ArrayAccess: remove field from document
     * 
     * @param  string $name 
     * @return void
     */
    public function offsetUnset($name)
    {
        if ($this->offsetExists($name)) {
            unset($this->_fields[$name]);
        }
    }
    
    /**
     * Overloading: retrieve and set fields by name
     * 
     * @param  string $name 
     * @param  mixed $args 
     * @return mixed
     */
    public function __call($name, $args)
    {
        $prefix = substr($name, 0, 3);
        if ($prefix == 'get') {
            // Get value
            $option = substr($name, 3);
            return $this->getField($option);
        } elseif ($prefix == 'set') {
            // set value
            $option = substr($name, 3);
            return $this->setField($option, $args[0]);
        }

        #require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException("Unknown operation $name");
    }

    /**
     * Countable: return count of fields in document
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_fields);
    }

    /**
     * IteratorAggregate: return iterator for iterating over fields
     * 
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_fields);
    }
}
