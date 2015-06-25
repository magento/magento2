<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Search\Request\Dimension;

/**
 * CatalogSearch Fulltext Index Engine resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Engine implements EngineInterface
{
    const ATTRIBUTE_PREFIX = 'attr_';

    /**
     * Scope identifier
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * Array of product collection factory names
     *
     * @var array
     */
    protected $productFactoryNames;

    /**
     * Catalog search data
     *
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $catalogSearchData;

    /**
     * @var \Magento\Search\Model\IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param \Magento\Search\Model\ScopeResolver\IndexScopeResolver $indexScopeResolver
     * @param \Magento\Framework\App\Resource $resource
     * @param string $tableName
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        \Magento\Search\Model\ScopeResolver\IndexScopeResolver $indexScopeResolver,
        \Magento\Framework\App\Resource $resource,
        $tableName = 'catalogsearch_fulltext'
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->catalogSearchData = $catalogSearchData;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->connection = $resource->getConnection(\Magento\Framework\App\Resource::DEFAULT_WRITE_RESOURCE);
        $this->tableName = $resource->getTableName($tableName);
    }

    /**
     * @inheritdoc
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $data = [];
        foreach ($documents as $entityId => $productAttributes) {
            foreach ($productAttributes as $attributeId => $indexValue) {
                $data[] = [
                    'product_id' => (int)$entityId,
                    'attribute_id' =>(int)$attributeId,
                    'data_index' => $indexValue
                ];
            }
        }

        if ($data) {
            $this->connection->insertOnDuplicate($this->resolveTableName($dimensions), $data, ['data_index']);
        }

        return $this;
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
     * Is Attribute Filterable as Term
     *
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @return bool
     */
    private function isTermFilterableAttribute($attribute)
    {
        return ($attribute->getIsVisibleInAdvancedSearch()
            || $attribute->getIsFilterable()
            || $attribute->getIsFilterableInSearch())
        && in_array($attribute->getFrontendInput(), ['select', 'multiselect']);
    }

    /**
     * @inheritdoc
     */
    public function processAttributeValue($attribute, $value)
    {
        if ($attribute->getIsSearchable()
            && in_array($attribute->getFrontendInput(), ['text', 'textarea'])
        ) {
            return $value;
        } elseif ($this->isTermFilterableAttribute($attribute)
            || in_array($attribute->getAttributeCode(), ['visibility', 'status'])
        ) {
            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(',', $value);
            }
            if (!is_array($value)) {
                $value = [$value];
            }
            $valueMapper = function ($value) use ($attribute) {
                return Engine::ATTRIBUTE_PREFIX . $attribute->getAttributeCode() . '_' . $value;
            };

            return implode(' ', array_map($valueMapper, $value));
        }
    }

    /**
     * @param Dimension[] $dimensions
     * @return string
     * @throws LocalizedException
     */
    private function resolveTableName($dimensions)
    {
        if (empty($this->tableName)) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Empty main table name'));
        }

        return $this->indexScopeResolver->resolve($this->tableName, $dimensions);
    }


    /**
     * @inheritdoc
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $where = [];
        $entityIds = iterator_to_array($documents);
        if ($entityIds !== null) {
            $where[] = $this->connection
                ->quoteInto('product_id IN (?)', $entityIds);
        }

        $this->connection
            ->delete($this->resolveTableName($dimensions), $where);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex($dimensions)
    {
        $this->connection->delete($this->resolveTableName($dimensions));
        return $this;
    }

    /**
     * Prepare index array as a string glued by separator
     *
     * @param array $index
     * @param string $separator
     * @return string
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $this->catalogSearchData->prepareIndexdata($index, $separator);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return true;
    }
}
