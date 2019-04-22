<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Controller\Adminhtml\Login;

/**
 * Class Login
 * @package Magefan\LoginAsCustomer\Controller\Adminhtml\Login
 */
class Login extends \Magento\Backend\App\Action
{
    /**
     * @var \Magefan\LoginAsCustomer\Model\Login
     */
    protected $login;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $session  = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager  = null;
    /**
     * @var \Magento\Framework\Url
     */
    protected $url = null;
    /**
     * Login constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magefan\LoginAsCustomer\Model\Login $login
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magefan\LoginAsCustomer\Model\Login $login = null,
        \Magento\Backend\Model\Auth\Session $session = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null,
        \Magento\Framework\Url $url = null
    ) {
        parent::__construct($context);
        $objectManager = $this->_objectManager;
        $this->login = $login ?: $objectManager->get(\Magefan\LoginAsCustomer\Model\Login::class);
        $this->session = $session ?: $objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        $this->storeManager = $storeManager ?: $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->url = $url ?: $objectManager->get(\Magento\Framework\Url::class);
    }
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('customer_id');

        $login = $this->login->setCustomerId($customerId);

        $login->deleteNotUsed();

        $customer = $login->getCustomer();

        if (!$customer->getId()) {
            $this->messageManager->addError(__('Customer with this ID are no longer exist.'));
            $this->_redirect('customer/index/index');
            return;
        }

        $user = $this->session->getUser();
        $login->generate($user->getId());
        $customerStoreId = $this->storeManager->getStore();

        if (null === $customerStoreId) {
            $store = $this->storeManager->getDefaultStoreView();
        }

        $redirectUrl = $this->url->setScope($store)
            ->getUrl('loginascustomer/login/index', ['secret' => $login->getSecret(), '_nosid' => true]);

        $this->getResponse()->setRedirect($redirectUrl);
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magefan_LoginAsCustomer::login_button');
    }
}
