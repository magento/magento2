<?php
/**
 *
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
namespace Magento\Persistent\Model;

class QuoteManager
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $persistentSession = null;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $persistentData = null;

    /**
     * Whether set quote to be persistent in workflow
     *
     * @var bool
     */
    protected $_setQuotePersistent = true;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->persistentSession = $persistentSession;
        $this->persistentData = $persistentData;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Make quote to be guest
     *
     * @param bool $checkQuote Check quote to be persistent (not stolen)
     * @return void
     */
    public function setGuest($checkQuote = false)
    {
        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $this->checkoutSession->getQuote();
        if ($quote && $quote->getId()) {
            if ($checkQuote && !$this->persistentData->isShoppingCartPersist() && !$quote->getIsPersistent()) {
                $this->checkoutSession->clearQuote()->clearStorage();
                return;
            }

            $quote->getPaymentsCollection()->walk('delete');
            $quote->getAddressesCollection()->walk('delete');
            $this->_setQuotePersistent = false;
            $quote->setIsActive(
                true
            )->setCustomerId(
                null
            )->setCustomerEmail(
                null
            )->setCustomerFirstname(
                null
            )->setCustomerLastname(
                null
            )->setCustomerGroupId(
                \Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID
            )->setIsPersistent(
                false
            )->removeAllAddresses();
            //Create guest addresses
            $quote->getShippingAddress();
            $quote->getBillingAddress();
            $quote->collectTotals()->save();
        }

        $this->persistentSession->getSession()->removePersistentCookie();
    }

    /**
     * Expire persistent quote
     *
     * @return void
     */
    public function expire()
    {
        $quote = $this->checkoutSession->setLoadInactive()->getQuote();
        if ($quote->getIsActive() && $quote->getCustomerId()) {
            $this->checkoutSession->setCustomerData(null)->clearQuote()->clearStorage();
        } else {
            $quote
                ->setIsActive(true)
                ->setIsPersistent(false)
                ->setCustomerId(null)
                ->setCustomerGroupId(\Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID);
        }
    }

    /**
     * Is quote persistent
     *
     * @return bool
     */
    public function isPersistent()
    {
        return $this->_setQuotePersistent;
    }
}
