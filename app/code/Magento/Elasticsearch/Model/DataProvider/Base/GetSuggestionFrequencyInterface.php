<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
