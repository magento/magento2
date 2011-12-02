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
 * @package    Zend_Dojo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Data.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * dojo.data support for Zend Framework
 *
 * @uses       ArrayAccess
 * @uses       Iterator
 * @uses       Countable
 * @package    Zend_Dojo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_Data implements ArrayAccess,Iterator,Countable
{
    /**
     * Identifier field of item
     * @var string|int
     */
    protected $_identifier;

    /**
     * Collected items
     * @var array
     */
    protected $_items = array();

    /**
     * Label field of item
     * @var string
     */
    protected $_label;

    /**
     * Data container metadata
     * @var array
     */
    protected $_metadata = array();

    /**
     * Constructor
     *
     * @param  string|null $identifier
     * @param  array|Traversable|null $items
     * @param  string|null $label
     * @return void
     */
    public function __construct($identifier = null, $items = null, $label = null)
    {
        if (null !== $identifier) {
            $this->setIdentifier($identifier);
        }
        if (null !== $items) {
            $this->setItems($items);
        }
        if (null !== $label) {
            $this->setLabel($label);
        }
    }

    /**
     * Set the items to collect
     *
     * @param array|Traversable $items
     * @return Zend_Dojo_Data
     */
    public function setItems($items)
    {
        $this->clearItems();
        return $this->addItems($items);
    }

    /**
     * Set an individual item, optionally by identifier (overwrites)
     *
     * @param  array|object $item
     * @param  string|null $identifier
     * @return Zend_Dojo_Data
     */
    public function setItem($item, $id = null)
    {
        $item = $this->_normalizeItem($item, $id);
        $this->_items[$item['id']] = $item['data'];
        return $this;
    }

    /**
     * Add an individual item, optionally by identifier
     *
     * @param  array|object $item
     * @param  string|null $id
     * @return Zend_Dojo_Data
     */
    public function addItem($item, $id = null)
    {
        $item = $this->_normalizeItem($item, $id);

        if ($this->hasItem($item['id'])) {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('Overwriting items using addItem() is not allowed');
        }

        $this->_items[$item['id']] = $item['data'];

        return $this;
    }

    /**
     * Add multiple items at once
     *
     * @param  array|Traversable $items
     * @return Zend_Dojo_Data
     */
    public function addItems($items)
    {
        if (!is_array($items) && (!is_object($items) || !($items instanceof Traversable))) {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('Only arrays and Traversable objects may be added to ' . __CLASS__);
        }

        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Get all items as an array
     *
     * Serializes items to arrays.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Does an item with the given identifier exist?
     *
     * @param  string|int $id
     * @return bool
     */
    public function hasItem($id)
    {
        return array_key_exists($id, $this->_items);
    }

    /**
     * Retrieve an item by identifier
     *
     * Item retrieved will be flattened to an array.
     *
     * @param  string $id
     * @return array
     */
    public function getItem($id)
    {
        if (!$this->hasItem($id)) {
            return null;
        }

        return $this->_items[$id];
    }

    /**
     * Remove item by identifier
     *
     * @param  string $id
     * @return Zend_Dojo_Data
     */
    public function removeItem($id)
    {
        if ($this->hasItem($id)) {
            unset($this->_items[$id]);
        }

        return $this;
    }

    /**
     * Remove all items at once
     *
     * @return Zend_Dojo_Data
     */
    public function clearItems()
    {
        $this->_items = array();
        return $this;
    }


    /**
     * Set identifier for item lookups
     *
     * @param  string|int|null $identifier
     * @return Zend_Dojo_Data
     */
    public function setIdentifier($identifier)
    {
        if (null === $identifier) {
            $this->_identifier = null;
        } elseif (is_string($identifier)) {
            $this->_identifier = $identifier;
        } elseif (is_numeric($identifier)) {
            $this->_identifier = (int) $identifier;
        } else {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('Invalid identifier; please use a string or integer');
        }

        return $this;
    }

    /**
     * Retrieve current item identifier
     *
     * @return string|int|null
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }


    /**
     * Set label to use for displaying item associations
     *
     * @param  string|null $label
     * @return Zend_Dojo_Data
     */
    public function setLabel($label)
    {
        if (null === $label) {
            $this->_label = null;
        } else {
            $this->_label = (string) $label;
        }
        return $this;
    }

    /**
     * Retrieve item association label
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Set metadata by key or en masse
     *
     * @param  string|array $spec
     * @param  mixed $value
     * @return Zend_Dojo_Data
     */
    public function setMetadata($spec, $value = null)
    {
        if (is_string($spec) && (null !== $value)) {
            $this->_metadata[$spec] = $value;
        } elseif (is_array($spec)) {
            foreach ($spec as $key => $value) {
                $this->setMetadata($key, $value);
            }
        }
        return $this;
    }

    /**
     * Get metadata item or all metadata
     *
     * @param  null|string $key Metadata key when pulling single metadata item
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        if (null === $key) {
            return $this->_metadata;
        }

        if (array_key_exists($key, $this->_metadata)) {
            return $this->_metadata[$key];
        }

        return null;
    }

    /**
     * Clear individual or all metadata item(s)
     *
     * @param  null|string $key
     * @return Zend_Dojo_Data
     */
    public function clearMetadata($key = null)
    {
        if (null === $key) {
            $this->_metadata = array();
        } elseif (array_key_exists($key, $this->_metadata)) {
            unset($this->_metadata[$key]);
        }
        return $this;
    }

    /**
     * Load object from array
     *
     * @param  array $data
     * @return Zend_Dojo_Data
     */
    public function fromArray(array $data)
    {
        if (array_key_exists('identifier', $data)) {
            $this->setIdentifier($data['identifier']);
        }
        if (array_key_exists('label', $data)) {
            $this->setLabel($data['label']);
        }
        if (array_key_exists('items', $data) && is_array($data['items'])) {
            $this->setItems($data['items']);
        } else {
            $this->clearItems();
        }
        return $this;
    }

    /**
     * Load object from JSON
     *
     * @param  string $json
     * @return Zend_Dojo_Data
     */
    public function fromJson($json)
    {
        if (!is_string($json)) {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('fromJson() expects JSON input');
        }
        #require_once 'Zend/Json.php';
        $data = Zend_Json::decode($json);
        return $this->fromArray($data);
    }

    /**
     * Seralize entire data structure, including identifier and label, to array
     *
     * @return array
     */
    public function toArray()
    {
        if (null === ($identifier = $this->getIdentifier())) {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('Serialization requires that an identifier be present in the object; first call setIdentifier()');
        }

        $array = array(
            'identifier' => $identifier,
            'items'      => array_values($this->getItems()),
        );

        $metadata = $this->getMetadata();
        if (!empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $array[$key] = $value;
            }
        }

        if (null !== ($label = $this->getLabel())) {
            $array['label'] = $label;
        }

        return $array;
    }

    /**
     * Serialize to JSON (dojo.data format)
     *
     * @return string
     */
    public function toJson()
    {
        #require_once 'Zend/Json.php';
        return Zend_Json::encode($this->toArray());
    }

    /**
     * Serialize to string (proxy to {@link toJson()})
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * ArrayAccess: does offset exist?
     *
     * @param  string|int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (null !== $this->getItem($offset));
    }

    /**
     * ArrayAccess: retrieve by offset
     *
     * @param  string|int $offset
     * @return array
     */
    public function offsetGet($offset)
    {
        return $this->getItem($offset);
    }

    /**
     * ArrayAccess: set value by offset
     *
     * @param  string $offset
     * @param  array|object|null $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setItem($value, $offset);
    }

    /**
     * ArrayAccess: unset value by offset
     *
     * @param  string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->removeItem($offset);
    }

    /**
     * Iterator: get current value
     *
     * @return array
     */
    public function current()
    {
        return current($this->_items);
    }

    /**
     * Iterator: get current key
     *
     * @return string|int
     */
    public function key()
    {
        return key($this->_items);
    }

    /**
     * Iterator: get next item
     *
     * @return void
     */
    public function next()
    {
        return next($this->_items);
    }

    /**
     * Iterator: rewind to first value in collection
     *
     * @return void
     */
    public function rewind()
    {
        return reset($this->_items);
    }

    /**
     * Iterator: is item valid?
     *
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->current();
    }

    /**
     * Countable: how many items are present
     *
     * @return int
     */
    public function count()
    {
        return count($this->_items);
    }

    /**
     * Normalize an item to attach to the collection
     *
     * @param  array|object $item
     * @param  string|int|null $id
     * @return array
     */
    protected function _normalizeItem($item, $id)
    {
        if (null === ($identifier = $this->getIdentifier())) {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('You must set an identifier prior to adding items');
        }

        if (!is_object($item) && !is_array($item)) {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('Only arrays and objects may be attached');
        }

        if (is_object($item)) {
            if (method_exists($item, 'toArray')) {
                $item = $item->toArray();
            } else {
                $item = get_object_vars($item);
            }
        }

        if ((null === $id) && !array_key_exists($identifier, $item)) {
            #require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('Item must contain a column matching the currently set identifier');
        } elseif (null === $id) {
            $id = $item[$identifier];
        } else {
            $item[$identifier] = $id;
        }

        return array(
            'id'   => $id,
            'data' => $item,
        );
    }
}
