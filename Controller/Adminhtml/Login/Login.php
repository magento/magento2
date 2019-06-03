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
    protected $loginModel;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession  = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager  = null;
    /**
     * @var \Magento\Framework\Url
     */
    protected $url = null;
    /**
     * @var \Magefan\LoginAsCustomer\Model\Config
     */
    protected $config = null;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $metadata;

    /**
     * Login constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magefan\LoginAsCustomer\Model\Login|null $loginModel
     * @param \Magento\Backend\Model\Auth\Session|null $authSession
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @param \Magento\Framework\Url|null $url
     * @param \Magefan\LoginAsCustomer\Model\Config|null $config
     * @param \Magento\Framework\App\ProductMetadataInterface|null $metadata
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magefan\LoginAsCustomer\Model\Login $loginModel = null,
        \Magento\Backend\Model\Auth\Session $authSession = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null,
        \Magento\Framework\Url $url = null,
        \Magefan\LoginAsCustomer\Model\Config $config = null,
        \Magento\Framework\App\ProductMetadataInterface $metadata = null
    ) {
        parent::__construct($context);
        $this->loginModel = $loginModel ?: $this->_objectManager->get(\Magefan\LoginAsCustomer\Model\Login::class);
        $this->authSession = $authSession ?: $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        $this->storeManager = $storeManager ?: $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->url = $url ?: $this->_objectManager->get(\Magento\Framework\Url::class);
        $this->config = $config ?: $this->_objectManager->get(\Magefan\LoginAsCustomer\Model\Config::class);
        $this->metadata = $metadata ?: $this->_objectManager->get(\Magento\Framework\App\ProductMetadataInterface::class);
    }
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('customer_id');
        if (!$customerId) {
            $customerId = (int) $this->getRequest()->getParam('entity_id');
        }

        if (!$this->config->isEnabled()) {
            $msg = __('Magefan Blog is disabled, to enable the extension please navigate to Stores > Configuration > Magefan Extensions > Login As Customer "%1"', $this->getUrl('adminhtml/system_config/edit', ['section' => 'mfloginascustomer']));
            $this->messageManager->addErrorMessage($msg);
            $this->_redirect('customer/index/index');
            return;
        } elseif (!$this->config->isKeyMissing() && $this->metadata->getEdition() != 'Community') {
            $msg = __('Magefan Blog Product Key is missing. Blog Extension will be automatically disabled soon. Please specify the product key in Stores > Configuration > Magefan Extensions > Login As Customer "%1"', $this->getUrl('adminhtml/system_config/edit', ['section' => 'mfloginascustomer']));
            $this->messageManager->addErrorMessage($msg);
            $this->_redirect('customer/index/index');
            return;
        }

        $customerStoreId = $this->getRequest()->getParam('store_id');

        if (!isset($customerStoreId) && $this->config->getStoreViewLogin()) {
            $this->messageManager->addNoticeMessage(__('Select Store View.'));
            $this->_redirect('loginascustomer/login/manual', ['entity_id' => $customerId ]);
            return;
        }

        $login = $this->loginModel->setCustomerId($customerId);

        $login->deleteNotUsed();

        $customer = $login->getCustomer();

        if (!$customer->getId()) {
            $this->messageManager->addError(__('Customer with this ID are no longer exist.'));
            $this->_redirect('customer/index/index');
            return;
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
