<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Model\Search;

use Magento\Search\Model\EngineResolver;

/**
 * Returns max  page size by search engine name
 * @api
 * @since 101.0.0
 */
class PageSizeProvider
{
    /**
     * @param EngineResolver $engineResolver
     * @param array $pageSizeBySearchEngine
     */
    public function __construct(
        private readonly EngineResolver $engineResolver,
        private readonly array $pageSizeBySearchEngine = []
    ) {
    }

    /**
     * Returns max_page_size depends on engine
     *
     * @return integer
     * @since 101.0.0
     */
    public function getMaxPageSize() : int
    {
        $searchEngine = $this->engineResolver->getCurrentSearchEngine();

        $pageSize = PHP_INT_MAX;
        if (isset($this->pageSizeBySearchEngine[$searchEngine])) {
            $pageSize = $this->pageSizeBySearchEngine[$searchEngine];
        }

        return (int)$pageSize;
    }
}
