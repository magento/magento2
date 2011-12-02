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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Credit memo API
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Creditmemo_Api_V2 extends Mage_Sales_Model_Order_Creditmemo_Api
{

    /**
     * Prepare filters
     *
     * @param null|object $filters
     * @return array
     */
    protected function _prepareListFilter($filters = null)
    {
        $preparedFilters = array();
        $helper = Mage::helper('Mage_Api_Helper_Data');
        if (isset($filters->filter)) {
            $helper->associativeArrayUnpack($filters->filter);
            $preparedFilters += $filters->filter;
        }
        if (isset($filters->complex_filter)) {
            $helper->associativeArrayUnpack($filters->complex_filter);
            foreach ($filters->complex_filter as &$filter) {
                $helper->associativeArrayUnpack($filter);
            }
            $preparedFilters += $filters->complex_filter;
        }
        foreach ($preparedFilters as $field => $value) {
            if (isset($this->_attributesMap['creditmemo'][$field])) {
                $preparedFilters[$this->_attributesMap['creditmemo'][$field]] = $value;
                unset($preparedFilters[$field]);
            }
        }

        return $preparedFilters;
    }

    /**
     * Prepare data
     *
     * @param null|object $data
     * @return array
     */
    protected function _prepareCreateData($data)
    {
        // convert data object to array, if it's null turn it into empty array
        $data = (isset($data) and is_object($data)) ? get_object_vars($data) : array();
        // convert qtys object to array
        if (isset($data['qtys']) && count($data['qtys'])) {
            $qtysArray = array();
            foreach ($data['qtys'] as &$item) {
                if (isset($item->order_item_id) && isset($item->qty)) {
                    $qtysArray[$item->order_item_id] = $item->qty;
                }
            }
            $data['qtys'] = $qtysArray;
        }
        return $data;
    }
}
