<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine;

class Config implements \Magento\Framework\Search\SearchEngine\ConfigInterface
{
    /**
     * Search engine config data storage
     *
     * @var Config\Data
     */
    protected $dataStorage;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     */
    public function __construct(\Magento\Framework\Config\DataInterface $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeclaredFeatures($searchEngine)
    {
        return $this->dataStorage->get($searchEngine, []);
    }

    /**
     * {@inheritdoc}
     */
    public function isFeatureSupported($featureName, $searchEngine)
    {
        $features = $this->getDeclaredFeatures($searchEngine);
        return in_array(strtolower($featureName), $features);
    }
}
