<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\ResourceModel;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

/**
 * Search engine resource model
 * @since 2.1.0
 */
class Engine implements EngineInterface
{
    /**
     * Catalog product visibility
     *
     * @var Visibility
     * @since 2.1.0
     */
    protected $catalogProductVisibility;

    /**
     * @var IndexScopeResolver
     * @since 2.1.0
     */
    private $indexScopeResolver;

    /**
     * Construct
     *
     * @param Visibility $catalogProductVisibility
     * @param IndexScopeResolver $indexScopeResolver
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getAllowedVisibility()
    {
        return $this->catalogProductVisibility->getVisibleInSiteIds();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     * @since 2.1.0
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $index;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function isAvailable()
    {
        return true;
    }
}
