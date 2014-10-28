<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
