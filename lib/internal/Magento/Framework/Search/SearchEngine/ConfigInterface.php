<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\SearchEngine;

/**
 * Interface \Magento\Framework\Search\SearchEngine\ConfigInterface
 *
 * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getDeclaredFeatures($searchEngine);

    /**
     * Checks if a particular search feature is supported
     *
     * @param string $featureName
     * @param string $searchEngine
     * @return bool
     * @since 2.1.0
     */
    public function isFeatureSupported($featureName, $searchEngine);
}
