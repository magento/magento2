<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject;

/**
 * Object Cache
 *
 * Stores objects for reuse, cleanup and to avoid circular references
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cache
{
    /**
     * Singleton instance
     *
     * @var \Magento\Framework\DataObject\Cache
     */
    protected static $_instance;

    /**
     * Running object index for anonymous objects
     *
     * @var integer
     */
    protected $_idx = 0;

    /**
     * Array of objects
     *
     * @var array of objects
     */
    protected $_objects = [];

    /**
     * SPL object hashes
     *
     * @var array
     */
    protected $_hashes = [];

    /**
     * SPL hashes by object
     *
     * @var array
     */
    protected $_objectHashes = [];

    /**
     * Objects by tags for cleanup
     *
     * @var array 2D
     */
    protected $_tags = [];

    /**
     * Tags by objects
     *
     * @var array 2D
     */
    protected $_objectTags = [];

    /**
     * References to objects
     *
     * @var array
     */
    protected $_references = [];

    /**
     * References by object
     *
     * @var array 2D
     */
    protected $_objectReferences = [];

    /**
     * Debug data such as backtrace per class
     *
     * @var array
     */
    protected $_debug = [];

    /**
     * Singleton factory
     *
     * @return \Magento\Framework\DataObject\Cache
     */
    public static function singleton()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Load an object from registry
     *
     * @param string|object $idx
     * @param object $default
     * @return object
     */
    public function load($idx, $default = null)
    {
        if (isset($this->_references[$idx])) {
            $idx = $this->_references[$idx];
        }
        if (isset($this->_objects[$idx])) {
            return $this->_objects[$idx];
        }
        return $default;
    }

    /**
     * Save an object entry
     *
     * @param object $object
     * @param string $idx
     * @param array|string $tags
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($object, $idx = null, $tags = null)
    {
        //\Magento\Framework\Profiler::start('OBJECT_SAVE');
        if (!is_object($object)) {
            return false;
        }

        $hash = spl_object_hash($object);
        if ($idx !== null && strpos($idx, '{')) {
            $idx = str_replace('{hash}', $hash, $idx);
        }
        if (isset($this->_hashes[$hash])) {
            //throw new \Exception('test');
            if ($idx !== null) {
                $this->_references[$idx] = $this->_hashes[$hash];
            }
            return $this->_hashes[$hash];
        }

        if ($idx === null) {
            $idx = '#' . ++$this->_idx;
        }

        if (isset($this->_objects[$idx])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    'Object already exists in registry (%1). Old object class: %2, new object class: %3',
                    [$idx, get_class($this->_objects[$idx]), get_class($object)]
                )
            );
        }

        $this->_objects[$idx] = $object;

        $this->_hashes[$hash] = $idx;
        $this->_objectHashes[$idx] = $hash;

        if (is_string($tags)) {
            $this->_tags[$tags][$idx] = true;
            $this->_objectTags[$idx][$tags] = true;
        } elseif (is_array($tags)) {
            foreach ($tags as $t) {
                $this->_tags[$t][$idx] = true;
                $this->_objectTags[$idx][$t] = true;
            }
        }
        //\Magento\Framework\Profiler::stop('OBJECT_SAVE');

        return $idx;
    }

    /**
     * Add a reference to an object
     *
     * @param string|array $refName
     * @param string $idx
     * @return bool|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reference($refName, $idx)
    {
        if (is_array($refName)) {
            foreach ($refName as $ref) {
                $this->reference($ref, $idx);
            }
            return;
        }

        if (isset($this->_references[$refName])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    'The reference already exists: %1. New index: %2, old index: %3',
                    [$refName, $idx, $this->_references[$refName]]
                )
            );
        }
        $this->_references[$refName] = $idx;
        $this->_objectReferences[$idx][$refName] = true;

        return true;
    }

    /**
     * Delete an object from registry
     *
     * @param string|object $idx
     * @return boolean
     */
    public function delete($idx)
    {
        //\Magento\Framework\Profiler::start("OBJECT_DELETE");
        if (is_object($idx)) {
            $idx = $this->find($idx);
            if (false === $idx) {
                //\Magento\Framework\Profiler::stop("OBJECT_DELETE");
                return false;
            }
            unset($this->_objects[$idx]);
            //\Magento\Framework\Profiler::stop("OBJECT_DELETE");
            return false;
        } elseif (!isset($this->_objects[$idx])) {
            //\Magento\Framework\Profiler::stop("OBJECT_DELETE");
            return false;
        }

        unset($this->_objects[$idx]);

        unset($this->_hashes[$this->_objectHashes[$idx]], $this->_objectHashes[$idx]);

        if (isset($this->_objectTags[$idx])) {
            foreach ($this->_objectTags[$idx] as $t => $dummy) {
                unset($this->_tags[$t][$idx]);
            }
            unset($this->_objectTags[$idx]);
        }

        if (isset($this->_objectReferences[$idx])) {
            foreach ($this->_references as $r => $dummy) {
                unset($this->_references[$r]);
            }
            unset($this->_objectReferences[$idx]);
        }
        //\Magento\Framework\Profiler::stop("OBJECT_DELETE");

        return true;
    }

    /**
     * Cleanup by class name for objects of subclasses too
     *
     * @param string $class
     * @return void
     */
    public function deleteByClass($class)
    {
        foreach ($this->_objects as $idx => $object) {
            if ($object instanceof $class) {
                $this->delete($idx);
            }
        }
    }

    /**
     * Cleanup objects by tags
     *
     * @param array|string $tags
     * @return true
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function deleteByTags($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }
        foreach ($tags as $t) {
            foreach ($this->_tags[$t] as $idx => $dummy) {
                $this->delete($idx);
            }
        }
        return true;
    }

    /**
     * Check whether object id exists in registry
     *
     * @param string $idx
     * @return boolean
     */
    public function has($idx)
    {
        return isset($this->_objects[$idx]) || isset($this->_references[$idx]);
    }

    /**
     * Find an object id
     *
     * @param object $object
     * @return string|boolean
     */
    public function find($object)
    {
        foreach ($this->_objects as $idx => $obj) {
            if ($object === $obj) {
                return $idx;
            }
        }
        return false;
    }

    /**
     * Find objects by ids
     *
     * @param string[] $ids
     * @return array
     */
    public function findByIds($ids)
    {
        $objects = [];
        foreach ($this->_objects as $idx => $obj) {
            if (in_array($idx, $ids)) {
                $objects[$idx] = $obj;
            }
        }
        return $objects;
    }

    /**
     * Find object by hash
     *
     * @param string $hash
     * @return object
     */
    public function findByHash($hash)
    {
        return isset($this->_hashes[$hash]) ? $this->_objects[$this->_hashes[$hash]] : null;
    }

    /**
     * Find objects by tags
     *
     * @param array|string $tags
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function findByTags($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }
        $objects = [];
        foreach ($tags as $t) {
            foreach ($this->_tags[$t] as $idx => $dummy) {
                if (isset($objects[$idx])) {
                    continue;
                }
                $objects[$idx] = $this->load($idx);
            }
        }
        return $objects;
    }

    /**
     * Find by class name for objects of subclasses too
     *
     * @param string $class
     * @return array
     */
    public function findByClass($class)
    {
        $objects = [];
        foreach ($this->_objects as $idx => $object) {
            if ($object instanceof $class) {
                $objects[$idx] = $object;
            }
        }
        return $objects;
    }

    /**
     * Debug
     *
     * @param string $idx
     * @param object|null $object
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function debug($idx, $object = null)
    {
        $bt = debug_backtrace();
        $debug = [];
        foreach ($bt as $i => $step) {
            $debug[$i] = [
                'file' => isset($step['file']) ? $step['file'] : null,
                'line' => isset($step['line']) ? $step['line'] : null,
                'function' => isset($step['function']) ? $step['function'] : null,
            ];
        }
        $this->_debug[$idx] = $debug;
    }

    /**
     * Return debug information by ids
     *
     * @param array|string $ids
     * @return array
     */
    public function debugByIds($ids)
    {
        if (is_string($ids)) {
            $ids = [$ids];
        }
        $debug = [];
        foreach ($ids as $idx) {
            $debug[$idx] = $this->_debug[$idx];
        }
        return $debug;
    }

    /**
     * Get all objects
     *
     * @return array
     */
    public function getAllObjects()
    {
        return $this->_objects;
    }

    /**
     * Get all tags
     *
     * @return array
     */
    public function getAllTags()
    {
        return $this->_tags;
    }

    /**
     * Get all tags by object
     *
     * @return array
     */
    public function getAllTagsByObject()
    {
        return $this->_objectTags;
    }

    /**
     * Get all references
     *
     * @return array
     */
    public function getAllReferences()
    {
        return $this->_references;
    }

    /**
     * Get all references by object
     *
     * @return array
     */
    public function getAllReferencesByObject()
    {
        return $this->_referencesByObject;
    }
}
