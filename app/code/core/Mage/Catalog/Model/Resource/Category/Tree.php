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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Category tree model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Category_Tree extends Varien_Data_Tree_Dbp
{
    const ID_FIELD    = 'id';
    const PATH_FIELD  = 'path';
    const ORDER_FIELD = 'order';
    const LEVEL_FIELD = 'level';

    /**
     * Categories resource collection
     *
     * @var Mage_Catalog_Model_Resource_Category_Collection
     */
    protected $_collection;

    /**
     * Id of 'is_active' category attribute
     *
     * @var int
     */
    protected $_isActiveAttributeId              = null;

    /**
     * Join URL rewrites data to collection flag
     *
     * @var boolean
     */
    protected $_joinUrlRewriteIntoCollection     = false;

    /**
     * Inactive categories ids
     *
     * @var array
     */
    protected $_inactiveCategoryIds              = null;

    /**
     * store id
     *
     * @var integer
     */
    protected $_storeId                          = null;

    /**
     * Initialize tree
     *
     */
    public function __construct()
    {
        $resource = Mage::getSingleton('Mage_Core_Model_Resource');

        parent::__construct(
            $resource->getConnection('catalog_write'),
            $resource->getTableName('catalog_category_entity'),
            array(
                Varien_Data_Tree_Dbp::ID_FIELD       => 'entity_id',
                Varien_Data_Tree_Dbp::PATH_FIELD     => 'path',
                Varien_Data_Tree_Dbp::ORDER_FIELD    => 'position',
                Varien_Data_Tree_Dbp::LEVEL_FIELD    => 'level',
            )
        );
    }

    /**
     * Set store id
     *
     * @param integer $storeId
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int) $storeId;
        return $this;
    }

    /**
     * Return store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Enter description here...
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @param boolean $sorted
     * @param array $exclude
     * @param boolean $toLoad
     * @param boolean $onlyActive
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    public function addCollectionData($collection = null, $sorted = false, $exclude = array(), $toLoad = true,
        $onlyActive = false)
    {
        if ($collection === null) {
            $collection = $this->getCollection($sorted);
        } else {
            $this->setCollection($collection);
        }

        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }

        $nodeIds = array();
        foreach ($this->getNodes() as $node) {
            if (!in_array($node->getId(), $exclude)) {
                $nodeIds[] = $node->getId();
            }
        }
        $collection->addIdFilter($nodeIds);
        if ($onlyActive) {

            $disabledIds = $this->_getDisabledIds($collection);
            if ($disabledIds) {
                $collection->addFieldToFilter('entity_id', array('nin' => $disabledIds));
            }
            $collection->addAttributeToFilter('is_active', 1);
            $collection->addAttributeToFilter('include_in_menu', 1);
        }

        if ($this->_joinUrlRewriteIntoCollection) {
            $collection->joinUrlRewrite();
            $this->_joinUrlRewriteIntoCollection = false;
        }

        if ($toLoad) {
            $collection->load();

            foreach ($collection as $category) {
                if ($this->getNodeById($category->getId())) {
                    $this->getNodeById($category->getId())
                        ->addData($category->getData());
                }
            }

            foreach ($this->getNodes() as $node) {
                if (!$collection->getItemById($node->getId()) && $node->getParent()) {
                    $this->removeNode($node);
                }
            }
        }

        return $this;
    }

    /**
     * Add inactive categories ids
     *
     * @param unknown_type $ids
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    public function addInactiveCategoryIds($ids)
    {
        if (!is_array($this->_inactiveCategoryIds)) {
            $this->_initInactiveCategoryIds();
        }
        $this->_inactiveCategoryIds = array_merge($ids, $this->_inactiveCategoryIds);
        return $this;
    }

    /**
     * Retrieve inactive categories ids
     *
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    protected function _initInactiveCategoryIds()
    {
        $this->_inactiveCategoryIds = array();
        Mage::dispatchEvent('catalog_category_tree_init_inactive_category_ids', array('tree' => $this));
        return $this;
    }

    /**
     * Retrieve inactive categories ids
     *
     * @return array
     */
    public function getInactiveCategoryIds()
    {
        if (!is_array($this->_inactiveCategoryIds)) {
            $this->_initInactiveCategoryIds();
        }

        return $this->_inactiveCategoryIds;
    }

    /**
     * Return disable category ids
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @return array
     */
    protected function _getDisabledIds($collection)
    {
        $storeId = Mage::app()->getStore()->getId();

        $this->_inactiveItems = $this->getInactiveCategoryIds();


        $this->_inactiveItems = array_merge(
            $this->_getInactiveItemIds($collection, $storeId),
            $this->_inactiveItems
        );


        $allIds = $collection->getAllIds();
        $disabledIds = array();

        foreach ($allIds as $id) {
            $parents = $this->getNodeById($id)->getPath();
            foreach ($parents as $parent) {
                if (!$this->_getItemIsActive($parent->getId(), $storeId)){
                    $disabledIds[] = $id;
                    continue;
                }
            }
        }
        return $disabledIds;
    }

    /**
     * Returns attribute id for attribute "is_active"
     *
     * @return int
     */
    protected function _getIsActiveAttributeId()
    {
        $resource = Mage::getSingleton('Mage_Core_Model_Resource');
        if ($this->_isActiveAttributeId === null) {
            $bind = array(
                'entity_type_code' => Mage_Catalog_Model_Category::ENTITY,
                'attribute_code'   => 'is_active'
            );
            $select = $this->_conn->select()
                ->from(array('a'=>$resource->getTableName('eav_attribute')), array('attribute_id'))
                ->join(array('t'=>$resource->getTableName('eav_entity_type')), 'a.entity_type_id = t.entity_type_id')
                ->where('entity_type_code = :entity_type_code')
                ->where('attribute_code = :attribute_code');

            $this->_isActiveAttributeId = $this->_conn->fetchOne($select, $bind);
        }
        return $this->_isActiveAttributeId;
    }

    /**
     * Retrieve inactive category item ids
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @param int $storeId
     * @return array
     */
    protected function _getInactiveItemIds($collection, $storeId)
    {
        $filter = $collection->getAllIdsSql();
        $attributeId = $this->_getIsActiveAttributeId();

        $conditionSql = $this->_conn->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $table = Mage::getSingleton('Mage_Core_Model_Resource')->getTableName('catalog_category_entity_int');
        $bind = array(
            'attribute_id' => $attributeId,
            'store_id'     => $storeId,
            'zero_store_id'=> 0,
            'cond'         => 0,

        );
        $select = $this->_conn->select()
            ->from(array('d'=>$table), array('d.entity_id'))
            ->where('d.attribute_id = :attribute_id')
            ->where('d.store_id = :zero_store_id')
            ->where('d.entity_id IN (?)', new Zend_Db_Expr($filter))
            ->joinLeft(
                array('c'=>$table),
                'c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.entity_id = d.entity_id',
                array()
            )
            ->where($conditionSql . ' = :cond');

        return $this->_conn->fetchCol($select, $bind);
    }

    /**
     * Check is category items active
     *
     * @param int $id
     * @return boolean
     */
    protected function _getItemIsActive($id)
    {
        if (!in_array($id, $this->_inactiveItems)) {
            return true;
        }
        return false;
    }

    /**
     * Get categories collection
     *
     * @param boolean $sorted
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCollection($sorted = false)
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_getDefaultCollection($sorted);
        }
        return $this->_collection;
    }

    /**
     * Enter description here...
     *
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    public function setCollection($collection)
    {
        if ($this->_collection !== null) {
            destruct($this->_collection);
        }
        $this->_collection = $collection;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param boolean $sorted
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    protected function _getDefaultCollection($sorted = false)
    {
        $this->_joinUrlRewriteIntoCollection = true;
        $collection = Mage::getModel('Mage_Catalog_Model_Category')->getCollection();
        /** @var $collection Mage_Catalog_Model_Resource_Category_Collection */

        $attributes = Mage::getConfig()->getNode('frontend/category/collection/attributes');
        if ($attributes) {
            $attributes = $attributes->asArray();
            $attributes = array_keys($attributes);
        }
        $collection->addAttributeToSelect($attributes);

        if ($sorted) {
            if (is_string($sorted)) {
                // $sorted is supposed to be attribute name
                $collection->addAttributeToSort($sorted);
            } else {
                $collection->addAttributeToSort('name');
            }
        }

        return $collection;
    }

    /**
     * Move tree before
     *
     * @param unknown_type $category
     * @param Varien_Data_Tree_Node $newParent
     * @param Varien_Data_Tree_Node $prevNode
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    protected function _beforeMove($category, $newParent, $prevNode)
    {
        Mage::dispatchEvent('catalog_category_tree_move_before', array(
            'category'      => $category,
            'prev_parent'   => $prevNode,
            'parent'        => $newParent
        ));

        return $this;
    }

    /**
     * Executing parents move method and cleaning cache after it
     *
     * @param unknown_type $category
     * @param unknown_type $newParent
     * @param unknown_type $prevNode
     */
    public function move($category, $newParent, $prevNode = null)
    {
        $this->_beforeMove($category, $newParent, $prevNode);
        Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Category')
            ->move($category->getId(), $newParent->getId());
        parent::move($category, $newParent, $prevNode);

        $this->_afterMove($category, $newParent, $prevNode);
    }

    /**
     * Move tree after
     *
     * @param unknown_type $category
     * @param Varien_Data_Tree_Node $newParent
     * @param Varien_Data_Tree_Node $prevNode
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    protected function _afterMove($category, $newParent, $prevNode)
    {
        Mage::app()->cleanCache(array(Mage_Catalog_Model_Category::CACHE_TAG));

        Mage::dispatchEvent('catalog_category_tree_move_after', array(
            'category'  => $category,
            'prev_node' => $prevNode,
            'parent'    => $newParent
        ));

        return $this;
    }

    /**
     * Load whole category tree, that will include specified categories ids.
     *
     * @param array $ids
     * @param bool $addCollectionData
     * @param bool $updateAnchorProductCount
     * @return Mage_Catalog_Model_Resource_Category_Tree
     */
    public function loadByIds($ids, $addCollectionData = true, $updateAnchorProductCount = true)
    {
        $levelField = $this->_conn->quoteIdentifier('level');
        $pathField  = $this->_conn->quoteIdentifier('path');
        // load first two levels, if no ids specified
        if (empty($ids)) {
            $select = $this->_conn->select()
                ->from($this->_table, 'entity_id')
                ->where($levelField . ' <= 2');
            $ids = $this->_conn->fetchCol($select);
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }

        // collect paths of specified IDs and prepare to collect all their parents and neighbours
        $select = $this->_conn->select()
            ->from($this->_table, array('path', 'level'))
            ->where('entity_id IN (?)', $ids);
        $where = array($levelField . '=0' => true);

        foreach ($this->_conn->fetchAll($select) as $item) {
            $pathIds  = explode('/', $item['path']);
            $level = (int)$item['level'];
            while ($level > 0) {
                $pathIds[count($pathIds) - 1] = '%';
                $path = implode('/', $pathIds);
                $where["$levelField=$level AND $pathField LIKE '$path'"] = true;
                array_pop($pathIds);
                $level--;
            }
        }
        $where = array_keys($where);

        // get all required records
        if ($addCollectionData) {
            $select = $this->_createCollectionDataSelect();
        } else {
            $select = clone $this->_select;
            $select->order($this->_orderField . ' ' . Varien_Db_Select::SQL_ASC);
        }
        $select->where(implode(' OR ', $where));

        // get array of records and add them as nodes to the tree
        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }
        if ($updateAnchorProductCount) {
            $this->_updateAnchorProductCount($arrNodes);
        }
        $childrenItems = array();
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->addChildNodes($childrenItems, '', null);
        return $this;
    }

    /**
     * Load array of category parents
     *
     * @param string $path
     * @param bool $addCollectionData
     * @param bool $withRootNode
     * @return array
     */
    public function loadBreadcrumbsArray($path, $addCollectionData = true, $withRootNode = false)
    {
        $pathIds = explode('/', $path);
        if (!$withRootNode) {
            array_shift($pathIds);
        }
        $result = array();
        if (!empty($pathIds)) {
            if ($addCollectionData) {
                $select = $this->_createCollectionDataSelect(false);
            } else {
                $select = clone $this->_select;
            }
            $select
                ->where('e.entity_id IN(?)', $pathIds)
                ->order($this->_conn->getLengthSql('e.path') . ' ' . Varien_Db_Select::SQL_ASC);
            $result = $this->_conn->fetchAll($select);
            $this->_updateAnchorProductCount($result);
        }
        return $result;
    }

    /**
     * Replace products count with self products count, if category is non-anchor
     *
     * @param array $data
     */
    protected function _updateAnchorProductCount(&$data)
    {
        foreach ($data as $key => $row) {
            if (0 === (int)$row['is_anchor']) {
                $data[$key]['product_count'] = $row['self_product_count'];
            }
        }
    }

    /**
     * Obtain select for categories with attributes.
     * By default everything from entity table is selected
     * + name, is_active and is_anchor
     * Also the correct product_count is selected, depending on is the category anchor or not.
     *
     * @param bool $sorted
     * @param array $optionalAttributes
     * @return Zend_Db_Select
     */
    protected function _createCollectionDataSelect($sorted = true, $optionalAttributes = array())
    {
        $select = $this->_getDefaultCollection($sorted ? $this->_orderField : false)
            ->getSelect();
        // add attributes to select
        $attributes = array('name', 'is_active', 'is_anchor');
        if ($optionalAttributes) {
            $attributes = array_unique(array_merge($attributes, $optionalAttributes));
        }
        $resource = Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Category');
        foreach ($attributes as $attributeCode) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            $attribute = $resource->getAttribute($attributeCode);
            // join non-static attribute table
            if (!$attribute->getBackend()->isStatic()) {
                $tableDefault   = sprintf('d_%s', $attributeCode);
                $tableStore     = sprintf('s_%s', $attributeCode);
                $valueExpr      = $this->_conn
                    ->getCheckSql("{$tableStore}.value_id > 0", "{$tableStore}.value", "{$tableDefault}.value");

                $select
                    ->joinLeft(
                        array($tableDefault => $attribute->getBackend()->getTable()),
                        sprintf('%1$s.entity_id=e.entity_id AND %1$s.attribute_id=%2$d'
                            . ' AND %1$s.entity_type_id=e.entity_type_id AND %1$s.store_id=%3$d',
                            $tableDefault, $attribute->getId(), Mage_Core_Model_App::ADMIN_STORE_ID),
                        array($attributeCode => 'value'))
                    ->joinLeft(
                        array($tableStore => $attribute->getBackend()->getTable()),
                        sprintf('%1$s.entity_id=e.entity_id AND %1$s.attribute_id=%2$d'
                            . ' AND %1$s.entity_type_id=e.entity_type_id AND %1$s.store_id=%3$d',
                            $tableStore, $attribute->getId(), $this->getStoreId()),
                        array($attributeCode => $valueExpr)
                    );
            }
        }

        // count children products qty plus self products qty
        $categoriesTable         = Mage::getSingleton('Mage_Core_Model_Resource')->getTableName('catalog_category_entity');
        $categoriesProductsTable = Mage::getSingleton('Mage_Core_Model_Resource')->getTableName('catalog_category_product');

        $subConcat = $this->_conn->getConcatSql(array('e.path', $this->_conn->quote('/%')));
        $subSelect = $this->_conn->select()
            ->from(array('see' => $categoriesTable), null)
            ->joinLeft(
                array('scp' => $categoriesProductsTable),
                'see.entity_id=scp.category_id',
                array('COUNT(DISTINCT scp.product_id)'))
            ->where('see.entity_id = e.entity_id')
            ->orWhere('see.path LIKE ?', $subConcat);
        $select->columns(array('product_count' => $subSelect));

        $subSelect = $this->_conn->select()
            ->from(array('cp' => $categoriesProductsTable), 'COUNT(cp.product_id)')
            ->where('cp.category_id = e.entity_id');

        $select->columns(array('self_product_count' => $subSelect));

        return $select;
    }

    /**
     * Get real existing category ids by specified ids
     *
     * @param array $ids
     * @return array
     */
    public function getExistingCategoryIdsBySpecifiedIds($ids)
    {
        if (empty($ids)) {
            return array();
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $select = $this->_conn->select()
            ->from($this->_table, array('entity_id'))
            ->where('entity_id IN (?)', $ids);
        return $this->_conn->fetchCol($select);
    }
}
