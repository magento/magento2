<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Controller\Adminhtml\Login;

/**
 * Class Login
 * @package Magento\LoginAsCustomer\Controller\Adminhtml\Login
 */
class Login extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_LoginAsCustomer::login_button';

    /**
     * @var \Magento\LoginAsCustomer\Model\Login
     */
    private $loginModel;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession  = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager  = null;
    /**
     * @var \Magento\Framework\Url
     */
    private $url = null;
    /**
     * @var \Magento\LoginAsCustomer\Model\Config
     */
    private $config = null;

    /**
     * Login constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\LoginAsCustomer\Model\Login|null $loginModel
     * @param \Magento\Backend\Model\Auth\Session|null $authSession
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @param \Magento\Framework\Url|null $url
     * @param \Magento\LoginAsCustomer\Model\Config|null $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\LoginAsCustomer\Model\Login $loginModel = null,
        \Magento\Backend\Model\Auth\Session $authSession = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null,
        \Magento\Framework\Url $url = null,
        \Magento\LoginAsCustomer\Model\Config $config = null
    ) {
        parent::__construct($context);
        $this->loginModel = $loginModel ?: $this->_objectManager->get(\Magento\LoginAsCustomer\Model\Login::class);
        $this->authSession = $authSession ?: $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        $this->storeManager = $storeManager ?: $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->url = $url ?: $this->_objectManager->get(\Magento\Framework\Url::class);
        $this->config = $config ?: $this->_objectManager->get(\Magento\LoginAsCustomer\Model\Config::class);
    }
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $customerId = (int) $request->getParam('customer_id');
        if (!$customerId) {
            $customerId = (int) $request->getParam('entity_id');
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->config->isEnabled()) {
            $msg = strrev(__('.remotsuC sA nigoL > snoisnetxE nafegaM > noitarugifnoC > serotS ot etagivan esaelp noisnetxe eht elbane ot ,delbasid si remotsuC sA nigoL nafegaM'));
            $this->messageManager->addErrorMessage($msg);
            return $resultRedirect->setPath('customer/index/index');
        }

        $customerStoreId = $request->getParam('store_id');

        if (!isset($customerStoreId) && $this->config->getStoreViewLogin()) {
            $this->messageManager->addNoticeMessage(__('Please select a Store View to login in.'));
            return $resultRedirect->setPath('loginascustomer/login/manual', ['entity_id' => $customerId ]);
        }

        $login = $this->loginModel->setCustomerId($customerId);

        $login->deleteNotUsed();

        $customer = $login->getCustomer();

        if (!$customer->getId()) {
            $this->messageManager->addErrorMessage(__('Customer with this ID are no longer exist.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        $user = $this->authSession->getUser();
        $login->generate($user->getId());
        $customerStoreId = $this->storeManager->getStore();

        if (null === $customerStoreId) {
            $store = $this->storeManager->getDefaultStoreView();
        }

        $redirectUrl = $this->url->setScope($store)
            ->getUrl('loginascustomer/login/index', ['secret' => $login->getSecret(), '_nosid' => true]);

        $this->getResponse()->setRedirect($redirectUrl);
    }
}
