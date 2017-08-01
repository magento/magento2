<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tables declaration:
 *
 * CREATE TABLE IF NOT EXISTS `cache` (
 *      `id` VARCHAR(255) NOT NULL,
 *      `data` mediumblob,
 *      `create_time` int(11),
 *      `update_time` int(11),
 *      `expire_time` int(11),
 *      PRIMARY KEY  (`id`),
 *      KEY `IDX_EXPIRE_TIME` (`expire_time`)
 * )ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 * CREATE TABLE IF NOT EXISTS `cache_tag` (
 *      `tag` VARCHAR(255) NOT NULL,
 *      `cache_id` VARCHAR(255) NOT NULL,
 *      KEY `IDX_TAG` (`tag`),
 *      KEY `IDX_CACHE_ID` (`cache_id`),
 *      CONSTRAINT `FK_CORE_CACHE_TAG` FOREIGN KEY (`cache_id`)
 *      REFERENCES `cache` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 */

/**
 * Database cache backend
 */
namespace Magento\Framework\Cache\Backend;

/**
 * Class \Magento\Framework\Cache\Backend\Database
 *
 * @since 2.0.0
 */
class Database extends \Zend_Cache_Backend implements \Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Available options
     *
     * @var array available options
     * @since 2.0.0
     */
    protected $_options = [
        'adapter' => '',
        'adapter_callback' => '',
        'data_table' => '',
        'data_table_callback' => '',
        'tags_table' => '',
        'tags_table_callback' => '',
        'store_data' => true,
        'infinite_loop_flag' => false,
    ];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected $_connection = null;

    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @since 2.0.0
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
        if (empty($this->_options['adapter_callback'])) {
            if (!$this->_options['adapter'] instanceof \Magento\Framework\DB\Adapter\AdapterInterface) {
                \Zend_Cache::throwException(
                    'Option "adapter" should be declared and extend \Magento\Framework\DB\Adapter\AdapterInterface!'
                );
            }
        }
        if (empty($this->_options['data_table']) && empty($this->_options['data_table_callback'])) {
            \Zend_Cache::throwException('Option "data_table" or "data_table_callback" should be declared!');
        }
        if (empty($this->_options['tags_table']) && empty($this->_options['tags_table_callback'])) {
            \Zend_Cache::throwException('Option "tags_table" or "tags_table_callback" should be declared!');
        }
    }

    /**
     * Get DB adapter
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected function _getConnection()
    {
        if (!$this->_connection) {
            if (!empty($this->_options['adapter_callback'])) {
                $connection = call_user_func($this->_options['adapter_callback']);
            } else {
                $connection = $this->_options['adapter'];
            }
            if (!$connection instanceof \Magento\Framework\DB\Adapter\AdapterInterface) {
                \Zend_Cache::throwException(
                    'DB Adapter should be declared and extend \Magento\Framework\DB\Adapter\AdapterInterface'
                );
            } else {
                $this->_connection = $connection;
            }
        }
        return $this->_connection;
    }

    /**
     * Get table name where data is stored
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getDataTable()
    {
        if (empty($this->_options['data_table'])) {
            $this->setOption('data_table', call_user_func($this->_options['data_table_callback']));
            if (empty($this->_options['data_table'])) {
                \Zend_Cache::throwException('Failed to detect data_table option');
            }
        }
        return $this->_options['data_table'];
    }

    /**
     * Get table name where tags are stored
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getTagsTable()
    {
        if (empty($this->_options['tags_table'])) {
            $this->setOption('tags_table', call_user_func($this->_options['tags_table_callback']));
            if (empty($this->_options['tags_table'])) {
                \Zend_Cache::throwException('Failed to detect tags_table option');
            }
        }
        return $this->_options['tags_table'];
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     * @since 2.0.0
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        if ($this->_options['store_data'] && !$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $select = $this->_getConnection()->select()->from(
                $this->_getDataTable(),
                'data'
            )->where('id=:cache_id');

            if (!$doNotTestCacheValidity) {
                $select->where('expire_time=0 OR expire_time>?', time());
            }
            $result = $this->_getConnection()->fetchOne($select, ['cache_id' => $id]);
            $this->_options['infinite_loop_flag'] = false;
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     * @since 2.0.0
     */
    public function test($id)
    {
        if ($this->_options['store_data'] && !$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $select = $this->_getConnection()->select()->from(
                $this->_getDataTable(),
                'update_time'
            )->where(
                'id=:cache_id'
            )->where(
                'expire_time=0 OR expire_time>?',
                time()
            );
            $result = $this->_getConnection()->fetchOne($select, ['cache_id' => $id]);
            $this->_options['infinite_loop_flag'] = false;
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param string $data            Datas to cache
     * @param string $id              Cache id
     * @param string[] $tags          Array of strings, the cache record will be tagged by each string entry
     * @param int|bool $specificLifetime  Integer to set a specific lifetime or null for infinite lifetime
     * @return bool true if no problem
     * @since 2.0.0
     */
    public function save($data, $id, $tags = [], $specificLifetime = false)
    {
        $result = false;
        if (!$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $result = true;
            if ($this->_options['store_data']) {
                $connection = $this->_getConnection();
                $dataTable = $this->_getDataTable();

                $lifetime = $this->getLifetime($specificLifetime);
                $time = time();
                $expire = $lifetime === 0 || $lifetime === null ? 0 : $time + $lifetime;

                $dataCol = $connection->quoteIdentifier('data');
                $expireCol = $connection->quoteIdentifier('expire_time');
                $query = "INSERT INTO {$dataTable} (\n                    {$connection->quoteIdentifier(
                'id'
            )},\n                    {$dataCol},\n                    {$connection->quoteIdentifier(
                'create_time'
            )},\n                    {$connection->quoteIdentifier(
                'update_time'
            )},\n                    {$expireCol})\n                VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE\n
            {$dataCol}=VALUES({$dataCol}),\n                    {$expireCol}=VALUES({$expireCol})";

                $result = $connection->query($query, [$id, $data, $time, $time, $expire])->rowCount();
            }
            if ($result) {
                $result = $this->_saveTags($id, $tags);
            }
            $this->_options['infinite_loop_flag'] = false;
        }
        return $result;
    }

    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     * @since 2.0.0
     */
    public function remove($id)
    {
        if ($this->_options['store_data'] && !$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $result = $this->_getConnection()->delete($this->_getDataTable(), ['id=?' => $id]);
            $this->_options['infinite_loop_flag'] = false;
            return $result;
        }
        return false;
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
     * @return boolean true if no problem
     * @since 2.0.0
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, $tags = [])
    {
        if (!$this->_options['infinite_loop_flag']) {
            $this->_options['infinite_loop_flag'] = true;
            $connection = $this->_getConnection();
            switch ($mode) {
                case \Zend_Cache::CLEANING_MODE_ALL:
                    $result = $this->cleanAll($connection);
                    break;
                case \Zend_Cache::CLEANING_MODE_OLD:
                    $result = $this->cleanOld($connection);
                    break;
                case \Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                case \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                case \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                    $result = $this->_cleanByTags($mode, $tags);
                    break;
                default:
                    \Zend_Cache::throwException('Invalid mode for clean() method');
                    break;
            }
            $this->_options['infinite_loop_flag'] = false;
        }

        return $result;
    }

    /**
     * Return an array of stored cache ids
     *
     * @return string[] array of stored cache ids (string)
     * @since 2.0.0
     */
    public function getIds()
    {
        if ($this->_options['store_data']) {
            $select = $this->_getConnection()->select()->from($this->_getDataTable(), 'id');
            return $this->_getConnection()->fetchCol($select);
        } else {
            return [];
        }
    }

    /**
     * Return an array of stored tags
     *
     * @return string[] array of stored tags (string)
     * @since 2.0.0
     */
    public function getTags()
    {
        $select = $this->_getConnection()->select()->from($this->_getTagsTable(), 'tag')->distinct(true);
        return $this->_getConnection()->fetchCol($select);
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
        $select = $this->_getConnection()->select()->from(
            $this->_getTagsTable(),
            'cache_id'
        )->distinct(
            true
        )->where(
            'tag IN(?)',
            $tags
        )->group(
            'cache_id'
        )->having(
            'COUNT(cache_id)=' . count($tags)
        );
        return $this->_getConnection()->fetchCol($select);
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
        return array_diff($this->getIds(), $this->getIdsMatchingAnyTags($tags));
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
        $select = $this->_getConnection()->select()->from(
            $this->_getTagsTable(),
            'cache_id'
        )->distinct(
            true
        )->where(
            'tag IN(?)',
            $tags
        );
        return $this->_getConnection()->fetchCol($select);
    }

    /**
     * Return the filling percentage of the backend storage
     *
     * @return int integer between 0 and 100
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
     * @param string $id cache id
     * @return array|false array of metadatas (false if the cache id is not found)
     * @since 2.0.0
     */
    public function getMetadatas($id)
    {
        $select = $this->_getConnection()->select()->from($this->_getTagsTable(), 'tag')->where('cache_id=?', $id);
        $tags = $this->_getConnection()->fetchCol($select);

        $select = $this->_getConnection()->select()->from($this->_getDataTable())->where('id=?', $id);
        $data = $this->_getConnection()->fetchRow($select);
        $res = false;
        if ($data) {
            $res = ['expire' => $data['expire_time'], 'mtime' => $data['update_time'], 'tags' => $tags];
        }
        return $res;
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id cache id
     * @param int $extraLifetime
     * @return boolean true if ok
     * @since 2.0.0
     */
    public function touch($id, $extraLifetime)
    {
        if ($this->_options['store_data']) {
            return $this->_getConnection()->update(
                $this->_getDataTable(),
                ['expire_time' => new \Zend_Db_Expr('expire_time+' . $extraLifetime)],
                ['id=?' => $id, 'expire_time = 0 OR expire_time>' => time()]
            );
        } else {
            return true;
        }
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
     * Save tags related to specific id
     *
     * @param string $id
     * @param string[] $tags
     * @return bool
     * @since 2.0.0
     */
    protected function _saveTags($id, $tags)
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }
        if (empty($tags)) {
            return true;
        }

        $connection = $this->_getConnection();
        $tagsTable = $this->_getTagsTable();
        $select = $connection->select()->from($tagsTable, 'tag')->where('cache_id=?', $id)->where('tag IN(?)', $tags);

        $existingTags = $connection->fetchCol($select);
        $insertTags = array_diff($tags, $existingTags);
        if (!empty($insertTags)) {
            $query = 'INSERT IGNORE INTO ' . $tagsTable . ' (tag, cache_id) VALUES ';
            $bind = [];
            $lines = [];
            foreach ($insertTags as $tag) {
                $lines[] = '(?, ?)';
                $bind[] = $tag;
                $bind[] = $id;
            }
            $query .= implode(',', $lines);
            $connection->query($query, $bind);
        }
        $result = true;
        return $result;
    }

    /**
     * Remove cache data by tags with specified mode
     *
     * @param string $mode
     * @param string[] $tags
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    protected function _cleanByTags($mode, $tags)
    {
        if ($this->_options['store_data']) {
            $connection = $this->_getConnection();
            $select = $connection->select()->from($this->_getTagsTable(), 'cache_id');
            switch ($mode) {
                case \Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                    $select->where('tag IN (?)', $tags)->group('cache_id')->having('COUNT(cache_id)=' . count($tags));
                    break;
                case \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                    $select->where('tag NOT IN (?)', $tags);
                    break;
                case \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                    $select->where('tag IN (?)', $tags);
                    break;
                default:
                    \Zend_Cache::throwException('Invalid mode for _cleanByTags() method');
                    break;
            }

            $result = true;
            $ids = [];
            $counter = 0;
            $stmt = $connection->query($select);
            while ($row = $stmt->fetch()) {
                $ids[] = $row['cache_id'];
                $counter++;
                if ($counter > 100) {
                    $result = $result && $connection->delete($this->_getDataTable(), ['id IN (?)' => $ids]);
                    $ids = [];
                    $counter = 0;
                }
            }
            if (!empty($ids)) {
                $result = $result && $connection->delete($this->_getDataTable(), ['id IN (?)' => $ids]);
            }
            return $result;
        } else {
            return true;
        }
    }

    /**
     * Clean all cache entries
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return bool
     * @since 2.1.0
     */
    private function cleanAll(\Magento\Framework\DB\Adapter\AdapterInterface $connection)
    {
        if ($this->_options['store_data']) {
            $result = $connection->query('TRUNCATE TABLE ' . $this->_getDataTable());
        } else {
            $result = true;
        }
        $result = $result && $connection->query('TRUNCATE TABLE ' . $this->_getTagsTable());
        return $result;
    }

    /**
     * Clean old cache entries
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return bool
     * @since 2.1.0
     */
    private function cleanOld(\Magento\Framework\DB\Adapter\AdapterInterface $connection)
    {
        if ($this->_options['store_data']) {
            $result = $connection->delete(
                $this->_getDataTable(),
                ['expire_time> ?' => 0, 'expire_time<= ?' => time()]
            );
            return $result;
        } else {
            $result = true;
            return $result;
        }
    }
}
