<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Tax;

use Magento\Tax\Helper\Data as TaxData;

/**
 * GraphQL-only tax helper substitute that avoids session-backed helper dependencies.
 */
class GraphQlTaxData extends TaxData
{
    /**
     * Intentionally skip the parent helper dependency graph.
     */
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    public function __construct()
    {
    }
    // phpcs:enable Magento2.CodeAnalysis.EmptyBlock

    /**
     * @inheritDoc
     */
    public function getPostCodeSubStringLength()
    {
        return 10;
    }
}
