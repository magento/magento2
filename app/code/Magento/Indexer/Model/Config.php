<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * @var Config\Data
     */
    protected $configData;

    /**
     * @param Config\Data $configData
     */
    public function __construct(Config\Data $configData)
    {
        $this->configData = $configData;
    }

    /**
     * Get indexers list
     *
     * @return array[]
     */
    public function getIndexers()
    {
        return $this->configData->get();
    }

    /**
     * Get indexer by ID
     *
     * @param string $indexerId
     * @return array
     */
    public function getIndexer($indexerId)
    {
        return $this->configData->get($indexerId);
    }
}
