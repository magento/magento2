<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer;

use Magento\Framework\Search\Request\IndexScopeResolverInterface;

/**
 * @inheritdoc
 */
class IndexNameResolver implements IndexNameResolverInterface
{
    /**
     * TODO: move to separate configurable interface
     * Suffix for replica index table
     *
     * @var string
     */
    private $additionalTableSuffix = '_replica';

    /**
     * @var IndexScopeResolverInterface
     */
    private $indexScopeResolver;

    /**
     * @param IndexScopeResolverInterface $indexScopeResolver
     */
    public function __construct(
        IndexScopeResolverInterface $indexScopeResolver
    ) {
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * @inheritdoc
     */
    public function resolveName(IndexName $indexName): string
    {
        $tableName = $this->indexScopeResolver->resolve($indexName->getIndexId(), $indexName->getDimensions());

        if ($indexName->getAlias()->getValue() === Alias::ALIAS_REPLICA) {
            $tableName = $this->getAdditionalTableName($tableName);
        }
        return $tableName;
    }

    /**
     * TODO: move to separate configurable interface
     */
    public function getAdditionalTableName(string $tableName): string
    {
        return $tableName . $this->additionalTableSuffix;
    }
}
