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
namespace Magento\Sales\Model\Observer\Backend;

class CustomerQuote
{
    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    protected $_config;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote;

    /**
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Config\Share $config
     * @param \Magento\Sales\Model\Quote $quote
     */
    public function __construct(
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Config\Share $config,
        \Magento\Sales\Model\Quote $quote
    ) {
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_quote = $quote;
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param \Magento\Event\Observer $observer
     */
    public function dispatch(\Magento\Event\Observer $observer)
    {
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->getGroupId() !== $customer->getOrigData('group_id')) {
            /**
             * It is needed to process customer's quotes for all websites
             * if customer accounts are shared between all of them
             */
            /** @var $websites \Magento\Core\Model\Website[] */
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
