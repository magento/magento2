<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Controller\Adminhtml\System\Account;

class Save extends \Magento\Backend\Controller\Adminhtml\System\Account
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Saving edited user information
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $userId = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getId();
        $password = (string)$this->getRequest()->getParam('password');
        $passwordConfirmation = (string)$this->getRequest()->getParam('password_confirmation');
        $interfaceLocale = (string)$this->getRequest()->getParam('interface_locale', false);

        /** @var $user \Magento\User\Model\User */
        $user = $this->_objectManager->create('Magento\User\Model\User')->load($userId);

        $user->setId(
            $userId
        )->setUsername(
            $this->getRequest()->getParam('username', false)
        )->setFirstname(
            $this->getRequest()->getParam('firstname', false)
        )->setLastname(
            $this->getRequest()->getParam('lastname', false)
        )->setEmail(
            strtolower($this->getRequest()->getParam('email', false))
        );

        if ($this->_objectManager->get('Magento\Framework\Locale\Validator')->isValid($interfaceLocale)) {
            $user->setInterfaceLocale($interfaceLocale);
            $this->_objectManager->get(
                'Magento\Backend\Model\Locale\Manager'
            )->switchBackendInterfaceLocale(
                $interfaceLocale
            );
        }
        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        $currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
        $currentUserPassword = $this->getRequest()->getParam($currentUserPasswordField);
        $isCurrentUserPasswordValid = !empty($currentUserPassword) && is_string($currentUserPassword);
        try {
            if (!($isCurrentUserPasswordValid && $user->verifyIdentity($currentUserPassword))) {
                throw new \Magento\Backend\Model\Auth\Exception(
                    __('You have entered an invalid password for current user.')
                );
            }
            if ($password !== '') {
                $user->setPassword($password);
                $user->setPasswordConfirmation($passwordConfirmation);
            }
            $user->save();
            $user->sendPasswordResetNotificationEmail();
            $this->messageManager->addSuccess(__('The account has been saved.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addMessages($e->getMessages());
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while saving account.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath("*/*/");
    }
}
