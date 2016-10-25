<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Config;

use Magento\Framework\Serialize\SerializerInterface;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection
     */
    protected $stateCollection;

    /**
     * @param \Magento\Framework\Indexer\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection $stateCollection
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Indexer\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection $stateCollection,
        $cacheId = 'indexer_config',
        SerializerInterface $serializer = null
    ) {
        $this->stateCollection = $stateCollection;

        $isCacheExists = $cache->test($cacheId);

        parent::__construct($reader, $cache, $cacheId, $serializer);

        if (!$isCacheExists) {
            $this->deleteNonexistentStates();
        }
    }

    /**
     * Delete all states that are not in configuration
     *
     * @return void
     */
    protected function deleteNonexistentStates()
    {
        foreach ($this->stateCollection->getItems() as $state) {
            /** @var \Magento\Indexer\Model\Indexer\State $state */
            if (!isset($this->_data[$state->getIndexerId()])) {
                $state->delete();
            }
        }
    }
}
