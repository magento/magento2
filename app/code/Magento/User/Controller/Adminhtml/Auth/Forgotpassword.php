<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Auth;

use Magento\Security\Model\SecurityManager;
use Magento\Framework\App\ObjectManager;

class Forgotpassword extends \Magento\User\Controller\Adminhtml\Auth
{
    /**
     * @var SecurityManager
     */
    protected $securityManager;
    
    /**
     * User model factory
     *
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Security\Model\SecurityManager $securityManager
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Security\Model\SecurityManager $securityManager,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory = null
    ) {
        parent::__construct($context, $userFactory);
        $this->securityManager = $securityManager;
        $this->userCollectionFactory = $userCollectionFactory ?:
                ObjectManager::getInstance()->get(\Magento\User\Model\ResourceModel\User\CollectionFactory::class);
    }

    /**
     * Forgot administrator password action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $email = (string)$this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!empty($email) && !empty($params)) {
            // Validate received data to be an email address
            if (\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                try {
                    $this->securityManager->performSecurityCheck(
                        \Magento\Security\Model\PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                        $email
                    );
                } catch (\Magento\Framework\Exception\SecurityViolationException $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                    return $resultRedirect->setPath('admin');
                }
                $collection = $this->userCollectionFactory->create();
                /** @var $collection \Magento\User\Model\ResourceModel\User\Collection */
                $collection->addFieldToFilter('email', $email);
                $collection->load(false);

                try {
                    if ($collection->getSize() > 0) {
                        foreach ($collection as $item) {
                            /** @var \Magento\User\Model\User $user */
                            $user = $this->_userFactory->create()->load($item->getId());
                            if ($user->getId()) {
                                $newPassResetToken = $this->_backendHelper->generateResetPasswordLinkToken();
                                $user->changeResetPasswordLinkToken($newPassResetToken);
                                $user->save();
                                $user->sendPasswordResetConfirmationEmail();
                            }
                            break;
                        }
                    }
                } catch (\Exception $exception) {
                    $this->messageManager->addExceptionMessage(
                        $exception,
                        __('We\'re unable to send the password reset email.')
                    );
                    return $resultRedirect->setPath('admin');
                }
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(__('We\'ll email you a link to reset your password.'));
                // @codingStandardsIgnoreEnd
                $this->getResponse()->setRedirect(
                    $this->_backendHelper->getHomePageUrl()
                );
                return;
            } else {
                $this->messageManager->addError(__('Please correct this email address:'));
            }
        } elseif (!empty($params)) {
            $this->messageManager->addError(__('Please enter an email address.'));
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
