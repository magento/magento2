<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Persistent\Observer\CustomerAuthenticatedEventObserver
 *
 * @since 2.0.0
 */
class CustomerAuthenticatedEventObserver implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * Request http
     *
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $_requestHttp;

    /**
     * @var \Magento\Persistent\Model\QuoteManager
     * @since 2.0.0
     */
    protected $quoteManager;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Persistent\Model\QuoteManager $quoteManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_requestHttp = $request;
        $this->quoteManager = $quoteManager;
    }

    /**
     * Reset session data when customer re-authenticates
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);

        if ($this->_requestHttp->getParam('context') != 'checkout') {
            $this->quoteManager->expire();
            return;
        }

        $this->quoteManager->setGuest();
    }
}
