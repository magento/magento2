<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products;

use Magento\Framework\ObjectManagerInterface;

/**
 * Generate SearchResult based off of total count from query and array of products and their data.
 */
class SearchResultFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Instantiate SearchResult
     *
     * @param array $data
     * @return SearchResult
     */
    public function create(
        array $data
    ): SearchResult {
        return $this->objectManager->create(
            SearchResult::class,
            ['data' => $data]
        );
    }
}
