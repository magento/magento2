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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Checkout observer model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Checkout\Model;

class Observer
{
    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Session $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(Session $checkoutSession, \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->_checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @return void
     */
    public function unsetAll()
    {
        $this->_checkoutSession->clearQuote()->clearStorage();
    }

    /**
     * @return void
     */
    public function loadCustomerQuote()
    {
        try {
            $this->_checkoutSession->loadCustomerQuote();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Load customer quote error'));
        }
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function salesQuoteSaveAfter($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        /* @var $quote \Magento\Sales\Model\Quote */
        if ($quote->getIsCheckoutCart()) {
            $this->_checkoutSession->getQuoteId($quote->getId());
        }
    }
}
