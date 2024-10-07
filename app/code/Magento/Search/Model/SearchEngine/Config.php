<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\Search\SearchEngine\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * Constructor
     *
     * @param Config\Data $dataStorage Search engine config data storage
     */
    public function __construct(
        protected readonly DataInterface $dataStorage
    ) {
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
