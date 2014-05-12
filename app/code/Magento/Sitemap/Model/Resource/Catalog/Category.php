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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sitemap\Model\Resource\Catalog;

/**
 * Sitemap resource catalog collection model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Category extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Collection Zend Db select
     *
     * @var \Zend_Db_Select
     */
    protected $_select;

    /**
     * Attribute cache
     *
     * @var array
     */
    protected $_attributesCache = array();

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\Resource\Category
     */
    protected $_categoryResource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Resource\Category $categoryResource
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\Category $categoryResource
    ) {
        $this->_storeManager = $storeManager;
        $this->_categoryResource = $categoryResource;
        parent::__construct($resource);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_entity', 'entity_id');
    }

    /**
     * Get category collection array
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $storeId
     * @return array|bool
     */
    public function getCollection($storeId)
    {
        $categories = array();

        /* @var $store \Magento\Store\Model\Store */
        $store = $this->_storeManager->getStore($storeId);

        if (!$store) {
            return false;
        }

        $this->_select = $this->_getWriteAdapter()->select()->from(
            $this->getMainTable()
        )->where(
            $this->getIdFieldName() . '=?',
            $store->getRootCategoryId()
        );
        $categoryRow = $this->_getWriteAdapter()->fetchRow($this->_select);

        if (!$categoryRow) {
            return false;
        }

        $urConditions = array(
            'e.entity_id = ur.category_id',
            $this->_getWriteAdapter()->quoteInto('ur.store_id=?', $store->getId()),
            'ur.product_id IS NULL',
            $this->_getWriteAdapter()->quoteInto('ur.is_system=?', 1)
        );
        $this->_select = $this->_getWriteAdapter()->select()->from(
            array('e' => $this->getMainTable()),
            array($this->getIdFieldName(), 'updated_at')
        )->joinLeft(
            array('ur' => $this->getTable('core_url_rewrite')),
            join(' AND ', $urConditions),
            array('url' => 'request_path')
        )->where(
            'e.path LIKE ?',
            $categoryRow['path'] . '/%'
        );

        $this->_addFilter($storeId, 'is_active', 1);

        $query = $this->_getWriteAdapter()->query($this->_select);
        while ($row = $query->fetch()) {
            $category = $this->_prepareCategory($row);
            $categories[$category->getId()] = $category;
        }

        return $categories;
    }

    /**
     * Prepare category
     *
     * @param array $categoryRow
     * @return \Magento\Framework\Object
     */
    protected function _prepareCategory(array $categoryRow)
    {
        $category = new \Magento\Framework\Object();
        $category->setId($categoryRow[$this->getIdFieldName()]);
        $categoryUrl = !empty($categoryRow['url']) ? $categoryRow['url'] : 'catalog/category/view/id/' .
            $category->getId();
        $category->setUrl($categoryUrl);
        $category->setUpdatedAt($categoryRow['updated_at']);
        return $category;
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return \Zend_Db_Select|bool
     */
    protected function _addFilter($storeId, $attributeCode, $value, $type = '=')
    {
        if (!$this->_select instanceof \Zend_Db_Select) {
            return false;
        }

        if (!isset($this->_attributesCache[$attributeCode])) {
            $attribute = $this->_categoryResource->getAttribute($attributeCode);

            $this->_attributesCache[$attributeCode] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getId(),
                'table' => $attribute->getBackend()->getTable(),
                'is_global' => $attribute->getIsGlobal(),
                'backend_type' => $attribute->getBackendType()
            );
        }
        $attribute = $this->_attributesCache[$attributeCode];

        switch ($type) {
            case '=':
                $conditionRule = '=?';
                break;
            case 'in':
                $conditionRule = ' IN(?)';
                break;
            default:
                return false;
                break;
        }

        if ($attribute['backend_type'] == 'static') {
            $this->_select->where('e.' . $attributeCode . $conditionRule, $value);
        } else {
            $this->_select->join(
                array('t1_' . $attributeCode => $attribute['table']),
                'e.entity_id = t1_' . $attributeCode . '.entity_id AND t1_' . $attributeCode . '.store_id = 0',
                array()
            )->where(
                't1_' . $attributeCode . '.attribute_id=?',
                $attribute['attribute_id']
            );

            if ($attribute['is_global']) {
                $this->_select->where('t1_' . $attributeCode . '.value' . $conditionRule, $value);
            } else {
                $ifCase = $this->_select->getAdapter()->getCheckSql(
                    't2_' . $attributeCode . '.value_id > 0',
                    't2_' . $attributeCode . '.value',
                    't1_' . $attributeCode . '.value'
                );
                $this->_select->joinLeft(
                    array('t2_' . $attributeCode => $attribute['table']),
                    $this->_getWriteAdapter()->quoteInto(
                        't1_' .
                        $attributeCode .
                        '.entity_id = t2_' .
                        $attributeCode .
                        '.entity_id AND t1_' .
                        $attributeCode .
                        '.attribute_id = t2_' .
                        $attributeCode .
                        '.attribute_id AND t2_' .
                        $attributeCode .
                        '.store_id=?',
                        $storeId
                    ),
                    array()
                )->where(
                    '(' . $ifCase . ')' . $conditionRule,
                    $value
                );
            }
        }

        return $this->_select;
    }
}
