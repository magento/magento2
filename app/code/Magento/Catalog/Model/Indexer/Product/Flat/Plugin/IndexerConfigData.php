<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\State as ProductFlatIndexerState;
use Magento\Indexer\Model\Config\Data as ConfigData;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor as ProductFlatIndexerProcessor;

/**
 * Plugin for Magento\Indexer\Model\Config\Data
 */
class IndexerConfigData
{
    /**
     * @var ProductFlatIndexerState
     */
    protected $state;

    /**
     * @param ProductFlatIndexerState $state
     */
    public function __construct(ProductFlatIndexerState $state)
    {
        $this->state = $state;
    }

    /**
     * Modify returned config when flat indexer is disabled
     *
     * @param ConfigData $subject
     * @param mixed $data
     * @param string $path
     * @param string $default
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function afterGet(ConfigData $subject, $data, $path = null, $default = null)
    {
        if ($this->state->isFlatEnabled()) {
            return $data;
        }

        $indexerId = ProductFlatIndexerProcessor::INDEXER_ID;

        if (!$path && isset($data[$indexerId])) {
            unset($data[$indexerId]);

            return $data;
        }

        if (!$path) {
            return $data;
        }

        list($firstKey) = explode('/', $path);

        if ($firstKey == $indexerId) {
            $data = $default;
        }

        return $data;
    }
}
