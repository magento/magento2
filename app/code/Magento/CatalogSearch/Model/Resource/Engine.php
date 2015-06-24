<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Resource\Db\AbstractDb;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\Store;

/**
 * CatalogSearch Fulltext Index Engine resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Engine extends AbstractDb implements EngineInterface
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
    protected $_catalogProductVisibility;

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
    protected $_catalogSearchData;
    /**
     * @var \Magento\Search\Model\IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param \Magento\Search\Model\IndexScopeResolver $indexScopeResolver
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        \Magento\Search\Model\IndexScopeResolver $indexScopeResolver,
        $resourcePrefix = null
    ) {
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogSearchData = $catalogSearchData;
        $this->indexScopeResolver = $indexScopeResolver;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogsearch_fulltext', 'product_id');
    }

    /**
     * @inheritdoc
     */
    public function saveIndex(Dimension $dimension, \Traversable $documents)
    {
        $data = [];
        $storeId = $dimension->getName() == self::SCOPE_FIELD_NAME ? $dimension->getValue() : Store::DEFAULT_STORE_ID;
        foreach ($documents as $entityId => $productAttributes) {
            foreach ($productAttributes as $attributeId => $indexValue) {
                $data[] = [
                    'product_id' => (int)$entityId,
                    'attribute_id' =>(int)$attributeId,
                    'store_id' => (int)$storeId,
                    'data_index' => $indexValue
                ];
            }
        }

        if ($data) {
            $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable($storeId), $data, ['data_index']);
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
        return $this->_catalogProductVisibility->getVisibleInSiteIds();
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
     * @param int|null $storeId
     * @return string
     * @throws LocalizedException
     */
    public function getMainTable($storeId = null)
    {
        if (empty($this->_mainTable)) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Empty main table name'));
        }

        return $this->indexScopeResolver->resolve($this->_mainTable, $storeId);
    }


    /**
     * @inheritdoc
     */
    public function deleteIndex(Dimension $dimension, \Traversable $documents)
    {
        $where = [];
        $entityIds = iterator_to_array($documents);
        if ($entityIds !== null) {
            $where[] = $this->_getWriteAdapter()
                ->quoteInto('product_id IN (?)', $entityIds);
        }

        $storeId = $dimension->getName() == self::SCOPE_FIELD_NAME ? $dimension->getValue() : Store::DEFAULT_STORE_ID;
        $this->_getWriteAdapter()
            ->delete($this->getMainTable($storeId), $where);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cleanIndex(Dimension $dimension)
    {
        $storeId = $dimension->getName() == self::SCOPE_FIELD_NAME ? $dimension->getValue() : Store::DEFAULT_STORE_ID;
        $this->_getWriteAdapter()->delete($this->getMainTable($storeId));
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
        return $this->_catalogSearchData->prepareIndexdata($index, $separator);
    }

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return true;
    }
}
