<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\DataProvider\Base;

/**
 * Get the search suggestion result count
 */
interface GetSuggestionFrequencyInterface
{
    /**
     * Get the search suggestion frequency
     *
     * @param string $text
     * @return int
     */
    public function execute(string $text): int;
}
