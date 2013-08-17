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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Sales_Model_Observer_Backend_CustomerQuote
{
    /**
     * @var Mage_Customer_Model_Config_Share
     */
    protected $_config;

    /**
     * @var Mage_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * @param Mage_Core_Model_StoreManagerInterface $storeManager
     * @param Mage_Customer_Model_Config_Share $config
     * @param Mage_Sales_Model_Quote $quote
     */
    public function __construct(
        Mage_Core_Model_StoreManagerInterface $storeManager,
        Mage_Customer_Model_Config_Share $config,
        Mage_Sales_Model_Quote $quote
    ) {
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_quote = $quote;
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param Varien_Event_Observer $observer
     */
    public function dispatch(Varien_Event_Observer $observer)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->getGroupId() !== $customer->getOrigData('group_id')) {
            /**
             * It is needed to process customer's quotes for all websites
             * if customer accounts are shared between all of them
             */
            /** @var $websites Mage_Core_Model_Website[] */
            $websites = $this->_config->isWebsiteScope() ?
                array($this->_storeManager->getWebsite($customer->getWebsiteId())) :
                $this->_storeManager->getWebsites();

            foreach ($websites as $website) {
                $this->_quote->setWebsite($website);
                $this->_quote->loadByCustomer($customer);

                if ($this->_quote->getId()) {
                    $this->_quote->setCustomerGroupId($customer->getGroupId());
                    $this->_quote->collectTotals();
                    $this->_quote->save();
                }
            }
        }
    }
}
