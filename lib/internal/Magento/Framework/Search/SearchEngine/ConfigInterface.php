<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\SearchEngine;

/**
 * Interface \Magento\Framework\Search\SearchEngine\ConfigInterface
 *
 */
interface ConfigInterface
{
    /**
     * Search engine feature: synonyms
     */
    const SEARCH_ENGINE_FEATURE_SYNONYMS = 'synonyms';

    /**
     * Get declared features of a search engine
     *
     * @param string $searchEngine
     * @return string[]
     */
    public function getDeclaredFeatures($searchEngine);

    /**
     * Checks if a particular search feature is supported
     *
     * @param string $featureName
     * @param string $searchEngine
     * @return bool
     */
    public function isFeatureSupported($featureName, $searchEngine);
}
