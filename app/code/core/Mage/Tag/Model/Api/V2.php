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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product Tag API
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Model_Api_V2 extends Mage_Tag_Model_Api
{
    /**
     * Retrieve list of tags for specified product as array of objects
     *
     * @param int $productId
     * @param string|int $store
     * @return array
     */
    public function items($productId, $store = null)
    {
        $result = parent::items($productId, $store);
        foreach ($result as $key => $tag) {
            $result[$key] = Mage::helper('Mage_Api_Helper_Data')->wsiArrayPacker($tag);
        }
        return array_values($result);
    }

    /**
     * Add tag(s) to product.
     * Return array of objects
     *
     * @param array $data
     * @return array
     */
    public function add($data)
    {
        $result = array();
        foreach (parent::add($data) as $key => $value) {
            $result[] = array('key' => $key, 'value' => $value);
        }

        return $result;
    }

    /**
     * Retrieve tag info as object
     *
     * @param int $tagId
     * @param string|int $store
     * @return object
     */
    public function info($tagId, $store)
    {
        $result = parent::info($tagId, $store);
        $result = Mage::helper('Mage_Api_Helper_Data')->wsiArrayPacker($result);
        foreach ($result->products as $key => $value) {
            $result->products[$key] = array('key' => $key, 'value' => $value);
        }
        return $result;
    }

    /**
     * Convert data from object to array before add
     *
     * @param object $data
     * @return array
     */
    protected function _prepareDataForAdd($data)
    {
        Mage::helper('Mage_Api_Helper_Data')->toArray($data);
        return parent::_prepareDataForAdd($data);
    }

    /**
     * Convert data from object to array before update
     *
     * @param object $data
     * @return array
     */
    protected function _prepareDataForUpdate($data)
    {
        Mage::helper('Mage_Api_Helper_Data')->toArray($data);
        return parent::_prepareDataForUpdate($data);
    }
}
