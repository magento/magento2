<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Backend;

class Memcached extends \Zend_Cache_Backend_Memcached implements \Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Maximum chunk of data that could be saved in one memcache cell (1 MiB)
     */
    const DEFAULT_SLAB_SIZE = 1048576;

    /**
     * Used to tell chunked data from ordinary
     */
    const CODE_WORD = '{splitted}';

    /**
     * Constructor
     *
     * @param array $options @see \Zend_Cache_Backend_Memcached::__construct()
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (!isset($options['slab_size']) || !is_numeric($options['slab_size'])) {
            if (isset($options['slab_size'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        "Invalid value for the node <slab_size>. Expected to be positive integer."
                    )
                );
            }

            $this->_options['slab_size'] = self::DEFAULT_SLAB_SIZE;
        } else {
            $this->_options['slab_size'] = $options['slab_size'];
        }
    }

    /**
     * Returns ID of a specific chunk on the basis of data's ID
     *
     * @param string $id    Main data's ID
     * @param int    $index Particular chunk number to return ID for
     * @return string
     */
    protected function _getChunkId($id, $index)
    {
        return "{$id}[{$index}]";
    }

    /**
     * Remove saved chunks in case something gone wrong (e.g. some chunk from the chain can not be found)
     *
     * @param string $id     ID of data's info cell
     * @param int    $chunks Number of chunks to remove (basically, the number after '{splitted}|')
     * @return null
     */
    protected function _cleanTheMess($id, $chunks)
    {
        for ($i = 0; $i < $chunks; $i++) {
            $this->remove($this->_getChunkId($id, $i));
        }

        $this->remove($id);
    }

    /**
     * Save data to memcached, split it into chunks if data size is bigger than memcached slab size.
     *
     * @param string $data             @see \Zend_Cache_Backend_Memcached::save()
     * @param string $id               @see \Zend_Cache_Backend_Memcached::save()
     * @param string[] $tags           @see \Zend_Cache_Backend_Memcached::save()
     * @param bool $specificLifetime   @see \Zend_Cache_Backend_Memcached::save()
     * @return bool
     */
    public function save($data, $id, $tags = [], $specificLifetime = false)
    {
        if (is_string($data) && strlen($data) > $this->_options['slab_size']) {
            $dataChunks = str_split($data, $this->_options['slab_size']);

            $dataChunksCount = count($dataChunks);
            for ($i = 0, $cnt = $dataChunksCount; $i < $cnt; $i++) {
                $chunkId = $this->_getChunkId($id, $i);

                if (!parent::save($dataChunks[$i], $chunkId, $tags, $specificLifetime)) {
                    $this->_cleanTheMess($id, $i + 1);
                    return false;
                }
            }

            $data = self::CODE_WORD . '|' . $i;
        }

        return parent::save($data, $id, $tags, $specificLifetime);
    }

    /**
     * Load data from memcached, glue from several chunks if it was splitted upon save.
     *
     * @param string $id                     @see \Zend_Cache_Backend_Memcached::load()
     * @param bool   $doNotTestCacheValidity @see \Zend_Cache_Backend_Memcached::load()
     * @return bool|false|string
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $data = parent::load($id, $doNotTestCacheValidity);

        if (is_string($data) && substr($data, 0, strlen(self::CODE_WORD)) == self::CODE_WORD) {
            // Seems we've got chunked data

            $arr = explode('|', $data);
            $chunks = isset($arr[1]) ? $arr[1] : false;
            $chunkData = [];

            if ($chunks && is_numeric($chunks)) {
                for ($i = 0; $i < $chunks; $i++) {
                    $chunk = parent::load($this->_getChunkId($id, $i), $doNotTestCacheValidity);

                    if (false === $chunk) {
                        // Some chunk in chain was not found, we can not glue-up the data:
                        // clean the mess and return nothing

                        $this->_cleanTheMess($id, $chunks);
                        return false;
                    }

                    $chunkData[] = $chunk;
                }

                return implode('', $chunkData);
            }
        }

        // Data has not been splitted to chunks on save
        return $data;
    }
}
