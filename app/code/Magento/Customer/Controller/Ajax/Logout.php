<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Ajax;

/**
 * Logout controller
 *
 * @method \Zend_Controller_Request_Http getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 */
class Logout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var \Magento\Core\Helper\Data $helper
     */
    protected $helper;

    /**
     * Initialize Logout controller
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->helper = $helper;

    }

    /**
     * Customer logout action
     *
     * @return void
     */
    public function execute()
    {
        $lastCustomerId = $this->customerSession->getId();
        $this->customerSession->logout()
            ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastCustomerId($lastCustomerId);

        $this->getResponse()->representJson($this->helper->jsonEncode(['message' => 'Logout Successful']));
    }
}
