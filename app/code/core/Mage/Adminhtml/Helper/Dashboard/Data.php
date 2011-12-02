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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Data helper for dashboard
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Helper_Dashboard_Data extends Mage_Core_Helper_Data
{
    protected $_locale = null;
    protected $_stores = null;

    /**
     * Retrieve stores configured in system.
     *
     * @return array
     */
    public function getStores()
    {
        if(!$this->_stores) {
            $this->_stores = Mage::app()->getStore()->getResourceCollection()->load();
        }

        return $this->_stores;
    }

    /**
     * Retrieve number of loaded stores
     *
     * @return int
     */
    public function countStores()
    {
        return sizeof($this->_stores->getItems());
    }

    /**
     * Prepare array with periods for dashboard graphs
     *
     * @return array
     */
    public function getDatePeriods()
    {
        return array(
            '24h' => $this->__('Last 24 Hours'),
            '7d'  => $this->__('Last 7 Days'),
            '1m'  => $this->__('Current Month'),
            '1y'  => $this->__('YTD'),
            '2y'  => $this->__('2YTD')
        );
    }

    /**
     * Create data hash to ensure that we got valid
     * data and it is not changed by some one else.
     *
     * @param string $data
     * @return string
     */
    public function getChartDataHash($data)
    {
        $secret = (string)Mage::getConfig()->getNode(Mage_Core_Model_App::XML_PATH_INSTALL_DATE);
        return md5($data . $secret);
    }
}
