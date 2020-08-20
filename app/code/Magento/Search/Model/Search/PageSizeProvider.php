<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Model\Search;

/**
 * Returns max  page size by search engine name
 * @api
 * @since 101.0.0
 */
class PageSizeProvider
{
    /**
     * @var \Magento\Search\Model\EngineResolver
     */
    private $engineResolver;

    /**
     * @var array
     */
    private $pageSizeBySearchEngine;

    /**
     * @param \Magento\Search\Model\EngineResolver $engineResolver
     * @param array $pageSizeBySearchEngine
     */
    public function __construct(
        \Magento\Search\Model\EngineResolver $engineResolver,
        array $pageSizeBySearchEngine = []
    ) {
        $this->engineResolver = $engineResolver;
        $this->pageSizeBySearchEngine = $pageSizeBySearchEngine;
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
