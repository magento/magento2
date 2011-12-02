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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment transactions collection
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Model_Resource_Payment_Transaction_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Created Before filter
     *
     * @var string
     */
    protected $_createdBefore          = "";
    /**
     * Initialize collection items factory class
     */
    protected function _construct()
    {
        $this->_init('Mage_Paypal_Model_Payment_Transaction', 'Mage_Paypal_Model_Resource_Payment_Transaction');
        parent::_construct();
    }

    /**
     * CreatedAt filter setter
     *
     * @param string $date
     * @return Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection
     */
    public function addCreatedBeforeFilter($date)
    {
        $this->_createdBefore = $date;
        return $this;
    }

    /**
     * Prepare filters
     *
     * @return Mage_Paypal_Model_Resource_Payment_Transaction_Collection
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        if ($this->isLoaded()) {
            return $this;
        }

        // filters
        if ($this->_createdBefore) {
            $this->getSelect()->where('main_table.created_at < ?', $this->_createdBefore);
        }
        return $this;
    }

    /**
     * Unserialize additional_information in each item
     *
     * @return Mage_Paypal_Model_Resource_Payment_Transaction_Collection
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }
        return parent::_afterLoad();
    }
}
