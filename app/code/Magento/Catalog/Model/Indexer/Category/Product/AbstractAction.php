<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Category\Product;

abstract class AbstractAction
{
    /**
     * Chunk size
     */
    const RANGE_CATEGORY_STEP = 500;

    /**
     * Chunk size for product
     */
    const RANGE_PRODUCT_STEP = 1000000;

    /**
     * Catalog category index table name
     */
    const MAIN_INDEX_TABLE = 'catalog_category_product_index';

    /**
     * Suffix for table to show it is temporary
     */
    const TEMPORARY_TABLE_SUFFIX = '_tmp';

    /**
     * Cached non anchor categories select by store id
     *
     * @var \Magento\Framework\DB\Select[]
     */
    protected $nonAnchorSelects = array();

    /**
     * Cached anchor categories select by store id
     *
     * @var \Magento\Framework\DB\Select[]
     */
    protected $anchorSelects = array();

    /**
     * Cached all product select by store id
     *
     * @var \Magento\Framework\DB\Select[]
     */
    protected $productsSelects = array();

    /**
     * Category path by id
     *
     * @var string[]
     */
    protected $categoryPath = array();

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $config;

    /**
     * Whether to use main or temporary index table
     *
     * @var bool
     */
    protected $useTempTable = true;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $config
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Run full reindex
     *
     * @return $this
     */
    abstract public function execute();

    /**
     * Run reindexation
     *
     * @return void
     */
    protected function reindex()
    {
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->getPathFromCategoryId($store->getRootCategoryId())) {
                $this->reindexRootCategory($store);
                $this->reindexAnchorCategories($store);
                $this->reindexNonAnchorCategories($store);
            }
        }
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * Return main index table name
     *
     * @return string
     */
    protected function getMainTable()
    {
        return $this->getTable(self::MAIN_INDEX_TABLE);
    }

    /**
     * Return temporary index table name
     *
     * @return string
     */
    protected function getMainTmpTable()
    {
        return $this->useTempTable ? $this->getTable(
            self::MAIN_INDEX_TABLE . self::TEMPORARY_TABLE_SUFFIX
        ) : $this->getMainTable();
    }

    /**
     * Retrieve connection for read data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getReadAdapter()
    {
        $writeAdapter = $this->getWriteAdapter();
        if ($writeAdapter && $writeAdapter->getTransactionLevel() > 0) {
            // if transaction is started we should use write connection for reading
            return $writeAdapter;
        }
        return $this->resource->getConnection('read');
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getWriteAdapter()
    {
        return $this->resource->getConnection('write');
    }

    /**
     * Return category path by id
     *
     * @param int $categoryId
     * @return string
     */
    protected function getPathFromCategoryId($categoryId)
    {
        if (!isset($this->categoryPath[$categoryId])) {
            $this->categoryPath[$categoryId] = $this->getReadAdapter()->fetchOne(
                $this->getReadAdapter()->select()->from(
                    $this->getTable('catalog_category_entity'),
                    array('path')
                )->where(
                    'entity_id = ?',
                    $categoryId
                )
            );
        }
        return $this->categoryPath[$categoryId];
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getNonAnchorCategoriesSelect(\Magento\Store\Model\Store $store)
    {
        if (!isset($this->nonAnchorSelects[$store->getId()])) {
            $statusAttributeId = $this->config->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'status'
            )->getId();
            $visibilityAttributeId = $this->config->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'visibility'
            )->getId();

            $rootPath = $this->getPathFromCategoryId($store->getRootCategoryId());

            $select = $this->getWriteAdapter()->select()->from(
                array('cc' => $this->getTable('catalog_category_entity')),
                array()
            )->joinInner(
                array('ccp' => $this->getTable('catalog_category_product')),
                'ccp.category_id = cc.entity_id',
                array()
            )->joinInner(
                array('cpw' => $this->getTable('catalog_product_website')),
                'cpw.product_id = ccp.product_id',
                array()
            )->joinInner(
                array('cpsd' => $this->getTable('catalog_product_entity_int')),
                'cpsd.entity_id = ccp.product_id AND cpsd.store_id = 0' .
                ' AND cpsd.attribute_id = ' .
                $statusAttributeId,
                array()
            )->joinLeft(
                array('cpss' => $this->getTable('catalog_product_entity_int')),
                'cpss.entity_id = ccp.product_id AND cpss.attribute_id = cpsd.attribute_id' .
                ' AND cpss.store_id = ' .
                $store->getId(),
                array()
            )->joinInner(
                array('cpvd' => $this->getTable('catalog_product_entity_int')),
                'cpvd.entity_id = ccp.product_id AND cpvd.store_id = 0' .
                ' AND cpvd.attribute_id = ' .
                $visibilityAttributeId,
                array()
            )->joinLeft(
                array('cpvs' => $this->getTable('catalog_product_entity_int')),
                'cpvs.entity_id = ccp.product_id AND cpvs.attribute_id = cpvd.attribute_id' .
                ' AND cpvs.store_id = ' .
                $store->getId(),
                array()
            )->where(
                'cc.path LIKE ' . $this->getWriteAdapter()->quote($rootPath . '/%')
            )->where(
                'cpw.website_id = ?',
                $store->getWebsiteId()
            )->where(
                $this->getWriteAdapter()->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            )->where(
                $this->getWriteAdapter()->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
                array(
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
                )
            )->columns(
                array(
                    'category_id' => 'cc.entity_id',
                    'product_id' => 'ccp.product_id',
                    'position' => 'ccp.position',
                    'is_parent' => new \Zend_Db_Expr('1'),
                    'store_id' => new \Zend_Db_Expr($store->getId()),
                    'visibility' => new \Zend_Db_Expr(
                        $this->getWriteAdapter()->getIfNullSql('cpvs.value', 'cpvd.value')
                    )
                )
            );

            $this->nonAnchorSelects[$store->getId()] = $select;
        }

        return $this->nonAnchorSelects[$store->getId()];
    }

    /**
     * Check whether select ranging is needed
     *
     * @return bool
     */
    protected function isRangingNeeded()
    {
        return true;
    }

    /**
     * Return selects cut by min and max
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $field
     * @param int $range
     * @return \Magento\Framework\DB\Select[]
     */
    protected function prepareSelectsByRange(\Magento\Framework\DB\Select $select, $field, $range = self::RANGE_CATEGORY_STEP)
    {
        return $this->isRangingNeeded() ? $this->getWriteAdapter()->selectsByRange(
            $field,
            $select,
            $range
        ) : array(
            $select
        );
    }

    /**
     * Reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    protected function reindexNonAnchorCategories(\Magento\Store\Model\Store $store)
    {
        $selects = $this->prepareSelectsByRange($this->getNonAnchorCategoriesSelect($store), 'entity_id');
        foreach ($selects as $select) {
            $this->getWriteAdapter()->query(
                $this->getWriteAdapter()->insertFromSelect(
                    $select,
                    $this->getMainTmpTable(),
                    array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
    }

    /**
     * Check if anchor select isset
     *
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    protected function hasAnchorSelect(\Magento\Store\Model\Store $store)
    {
        return isset($this->anchorSelects[$store->getId()]);
    }

    /**
     * Create anchor select
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function createAnchorSelect(\Magento\Store\Model\Store $store)
    {
        $isAnchorAttributeId = $this->config->getAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'is_anchor'
        )->getId();
        $statusAttributeId = $this->config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status')->getId();
        $visibilityAttributeId = $this->config->getAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'visibility'
        )->getId();
        $rootCatIds = explode('/', $this->getPathFromCategoryId($store->getRootCategoryId()));
        array_pop($rootCatIds);
        return $this->getWriteAdapter()->select()->from(
            array('cc' => $this->getTable('catalog_category_entity')),
            array()
        )->joinInner(
            array('cc2' => $this->getTable('catalog_category_entity')),
            'cc2.path LIKE ' . $this->getWriteAdapter()->getConcatSql(
                array($this->getWriteAdapter()->quoteIdentifier('cc.path'), $this->getWriteAdapter()->quote('/%'))
            ) . ' AND cc.entity_id NOT IN (' . implode(
                ',',
                $rootCatIds
            ) . ')',
            array()
        )->joinInner(
            array('ccp' => $this->getTable('catalog_category_product')),
            'ccp.category_id = cc2.entity_id',
            array()
        )->joinInner(
            array('cpw' => $this->getTable('catalog_product_website')),
            'cpw.product_id = ccp.product_id',
            array()
        )->joinInner(
            array('cpsd' => $this->getTable('catalog_product_entity_int')),
            'cpsd.entity_id = ccp.product_id AND cpsd.store_id = 0' . ' AND cpsd.attribute_id = ' . $statusAttributeId,
            array()
        )->joinLeft(
            array('cpss' => $this->getTable('catalog_product_entity_int')),
            'cpss.entity_id = ccp.product_id AND cpss.attribute_id = cpsd.attribute_id' .
            ' AND cpss.store_id = ' .
            $store->getId(),
            array()
        )->joinInner(
            array('cpvd' => $this->getTable('catalog_product_entity_int')),
            'cpvd.entity_id = ccp.product_id AND cpvd.store_id = 0' .
            ' AND cpvd.attribute_id = ' .
            $visibilityAttributeId,
            array()
        )->joinLeft(
            array('cpvs' => $this->getTable('catalog_product_entity_int')),
            'cpvs.entity_id = ccp.product_id AND cpvs.attribute_id = cpvd.attribute_id ' .
            'AND cpvs.store_id = ' .
            $store->getId(),
            array()
        )->joinInner(
            array('ccad' => $this->getTable('catalog_category_entity_int')),
            'ccad.entity_id = cc.entity_id AND ccad.store_id = 0' . ' AND ccad.attribute_id = ' . $isAnchorAttributeId,
            array()
        )->joinLeft(
            array('ccas' => $this->getTable('catalog_category_entity_int')),
            'ccas.entity_id = cc.entity_id AND ccas.attribute_id = ccad.attribute_id' .
            ' AND ccas.store_id = ' .
            $store->getId(),
            array()
        )->where(
            'cpw.website_id = ?',
            $store->getWebsiteId()
        )->where(
            $this->getWriteAdapter()->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        )->where(
            $this->getWriteAdapter()->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
            array(
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
            )
        )->where(
            $this->getWriteAdapter()->getIfNullSql('ccas.value', 'ccad.value') . ' = ?',
            1
        )->columns(
            array(
                'category_id' => 'cc.entity_id',
                'product_id' => 'ccp.product_id',
                'position' => new \Zend_Db_Expr('ccp.position + 10000'),
                'is_parent' => new \Zend_Db_Expr('0'),
                'store_id' => new \Zend_Db_Expr($store->getId()),
                'visibility' => new \Zend_Db_Expr($this->getWriteAdapter()->getIfNullSql('cpvs.value', 'cpvd.value'))
            )
        );
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getAnchorCategoriesSelect(\Magento\Store\Model\Store $store)
    {
        if (!$this->hasAnchorSelect($store)) {
            $this->anchorSelects[$store->getId()] = $this->createAnchorSelect($store);
        }
        return $this->anchorSelects[$store->getId()];
    }

    /**
     * Reindex products of anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    protected function reindexAnchorCategories(\Magento\Store\Model\Store $store)
    {
        $selects = $this->prepareSelectsByRange($this->getAnchorCategoriesSelect($store), 'entity_id');

        foreach ($selects as $select) {
            $this->getWriteAdapter()->query(
                $this->getWriteAdapter()->insertFromSelect(
                    $select,
                    $this->getMainTmpTable(),
                    array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
    }

    /**
     * Get select for all products
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getAllProducts(\Magento\Store\Model\Store $store)
    {
        if (!isset($this->productsSelects[$store->getId()])) {
            $statusAttributeId = $this->config->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'status'
            )->getId();
            $visibilityAttributeId = $this->config->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'visibility'
            )->getId();

            $select = $this->getWriteAdapter()->select()->from(
                array('cp' => $this->getTable('catalog_product_entity')),
                array()
            )->joinInner(
                array('cpw' => $this->getTable('catalog_product_website')),
                'cpw.product_id = cp.entity_id',
                array()
            )->joinInner(
                array('cpsd' => $this->getTable('catalog_product_entity_int')),
                'cpsd.entity_id = cp.entity_id AND cpsd.store_id = 0' .
                ' AND cpsd.attribute_id = ' .
                $statusAttributeId,
                array()
            )->joinLeft(
                array('cpss' => $this->getTable('catalog_product_entity_int')),
                'cpss.entity_id = cp.entity_id AND cpss.attribute_id = cpsd.attribute_id' .
                ' AND cpss.store_id = ' .
                $store->getId(),
                array()
            )->joinInner(
                array('cpvd' => $this->getTable('catalog_product_entity_int')),
                'cpvd.entity_id = cp.entity_id AND cpvd.store_id = 0' .
                ' AND cpvd.attribute_id = ' .
                $visibilityAttributeId,
                array()
            )->joinLeft(
                array('cpvs' => $this->getTable('catalog_product_entity_int')),
                'cpvs.entity_id = cp.entity_id AND cpvs.attribute_id = cpvd.attribute_id ' .
                ' AND cpvs.store_id = ' .
                $store->getId(),
                array()
            )->joinLeft(
                array('ccp' => $this->getTable('catalog_category_product')),
                'ccp.product_id = cp.entity_id',
                array()
            )->where(
                'cpw.website_id = ?',
                $store->getWebsiteId()
            )->where(
                $this->getWriteAdapter()->getIfNullSql('cpss.value', 'cpsd.value') . ' = ?',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            )->where(
                $this->getWriteAdapter()->getIfNullSql('cpvs.value', 'cpvd.value') . ' IN (?)',
                array(
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
                )
            )->group(
                'cp.entity_id'
            )->columns(
                array(
                    'category_id' => new \Zend_Db_Expr($store->getRootCategoryId()),
                    'product_id' => 'cp.entity_id',
                    'position' => new \Zend_Db_Expr(
                        $this->getWriteAdapter()->getCheckSql('ccp.product_id IS NOT NULL', 'ccp.position', '0')
                    ),
                    'is_parent' => new \Zend_Db_Expr(
                        $this->getWriteAdapter()->getCheckSql('ccp.product_id IS NOT NULL', '1', '0')
                    ),
                    'store_id' => new \Zend_Db_Expr($store->getId()),
                    'visibility' => new \Zend_Db_Expr(
                        $this->getWriteAdapter()->getIfNullSql('cpvs.value', 'cpvd.value')
                    )
                )
            );

            $this->productsSelects[$store->getId()] = $select;
        }

        return $this->productsSelects[$store->getId()];
    }

    /**
     * Check whether indexation of root category is needed
     *
     * @return bool
     */
    protected function isIndexRootCategoryNeeded()
    {
        return true;
    }

    /**
     * Reindex all products to root category
     *
     * @param \Magento\Store\Model\Store $store
     * @return void
     */
    protected function reindexRootCategory(\Magento\Store\Model\Store $store)
    {
        if ($this->isIndexRootCategoryNeeded()) {
            $selects = $this->prepareSelectsByRange(
                $this->getAllProducts($store),
                'entity_id',
                self::RANGE_PRODUCT_STEP
            );

            foreach ($selects as $select) {
                $this->getWriteAdapter()->query(
                    $this->getWriteAdapter()->insertFromSelect(
                        $select,
                        $this->getMainTmpTable(),
                        array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
                        \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                    )
                );
            }
        }
    }
}
