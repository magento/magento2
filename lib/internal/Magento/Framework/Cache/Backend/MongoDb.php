<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * MongoDb cache backend
 */
namespace Magento\Framework\Cache\Backend;

/**
 * Class \Magento\Framework\Cache\Backend\MongoDb
 *
 * @since 2.0.0
 */
class MongoDb extends \Zend_Cache_Backend implements \Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Infinite expiration time
     */
    const EXPIRATION_TIME_INFINITE = 0;

    /**#@+
     * Available comparison modes. Used for composing queries to search by tags
     */
    const COMPARISON_MODE_MATCHING_TAG = \Zend_Cache::CLEANING_MODE_MATCHING_TAG;

    const COMPARISON_MODE_NOT_MATCHING_TAG = \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG;

    const COMPARISON_MODE_MATCHING_ANY_TAG = \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
    /**#@-*/

    /**
     * @var \MongoCollection|null
     * @since 2.0.0
     */
    protected $_collection = null;

    /**
     * List of available options
     *
     * @var array
     * @since 2.0.0
     */
    protected $_options = [
        'connection_string' => 'mongodb://localhost:27017', // MongoDB connection string
        'mongo_options' => [], // MongoDB connection options
        'db' => '', // Name of a database to be used for cache storage
        'collection' => 'cache', // Name of a collection to be used for cache storage
    ];

    /**
     * @param array $options
     * @since 2.0.0
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('mongo') || !version_compare(\Mongo::VERSION, '1.2.11', '>=')) {
            \Zend_Cache::throwException(
                "At least 1.2.11 version of 'mongo' extension is required for using MongoDb cache backend"
            );
        }
        if (empty($options['db'])) {
            \Zend_Cache::throwException("'db' option is not specified");
        }
        parent::__construct($options);
    }

    /**
     * Get collection
     *
     * @return \MongoCollection
     * @since 2.0.0
     */
    protected function _getCollection()
    {
        if (null === $this->_collection) {
            $connection = new \Mongo($this->_options['connection_string'], $this->_options['mongo_options']);
            $database = $connection->selectDB($this->_options['db']);
            $this->_collection = $database->selectCollection($this->_options['collection']);
        }
        return $this->_collection;
    }

    /**
     * Return an array of stored cache ids
     *
     * @return string[] array of stored cache ids (string)
     * @since 2.0.0
     */
    public function getIds()
    {
        return array_keys(iterator_to_array($this->_getCollection()->find([], ['_id'])));
    }

    /**
     * Return an array of stored tags
     *
     * @return string[] array of stored tags (string)
     * @since 2.0.0
     */
    public function getTags()
    {
        $result = $this->_getCollection()->distinct('tags');
        return $result ?: [];
    }

    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of matching cache ids (string)
     * @since 2.0.0
     */
    public function getIdsMatchingTags($tags = [])
    {
        $query = $this->_getQueryMatchingTags($tags, self::COMPARISON_MODE_MATCHING_TAG);
        if (empty($query)) {
            return [];
        }
        $result = $this->_getCollection()->find($query, ['_id']);
        return array_keys(iterator_to_array($result));
    }

    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of not matching cache ids (string)
     * @since 2.0.0
     */
    public function getIdsNotMatchingTags($tags = [])
    {
        $query = $this->_getQueryMatchingTags($tags, self::COMPARISON_MODE_NOT_MATCHING_TAG);
        if (empty($query)) {
            return [];
        }
        $result = $this->_getCollection()->find($query, ['_id']);
        return array_keys(iterator_to_array($result));
    }

    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param string[] $tags array of tags
     * @return string[] array of any matching cache ids (string)
     * @since 2.0.0
     */
    public function getIdsMatchingAnyTags($tags = [])
    {
        $query = $this->_getQueryMatchingTags($tags, self::COMPARISON_MODE_MATCHING_ANY_TAG);
        if (empty($query)) {
            return [];
        }
        $result = $this->_getCollection()->find($query, ['_id']);
        return array_keys(iterator_to_array($result));
    }

    /**
     * Get query to filter by specified tags and comparison mode
     *
     * @param string[] $tags
     * @param string $comparisonMode
     * @return array
     * @since 2.0.0
     */
    protected function _getQueryMatchingTags(array $tags, $comparisonMode)
    {
        $operators = [
            self::COMPARISON_MODE_MATCHING_TAG => '$and',
            self::COMPARISON_MODE_NOT_MATCHING_TAG => '$nor',
            self::COMPARISON_MODE_MATCHING_ANY_TAG => '$or',
        ];
        if (!isset($operators[$comparisonMode])) {
            \Zend_Cache::throwException("Incorrect comparison mode specified: {$comparisonMode}");
        }
        $operator = $operators[$comparisonMode];
        $query = [];
        foreach ($tags as $tag) {
            $query[$operator][] = ['tags' => $this->_quoteString($tag)];
        }
        return $query;
    }

    /**
     * Return the filling percentage of the backend storage
     *
     * @return int integer between 0 and 100
     * TODO: implement basing on info from MongoDB server
     * @since 2.0.0
     */
    public function getFillingPercentage()
    {
        return 1;
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
     * @return array|false array of metadatas (false if the cache id is not found)
     * @since 2.0.0
     */
    public function getMetadatas($cacheId)
    {
        $result = $this->_getCollection()->findOne(
            ['_id' => $this->_quoteString($cacheId)],
            ['expire', 'tags', 'mtime']
        );
        return $result === null ? false : $result;
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $cacheId cache id
     * @param int $extraLifetime
     * @return boolean true if ok
     * @since 2.0.0
     */
    public function touch($cacheId, $extraLifetime)
    {
        $time = time();
        $condition = ['_id' => $this->_quoteString($cacheId), 'expire' => ['$gt' => $time]];
        $update = ['$set' => ['mtime' => $time], '$inc' => ['expire' => (int)$extraLifetime]];
        return $this->_getCollection()->update($condition, $update);
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
     * @since 2.0.0
     */
    public function getCapabilities()
    {
        return [
            'automatic_cleaning' => true,
            'tags' => true,
            'expired_read' => true,
            'priority' => false,
            'infinite_lifetime' => true,
            'get_list' => true
        ];
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param  string  $cacheId                     Cache id
     * @param  boolean $notTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|bool cached data. Return false if nothing found
     * @since 2.0.0
     */
    public function load($cacheId, $notTestCacheValidity = false)
    {
        $query = ['_id' => $this->_quoteString($cacheId)];
        if (!$notTestCacheValidity) {
            $query['$or'] = [
                ['expire' => self::EXPIRATION_TIME_INFINITE],
                ['expire' => ['$gt' => time()]],
            ];
        }
        $result = $this->_getCollection()->findOne($query, ['data']);
        return $result ? $result['data']->bin : false;
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $cacheId cache id
     * @return int|bool "last modified" timestamp of the available cache record or false if cache is not available
     * @since 2.0.0
     */
    public function test($cacheId)
    {
        $result = $this->_getCollection()->findOne(
            [
                '_id' => $this->_quoteString($cacheId),
                '$or' => [
                    ['expire' => self::EXPIRATION_TIME_INFINITE],
                    ['expire' => ['$gt' => time()]],
                ],
            ],
            ['mtime']
        );
        return $result ? $result['mtime'] : false;
    }

    /**
     * Save some string data into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data            Datas to cache
     * @param  string $cacheId              Cache id
     * @param  string[] $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int|bool $specificLifetime If != false, set a specific lifetime (null => infinite lifetime)
     * @return boolean true if no problem
     * @since 2.0.0
     */
    public function save($data, $cacheId, $tags = [], $specificLifetime = false)
    {
        $lifetime = $this->getLifetime($specificLifetime);
        $time = time();
        $expire = $lifetime === null ? self::EXPIRATION_TIME_INFINITE : $time + $lifetime;
        $tags = array_map([$this, '_quoteString'], $tags);
        $document = [
            '_id' => $this->_quoteString($cacheId),
            'data' => new \MongoBinData($this->_quoteString($data), \MongoBinData::BYTE_ARRAY),
            'tags' => $tags,
            'mtime' => $time,
            'expire' => $expire,
        ];
        return $this->_getCollection()->save($document);
    }

    /**
     * Remove a cache record
     *
     * @param  string $cacheId Cache id
     * @return boolean True if no problem
     * @since 2.0.0
     */
    public function remove($cacheId)
    {
        return $this->_getCollection()->remove(['_id' => $this->_quoteString($cacheId)]);
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
     * @since 2.0.0
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = [])
    {
        $result = false;
        switch ($mode) {
            case \Zend_Cache::CLEANING_MODE_ALL:
                $result = $this->_getCollection()->drop();
                $result = (bool)$result['ok'];
                break;
            case \Zend_Cache::CLEANING_MODE_OLD:
                $query = ['expire' => ['$ne' => self::EXPIRATION_TIME_INFINITE, '$lte' => time()]];
                break;
            case \Zend_Cache::CLEANING_MODE_MATCHING_TAG:
            case \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
            case \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                $query = $this->_getQueryMatchingTags((array)$tags, $mode);
                break;
            default:
                \Zend_Cache::throwException('Unsupported cleaning mode: ' . $mode);
        }
        if (!empty($query)) {
            $result = $this->_getCollection()->remove($query);
        }

        return $result;
    }

    /**
     * Quote specified value to be used in query as string
     *
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    protected function _quoteString($value)
    {
        return (string)$value;
    }
}
