<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Account;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Security\Model\SecurityCookie;

/**
 * Saving an admin user info.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Account implements HttpPostActionInterface
{
    /**
     * @var SecurityCookie
     */
    private $securityCookie;

    /**
     * Save controller constructor.
     *
     * @param Context $context
     * @param SecurityCookie|null $securityCookie
     */
    public function __construct(Context $context, ?SecurityCookie $securityCookie = null)
    {
        parent::__construct($context);
        $this->securityCookie = $securityCookie ?? ObjectManager::getInstance()->get(SecurityCookie::class);
    }

    /**
     * Saving edited user information
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $userId = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser()->getId();
        $password = (string)$this->getRequest()->getParam('password');
        $passwordConfirmation = (string)$this->getRequest()->getParam('password_confirmation');
        $interfaceLocale = (string)$this->getRequest()->getParam('interface_locale', false);

        /** @var $user \Magento\User\Model\User */
        $user = $this->_objectManager->create(\Magento\User\Model\User::class)->load($userId);

        $user->setId($userId)
            ->setUserName($this->getRequest()->getParam('username', false))
            ->setFirstName($this->getRequest()->getParam('firstname', false))
            ->setLastName($this->getRequest()->getParam('lastname', false))
            ->setEmail(strtolower($this->getRequest()->getParam('email', false)));

        if ($this->_objectManager->get(\Magento\Framework\Validator\Locale::class)->isValid($interfaceLocale)) {
            $user->setInterfaceLocale($interfaceLocale);
            /** @var \Magento\Backend\Model\Locale\Manager $localeManager */
            $localeManager = $this->_objectManager->get(\Magento\Backend\Model\Locale\Manager::class);
            $localeManager->switchBackendInterfaceLocale($interfaceLocale);
        }
        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        $currentUserPasswordField = \Magento\User\Block\User\Edit\Tab\Main::CURRENT_USER_PASSWORD_FIELD;
        $currentUserPassword = $this->getRequest()->getParam($currentUserPasswordField);
        try {
            $user->performIdentityCheck($currentUserPassword);
            if ($password !== '') {
                $user->setPassword($password);
                $user->setData('password_confirmation', $passwordConfirmation);
            }
            $errors = $user->validate();
            if ($errors !== true && !empty($errors)) {
                foreach ($errors as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
            } else {
                $user->save();
                $user->sendNotificationEmailsIfRequired();
                $this->messageManager->addSuccessMessage(__('You saved the account.'));
            }
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->securityCookie->setLogoutReasonCookie(
                \Magento\Security\Model\AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
        } catch (ValidatorException $e) {
            $this->messageManager->addMessages($e->getMessages());
            if ($e->getMessage()) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving account.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath("*/*/");
    }
}
