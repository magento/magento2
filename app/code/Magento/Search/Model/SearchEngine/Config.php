<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine;

/**
 * Class \Magento\Search\Model\SearchEngine\Config
 *
 * @since 2.1.0
 */
class Config implements \Magento\Framework\Search\SearchEngine\ConfigInterface
{
    /**
     * Search engine config data storage
     *
     * @var Config\Data
     * @since 2.1.0
     */
    protected $dataStorage;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     * @since 2.1.0
     */
    public function __construct(\Magento\Framework\Config\DataInterface $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getDeclaredFeatures($searchEngine)
    {
        return $this->dataStorage->get($searchEngine, []);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function isFeatureSupported($featureName, $searchEngine)
    {
        $features = $this->getDeclaredFeatures($searchEngine);
        return in_array(strtolower($featureName), $features);
    }
}
