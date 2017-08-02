<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Config;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Provides indexer configuration
 * @since 2.0.0
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection
     * @since 2.0.0
     */
    protected $stateCollection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Indexer\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection $stateCollection
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     * @since 2.0.0
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
     * @since 2.0.0
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
