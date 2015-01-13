<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\Exception\State\InvalidTransitionException;

class Confirmation extends \Magento\Customer\Controller\Account
{
    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var AccountManagementInterface  */
    protected $customerAccountManagement;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param RedirectFactory $resultRedirectFactory
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $customerAccountManagement
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $customerAccountManagement,
        UrlFactory $urlFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->urlModel = $urlFactory->create();
        parent::__construct($context, $customerSession, $resultRedirectFactory, $resultPageFactory);
    }

    /**
     * Send confirmation link to specified email
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            try {
                $this->customerAccountManagement->resendConfirmation(
                    $email,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccess(__('Please, check your email for confirmation key.'));
            } catch (InvalidTransitionException $e) {
                $this->messageManager->addSuccess(__('This email does not require confirmation.'));
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Wrong email.'));
                $resultRedirect->setUrl(
                    $this->urlModel->getUrl('*/*/*', ['email' => $email, '_secure' => true])
                );
                return $resultRedirect;
            }
            $this->_getSession()->setUsername($email);
            $resultRedirect->setUrl($this->urlModel->getUrl('*/*/index', ['_secure' => true]));
            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('accountConfirmation')->setEmail(
            $this->getRequest()->getParam('email', $email)
        );
        $resultPage->getLayout()->initMessages();
        return $resultPage;
    }
}
