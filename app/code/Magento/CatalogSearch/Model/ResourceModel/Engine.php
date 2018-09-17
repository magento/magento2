<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\ResourceModel;

/**
 * CatalogSearch Fulltext Index Engine resource model
 *
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class Engine implements EngineInterface
{
    /**
     * @deprecated
     * @see EngineInterface::FIELD_PREFIX
     */
    const ATTRIBUTE_PREFIX = 'attr_';

    /**
     * Scope identifier
     *
     * @deprecated
     * @see EngineInterface::SCOPE_IDENTIFIER
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
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
        return true;
    }

    /**
     * Is attribute filterable as term cache
     *
     * @var array
     */
    private $termFilterableAttributeAttributeCache = [];

    /**
     * Is Attribute Filterable as Term
     *
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @return bool
     */
    private function isTermFilterableAttribute($attribute)
    {
        $attributeId = $attribute->getAttributeId();
        if (!isset($this->termFilterableAttributeAttributeCache[$attributeId])) {
            $this->termFilterableAttributeAttributeCache[$attributeId] =
                in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)
                && ($attribute->getIsVisibleInAdvancedSearch()
                    || $attribute->getIsFilterable()
                    || $attribute->getIsFilterableInSearch());
        }

        return $this->termFilterableAttributeAttributeCache[$attributeId];
    }

    /**
     * @inheritdoc
     */
    public function processAttributeValue($attribute, $value)
    {
        $result = false;
        if ($attribute->getIsSearchable()
            && in_array($attribute->getFrontendInput(), ['text', 'textarea'])
        ) {
            $result = $value;
        } elseif ($this->isTermFilterableAttribute($attribute)) {
            $result = '';
        }

        return $result;
    }

    /**
     * Prepare index array as a string glued by separator
     * Support 2 level array gluing
     *
     * @param array $index
     * @param string $separator
     * @return array
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        $indexData = [];
        foreach ($index as $attributeId => $value) {
            $indexData[$attributeId] = is_array($value) ? implode($separator, $value) : $value;
        }
        return $indexData;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return true;
    }
}
