<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Auth;

class Forgotpassword extends \Magento\User\Controller\Adminhtml\Auth
{
    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Security\Model\SecurityManager $securityManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Security\Model\SecurityManager $securityManager
    ) {
        parent::__construct($context, $userFactory);
        $this->securityManager = $securityManager;
    }

    /**
     * Forgot administrator password action
     *
     * @return void
     */
    public function execute()
    {
        $email = (string)$this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();

        if (!empty($email) && !empty($params)) {
            // Validate received data to be an email address
            if (\Zend_Validate::is($email, 'EmailAddress')) {
                try {
                    $this->securityManager->performSecurityCheck(
                        \Magento\Security\Model\PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                        $email
                    );
                } catch (\Magento\Framework\Exception\SecurityViolationException $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('admin');
                }
                $collection = $this->_objectManager->get('Magento\User\Model\ResourceModel\User\Collection');
                /** @var $collection \Magento\User\Model\ResourceModel\User\Collection */
                $collection->addFieldToFilter('email', $email);
                $collection->load(false);

                if ($collection->getSize() > 0) {
                    foreach ($collection as $item) {
                        /** @var \Magento\User\Model\User $user */
                        $user = $this->_userFactory->create()->load($item->getId());
                        if ($user->getId()) {
                            $newPassResetToken = $this->_objectManager->get(
                                'Magento\User\Helper\Data'
                            )->generateResetPasswordLinkToken();
                            $user->changeResetPasswordLinkToken($newPassResetToken);
                            $user->save();
                            $user->sendPasswordResetConfirmationEmail();
                        }
                        break;
                    }
                }
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(__('We\'ll email you a link to reset your password.'));
                // @codingStandardsIgnoreEnd
                $this->getResponse()->setRedirect(
                    $this->_objectManager->get('Magento\Backend\Helper\Data')->getHomePageUrl()
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
