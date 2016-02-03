<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\SearchEngine;

interface ConfigInterface
{
    /**
     * Get declared features of a search engine
     *
     * @param string $searchEngine
     * @return string[]
     */
    public function getDeclaredFeatures($searchEngine);
}
