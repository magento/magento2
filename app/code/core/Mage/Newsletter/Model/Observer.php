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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_Model_Observer
{
    public function subscribeCustomer($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (($customer instanceof Mage_Customer_Model_Customer)) {
            Mage::getModel('Mage_Newsletter_Model_Subscriber')->subscribeCustomer($customer);
        }
        return $this;
    }

    /**
     * Customer delete handler
     *
     * @param Varien_Object $observer
     * @return Mage_Newsletter_Model_Observer
     */
    public function customerDeleted($observer)
    {
        $subscriber = Mage::getModel('Mage_Newsletter_Model_Subscriber')
            ->loadByEmail($observer->getEvent()->getCustomer()->getEmail());
        if($subscriber->getId()) {
            $subscriber->delete();
        }
        return $this;
    }

    public function scheduledSend($schedule)
    {
        $countOfQueue  = 3;
        $countOfSubscritions = 20;

        $collection = Mage::getModel('Mage_Newsletter_Model_Queue')->getCollection()
            ->setPageSize($countOfQueue)
            ->setCurPage(1)
            ->addOnlyForSendingFilter()
            ->load();

         $collection->walk('sendPerSubscriber', array($countOfSubscritions));
    }

    /**
     * Set the subscriber store id on the transport object
     *
     * Set the subscriber store id on the transport object so the logo configuration for the
     * subscriber store is read (which is not necessarily the current one).
     *
     * @param Varien_Object $observer
     * @return Mage_Newsletter_Model_Observer
     */
    public function emailTemplateFilterBefore($observer)
    {
        // Only match newsletter subscriber events
        $subscriber = $observer->getTransport()->getVariables()->getSubscriber();
        if ($subscriber && $subscriber instanceof Mage_Newsletter_Model_Subscriber) {
            $observer->getTransport()->setStoreId($subscriber->getStoreId());
        }
        return $this;
    }
}
