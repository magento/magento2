<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\ConfigInterface;

/**
 * Class \Magento\Indexer\Model\Config
 *
 * @since 2.0.0
 */
class Config implements ConfigInterface
{
    /**
     * @var Config\Data
     * @since 2.0.0
     */
    protected $configData;

    /**
     * @param Config\Data $configData
     * @since 2.0.0
     */
    public function __construct(Config\Data $configData)
    {
        $this->configData = $configData;
    }

    /**
     * Get indexers list
     *
     * @return array[]
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getIndexer($indexerId)
    {
        return $this->configData->get($indexerId);
    }
}
