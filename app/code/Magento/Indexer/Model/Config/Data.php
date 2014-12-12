<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Indexer\Model\Config;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * @var \Magento\Indexer\Model\Resource\Indexer\State\Collection
     */
    protected $stateCollection;

    /**
     * @param \Magento\Indexer\Model\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Indexer\Model\Resource\Indexer\State\Collection $stateCollection
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Indexer\Model\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Indexer\Model\Resource\Indexer\State\Collection $stateCollection,
        $cacheId = 'indexer_config'
    ) {
        $this->stateCollection = $stateCollection;

        $isCacheExists = $cache->test($cacheId);

        parent::__construct($reader, $cache, $cacheId);

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
