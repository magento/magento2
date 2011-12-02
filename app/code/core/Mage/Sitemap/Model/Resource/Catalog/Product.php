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
 * @package     Mage_Sitemap
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sitemap resource product collection model
 *
 * @category    Mage
 * @package     Mage_Sitemap
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sitemap_Model_Resource_Catalog_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Collection Zend Db select
     *
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * Attribute cache
     *
     * @var array
     */
    protected $_attributesCache    = array();

    /**
     * Init resource model (catalog/category)
     *
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Add attribute to filter
     *
     * @param int $storeId
     * @param string $attributeCode
     * @param mixed $value
     * @param string $type
     * @return Zend_Db_Select
     */
    protected function _addFilter($storeId, $attributeCode, $value, $type = '=')
    {
        if (!isset($this->_attributesCache[$attributeCode])) {
            $attribute = Mage::getSingleton('Mage_Catalog_Model_Product')->getResource()->getAttribute($attributeCode);

            $this->_attributesCache[$attributeCode] = array(
                'entity_type_id'    => $attribute->getEntityTypeId(),
                'attribute_id'      => $attribute->getId(),
                'table'             => $attribute->getBackend()->getTable(),
                'is_global'         => $attribute->getIsGlobal() == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'backend_type'      => $attribute->getBackendType()
            );
        }

        $attribute = $this->_attributesCache[$attributeCode];

        if (!$this->_select instanceof Zend_Db_Select) {
            return false;
        }

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
                array('t1_'.$attributeCode => $attribute['table']),
                'e.entity_id=t1_'.$attributeCode.'.entity_id AND t1_'.$attributeCode.'.store_id=0',
                array()
            )
            ->where('t1_'.$attributeCode.'.attribute_id=?', $attribute['attribute_id']);

            if ($attribute['is_global']) {
                $this->_select->where('t1_'.$attributeCode.'.value'.$conditionRule, $value);
            } else {
                $ifCase = $this->_select->getAdapter()->getCheckSql('t2_'.$attributeCode.'.value_id > 0', 't2_'.$attributeCode.'.value', 't1_'.$attributeCode.'.value');
                $this->_select->joinLeft(
                    array('t2_'.$attributeCode => $attribute['table']),
                    $this->_getWriteAdapter()->quoteInto('t1_'.$attributeCode.'.entity_id = t2_'.$attributeCode.'.entity_id AND t1_'.$attributeCode.'.attribute_id = t2_'.$attributeCode.'.attribute_id AND t2_'.$attributeCode.'.store_id=?', $storeId),
                    array()
                )
                ->where('('.$ifCase.')'.$conditionRule, $value);
            }
        }

        return $this->_select;
    }

    /**
     * Get category collection array
     *
     * @param unknown_type $storeId
     * @return array
     */
    public function getCollection($storeId)
    {
        $products = array();

        $store = Mage::app()->getStore($storeId);
        /* @var $store Mage_Core_Model_Store */

        if (!$store) {
            return false;
        }

        $urCondions = array(
            'e.entity_id=ur.product_id',
            'ur.category_id IS NULL',
            $this->_getWriteAdapter()->quoteInto('ur.store_id=?', $store->getId()),
            $this->_getWriteAdapter()->quoteInto('ur.is_system=?', 1),
        );
        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('e' => $this->getMainTable()), array($this->getIdFieldName()))
            ->join(
                array('w' => $this->getTable('catalog_product_website')),
                'e.entity_id=w.product_id',
                array()
            )
            ->where('w.website_id=?', $store->getWebsiteId())
            ->joinLeft(
                array('ur' => $this->getTable('core_url_rewrite')),
                join(' AND ', $urCondions),
                array('url' => 'request_path')
            );

        $this->_addFilter($storeId, 'visibility', Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInSiteIds(), 'in');
        $this->_addFilter($storeId, 'status', Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getVisibleStatusIds(), 'in');

        $query = $this->_getWriteAdapter()->query($this->_select);
        while ($row = $query->fetch()) {
            $product = $this->_prepareProduct($row);
            $products[$product->getId()] = $product;
        }

        return $products;
    }

    /**
     * Prepare product
     *
     * @param array $productRow
     * @return Varien_Object
     */
    protected function _prepareProduct(array $productRow)
    {
        $product = new Varien_Object();
        $product->setId($productRow[$this->getIdFieldName()]);
        $productUrl = !empty($productRow['url']) ? $productRow['url'] : 'catalog/product/view/id/' . $product->getId();
        $product->setUrl($productUrl);
        return $product;
    }
}
