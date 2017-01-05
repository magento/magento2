<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

/**
 * Search engine resource model
 */
class Engine implements EngineInterface
{
    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * Construct
     *
     * @param Visibility $catalogProductVisibility
     * @param IndexScopeResolver $indexScopeResolver
     */
    public function __construct(
        Visibility $catalogProductVisibility,
        IndexScopeResolver $indexScopeResolver
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->indexScopeResolver = $indexScopeResolver;
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return int[]
     */
    public function getAllowedVisibility()
    {
        return $this->catalogProductVisibility->getVisibleInSiteIds();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }

    /**
     * Prepare index array as a string glued by separator
     * Support 2 level array gluing
     *
     * @param array $index
     * @param string $separator
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $index;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return true;
    }
}
