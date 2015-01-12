<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Resource;

use Magento\CatalogSearch\Model\Resource\Product\CollectionFactory;
use Magento\Framework\Model\Resource\Db\AbstractDb;

/**
 * CatalogSearch Fulltext Index Engine resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Engine extends AbstractDb implements EngineInterface
{
    const ATTRIBUTE_PREFIX = 'attr_';

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product Collection Factory
     *
     * @var \Magento\CatalogSearch\Model\Resource\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Array of product collection factory names
     *
     * @var array
     */
    protected $productFactoryNames;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\Advanced
     */
    protected $_searchResource;

    /**
     * @var \Magento\CatalogSearch\Model\Resource\Advanced\Collection
     */
    protected $_searchResourceCollection;

    /**
     * Catalog search data
     *
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $_catalogSearchData = null;

    /**
     * Catalog search data
     *
     * @var \Magento\Search\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\CatalogSearch\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\CatalogSearch\Model\Resource\Advanced $searchResource
     * @param \Magento\CatalogSearch\Model\Resource\Advanced\Collection $searchResourceCollection
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param \Magento\Search\Model\Resource\Helper $resourceHelper
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\CatalogSearch\Model\Resource\Advanced $searchResource,
        \Magento\CatalogSearch\Model\Resource\Advanced\Collection $searchResourceCollection,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        \Magento\Search\Model\Resource\Helper $resourceHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_searchResource = $searchResource;
        $this->_searchResourceCollection = $searchResourceCollection;
        $this->_catalogSearchData = $catalogSearchData;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($resource);
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
     * Add entity data to fulltext search table
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     * @param string $entity 'product'|'cms'
     * @return $this
     */
    public function saveEntityIndex($entityId, $storeId, $index, $entity = 'product')
    {
        $this->_getWriteAdapter()
            ->insert(
                $this->getMainTable(),
                ['product_id' => $entityId, 'store_id' => $storeId, 'data_index' => $index]
            );

        return $this;
    }

    /**
     * Multi add entities data to fulltext search table
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @param string $entity 'product'|'cms'
     * @return $this
     */
    public function saveEntityIndexes($storeId, $entityIndexes, $entity = 'product')
    {
        $data = [];
        $storeId = (int)$storeId;
        foreach ($entityIndexes as $entityId => $index) {
            $data[] = ['product_id' => (int)$entityId, 'store_id' => $storeId, 'data_index' => $index];
        }

        if ($data) {
            $this->_resourceHelper->insertOnDuplicate($this->getMainTable(), $data, ['data_index']);
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
     * Remove entity data from fulltext search table
     *
     * @param int $storeId
     * @param int $entityId
     * @param string $entity 'product'|'cms'
     * @return $this
     */
    public function cleanIndex($storeId = null, $entityId = null, $entity = 'product')
    {
        $where = [];

        if (!is_null($storeId)) {
            $where[] = $this->_getWriteAdapter()
                ->quoteInto('store_id=?', $storeId);
        }
        if (!is_null($entityId)) {
            $where[] = $this->_getWriteAdapter()
                ->quoteInto('product_id IN (?)', $entityId);
        }

        $this->_getWriteAdapter()
            ->delete($this->getMainTable(), $where);

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
     * Return resource model for the full text search
     *
     * @return \Magento\CatalogSearch\Model\Resource\Advanced
     */
    public function getResource()
    {
        return $this->_searchResource;
    }

    /**
     * Return resource collection model for the full text search
     *
     * @return \Magento\CatalogSearch\Model\Resource\Advanced\Collection
     */
    public function getResourceCollection()
    {
        return $this->_searchResourceCollection;
    }

    /**
     * Retrieve fulltext search result data collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getResultCollection()
    {
        return $this->productCollectionFactory->create(
            \Magento\CatalogSearch\Model\Resource\Product\CollectionFactory::PRODUCT_COLLECTION_FULLTEXT
        );
    }

    /**
     * Retrieve advanced search result data collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getAdvancedResultCollection()
    {
        return $this->productCollectionFactory->create(
            \Magento\CatalogSearch\Model\Resource\Product\CollectionFactory::PRODUCT_COLLECTION_ADVANCED
        );
    }

    /**
     * Define if Layered Navigation is allowed
     *
     * @return bool
     */
    public function isLayeredNavigationAllowed()
    {
        return true;
    }

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function test()
    {
        return true;
    }
}
