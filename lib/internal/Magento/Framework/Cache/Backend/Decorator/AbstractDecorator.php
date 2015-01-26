<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract decorator class for \Zend_Cache_Backend class and its descendants
 */
namespace Magento\Framework\Cache\Backend\Decorator;

abstract class AbstractDecorator extends \Zend_Cache_Backend implements \Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Concrete Cache Backend class that is being decorated
     * @var \Zend_Cache_Backend
     */
    protected $_backend;

    /**
     * Array of specific options. Made in separate array to distinguish from parent options
     * @var array
     */
    protected $_decoratorOptions = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (array_key_exists(
            'concrete_backend',
            $options
        ) && $options['concrete_backend'] instanceof \Zend_Cache_Backend_Interface
        ) {
            $this->_backend = $options['concrete_backend'];
            unset($options['concrete_backend']);
        } else {
            \Zend_Cache::throwException(
                "'concrete_backend' is not specified or it does not implement 'Zend_Cache_Backend_Interface' interface"
            );
        }
        foreach ($options as $optionName => $optionValue) {
            if (array_key_exists($optionName, $this->_decoratorOptions)) {
                $this->_decoratorOptions[$optionName] = $optionValue;
            }
        }
    }

    /**
     * Set the frontend directives
     *
     * @param array $directives assoc of directives
     * @return void
     */
    public function setDirectives($directives)
    {
        $this->_backend->setDirectives($directives);
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param  string  $cacheId                     Cache id
     * @param  boolean $noTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($cacheId, $noTestCacheValidity = false)
    {
        return $this->_backend->load($cacheId, $noTestCacheValidity);
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $cacheId cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($cacheId)
    {
        return $this->_backend->test($cacheId);
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $cacheId          Cache id
     * @param  string[] $tags           Array of strings, the cache record will be tagged by each string entry
     * @param  bool $specificLifetime   If != false, set a specific lifetime for this cache record
     *                                  (null => infinite lifetime)
     * @param  int $priority            integer between 0 (very low priority) and 10 (maximum priority) used by
     *                                  some particular backends
     * @return bool true if no problem
     */
    public function save($data, $cacheId, $tags = [], $specificLifetime = false, $priority = 8)
    {
        return $this->_backend->save($data, $cacheId, $tags, $specificLifetime, $priority);
    }

    /**
     * Remove a cache record
     *
     * @param string $cacheId Cache id
     * @return bool true if no problem
     */
    public function remove($cacheId)
    {
        return $this->_backend->remove($cacheId);
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * \Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * \Zend_Cache::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used)
     * \Zend_Cache::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags
     *                                               ($tags can be an array of strings or a single string)
     * \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)
     * \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG => remove cache entries matching any given tags
     *                                               ($tags can be an array of strings or a single string)
     *
     * @param  string $mode Clean mode
     * @param  string[] $tags Array of tags
     * @return bool true if no problem
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = [])
    {
        return $this->_backend->clean($mode, $tags);
    }

    /**
     * Return an array of stored cache ids
     *
     * @return string[] array of stored cache ids (string)
     */
    public function getIds()
    {
        return $this->_backend->getIds();
    }

    /**
     * Return an array of stored tags
     *
     * @return string[] array of stored tags (string)
     */
    public function getTags()
    {
        return $this->_backend->getTags();
    }

    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of matching cache ids (string)
     */
    public function getIdsMatchingTags($tags = [])
    {
        return $this->_backend->getIdsMatchingTags($tags);
    }

    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of not matching cache ids (string)
     */
    public function getIdsNotMatchingTags($tags = [])
    {
        return $this->_backend->getIdsNotMatchingTags($tags);
    }

    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of any matching cache ids (string)
     */
    public function getIdsMatchingAnyTags($tags = [])
    {
        return $this->_backend->getIdsMatchingAnyTags($tags);
    }

    /**
     * Return the filling percentage of the backend storage
     *
     * @return int integer between 0 and 100
     */
    public function getFillingPercentage()
    {
        return $this->_backend->getFillingPercentage();
    }

    /**
     * Return an array of metadatas for the given cache id
     *
     * The array must include these keys :
     * - expire : the expire timestamp
     * - tags : a string array of tags
     * - mtime : timestamp of last modification time
     *
     * @param string $cacheId cache id
     * @return array|bool array of metadatas (false if the cache id is not found)
     */
    public function getMetadatas($cacheId)
    {
        return $this->_backend->getMetadatas($cacheId);
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $cacheId cache id
     * @param int $extraLifetime
     * @return boolean true if ok
     */
    public function touch($cacheId, $extraLifetime)
    {
        return $this->_backend->touch($cacheId, $extraLifetime);
    }

    /**
     * Return an associative array of capabilities (booleans) of the backend
     *
     * The array must include these keys :
     * - automatic_cleaning (is automating cleaning necessary)
     * - tags (are tags supported)
     * - expired_read (is it possible to read expired cache records
     *                 (for doNotTestCacheValidity option for example))
     * - priority does the backend deal with priority when saving
     * - infinite_lifetime (is infinite lifetime can work with this backend)
     * - get_list (is it possible to get the list of cache ids and the complete list of tags)
     *
     * @return array associative of with capabilities
     */
    public function getCapabilities()
    {
        return $this->_backend->getCapabilities();
    }

    /**
     * Set an option
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws \Zend_Cache_Exception
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->_backend->setOption($name, $value);
    }

    /**
     * Get the life time
     *
     * if $specificLifetime is not false, the given specific life time is used
     * else, the global lifetime is used
     *
     * @param  int $specificLifetime
     * @return int Cache life time
     */
    public function getLifetime($specificLifetime)
    {
        return $this->_backend->getLifetime($specificLifetime);
    }

    /**
     * Determine system TMP directory and detect if we have read access
     *
     * inspired from \Zend_File_Transfer_Adapter_Abstract
     *
     * @return string
     * @throws \Zend_Cache_Exception if unable to determine directory
     */
    public function getTmpDir()
    {
        return $this->_backend->getTmpDir();
    }
}
