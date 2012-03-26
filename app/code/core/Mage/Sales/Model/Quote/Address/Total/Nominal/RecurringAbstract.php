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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Total model for recurring profiles
 */
abstract class Mage_Sales_Model_Quote_Address_Total_Nominal_RecurringAbstract
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Don't add amounts to address
     *
     * @var bool
     */
    protected $_canAddAmountToAddress = false;

    /**
     * By what key to set data into item
     *
     * @var string
     */
    protected $_itemRowTotalKey = null;

    /**
     * By what key to get data from profile
     *
     * @var string
     */
    protected $_profileDataKey = null;

    /**
     * Collect recurring item parameters and copy to the address items
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_Sales_Model_Quote_Address_Total_Nominal_RecurringAbstract
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $items = $this->_getAddressItems($address);
        foreach ($items as $item) {
            if ($item->getProduct()->isRecurring()) {
                $profileData = $item->getProduct()->getRecurringProfile();
                if (!empty($profileData[$this->_profileDataKey])) {
                    $item->setData(
                        $this->_itemRowTotalKey,
                        $address->getQuote()->getStore()->convertPrice($profileData[$this->_profileDataKey])
                    );
                    $this->_afterCollectSuccess($address, $item);
                }
            }
        }
        return $this;
    }

    /**
     * Don't fetch anything
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return Mage_Sales_Model_Quote_Address_Total_Abstract::fetch($address);
    }

    /**
     * Get nominal items only
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    protected function _getAddressItems(Mage_Sales_Model_Quote_Address $address)
    {
        return $address->getAllNominalItems();
    }

    /**
     * Hook for successful collecting of a recurring amount
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     */
    protected function _afterCollectSuccess($address, $item)
    {
    }
}
