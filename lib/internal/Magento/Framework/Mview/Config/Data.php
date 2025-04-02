<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides materialized view configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * @var \Magento\Framework\Mview\View\State\CollectionInterface
     */
    protected $stateCollection;

    /**
     * Constructor
     *
     * @param Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\Mview\View\State\CollectionInterface $stateCollection
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\Mview\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\Mview\View\State\CollectionInterface $stateCollection,
        $cacheId = 'mview_config',
        ?SerializerInterface $serializer = null
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
            /** @var \Magento\Framework\Mview\View\StateInterface $state */
            if (!isset($this->_data[$state->getViewId()])) {
                $state->delete();
            }
        }
    }
}
