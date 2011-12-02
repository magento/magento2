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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product Tag API
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Model_Api extends Mage_Catalog_Model_Api_Resource
{
    /**
     * Retrieve list of tags for specified product
     *
     * @param int $productId
     * @param string|int $store
     * @return array
     */
    public function items($productId, $store = null)
    {
        $result = array();
        // fields list to return
        $fieldsForResult = array('tag_id', 'name');

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);
        if (!$product->getId()) {
            $this->_fault('product_not_exists');
        }

        /** @var $tags Mage_Tag_Model_Resource_Tag_Collection */
        $tags = Mage::getModel('Mage_Tag_Model_Tag')->getCollection()->joinRel()->addProductFilter($productId);
        if ($store) {
            $tags->addStoreFilter($this->_getStoreId($store));
        }

        /** @var $tag Mage_Tag_Model_Tag */
        foreach ($tags as $tag) {
            $result[$tag->getId()] = $tag->toArray($fieldsForResult);
        }

        return $result;
    }

    /**
     * Retrieve tag info as array('name'-> .., 'status' => ..,
     * 'base_popularity' => .., 'products' => array($productId => $popularity, ...))
     *
     * @param int $tagId
     * @param string|int $store
     * @return array
     */
    public function info($tagId, $store)
    {
        $result = array();
        $storeId = $this->_getStoreId($store);
        /** @var $tag Mage_Tag_Model_Tag */
        $tag = Mage::getModel('Mage_Tag_Model_Tag')->setStoreId($storeId)->setAddBasePopularity()->load($tagId);
        if (!$tag->getId()) {
            $this->_fault('tag_not_exists');
        }
        $result['status'] = $tag->getStatus();
        $result['name'] = $tag->getName();
        $result['base_popularity'] = (is_numeric($tag->getBasePopularity())) ? $tag->getBasePopularity() : 0;
        // retrieve array($productId => $popularity, ...)
        $result['products'] = array();
        $relatedProductsCollection = $tag->getEntityCollection()->addTagFilter($tagId)
            ->addStoreFilter($storeId)->addPopularity($tagId);
        foreach ($relatedProductsCollection as $product) {
            $result['products'][$product->getId()] = $product->getPopularity();
        }

        return $result;
    }

    /**
     * Add tag(s) to product.
     * Return array of added/updated tags as array($tagName => $tagId, ...)
     *
     * @param array $data
     * @return array
     */
    public function add($data)
    {
        $data = $this->_prepareDataForAdd($data);
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product')->load($data['product_id']);
        if (!$product->getId()) {
            $this->_fault('product_not_exists');
        }
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($data['customer_id']);
        if (!$customer->getId()) {
            $this->_fault('customer_not_exists');
        }
        $storeId = $this->_getStoreId($data['store']);

        try {
            /** @var $tag Mage_Tag_Model_Tag */
            $tag = Mage::getModel('Mage_Tag_Model_Tag');
            $tagHelper = Mage::helper('Mage_Tag_Helper_Data');
            $tagNamesArr = $tagHelper->cleanTags($tagHelper->extractTags($data['tag']));
            foreach ($tagNamesArr as $tagName) {
                // unset previously added tag data
                $tag->unsetData();
                $tag->loadByName($tagName);
                if (!$tag->getId()) {
                    $tag->setName($tagName)
                        ->setFirstCustomerId($customer->getId())
                        ->setFirstStoreId($storeId)
                        ->setStatus($tag->getPendingStatus())
                        ->save();
                }
                $tag->saveRelation($product->getId(), $customer->getId(), $storeId);
                $result[$tagName] = $tag->getId();
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return $result;
    }

    /**
     * Change existing tag information
     *
     * @param int $tagId
     * @param array $data
     * @param string|int $store
     * @return bool
     */
    public function update($tagId, $data, $store)
    {
        $data = $this->_prepareDataForUpdate($data);
        $storeId = $this->_getStoreId($store);
        /** @var $tag Mage_Tag_Model_Tag */
        $tag = Mage::getModel('Mage_Tag_Model_Tag')->setStoreId($storeId)->setAddBasePopularity()->load($tagId);
        if (!$tag->getId()) {
            $this->_fault('tag_not_exists');
        }

        // store should be set for 'base_popularity' to be saved in Mage_Tag_Model_Resource_Tag::_afterSave()
        $tag->setStore($storeId);
        if (isset($data['base_popularity'])) {
            $tag->setBasePopularity($data['base_popularity']);
        }
        if (isset($data['name'])) {
            $tag->setName(trim($data['name']));
        }
        if (isset($data['status'])) {
            // validate tag status
            if (!in_array($data['status'], array(
                $tag->getApprovedStatus(), $tag->getPendingStatus(), $tag->getDisabledStatus()))) {
                $this->_fault('invalid_data');
            }
            $tag->setStatus($data['status']);
        }

        try {
            $tag->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    /**
     * Remove existing tag
     *
     * @param int $tagId
     * @return bool
     */
    public function remove($tagId)
    {
        /** @var $tag Mage_Tag_Model_Tag */
        $tag = Mage::getModel('Mage_Tag_Model_Tag')->load($tagId);
        if (!$tag->getId()) {
            $this->_fault('tag_not_exists');
        }
        try {
            $tag->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('remove_error', $e->getMessage());
        }

        return true;
    }

    /**
     * Check data before add
     *
     * @param array $data
     * @return array
     */
    protected function _prepareDataForAdd($data)
    {
        if (!isset($data['product_id']) or !isset($data['tag'])
            or !isset($data['customer_id']) or !isset($data['store'])) {
            $this->_fault('invalid_data');
        }

        return $data;
    }

    /**
     * Check data before update
     *
     * @param $data
     * @return
     */
    protected function _prepareDataForUpdate($data)
    {
        // $data should contain at least one field to change
        if ( !(isset($data['name']) or isset($data['status']) or isset($data['base_popularity']))) {
            $this->_fault('invalid_data');
        }

        return $data;
    }
}
