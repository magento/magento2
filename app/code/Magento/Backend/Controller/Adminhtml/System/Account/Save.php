<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Account;

use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Security\Model\SecurityCookie;
use Magento\User\Model\User;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Account
{
    /**
     * @var SecurityCookie
     */
    private $securityCookie;

    /**
     * Get security cookie
     *
     * @deprecated 100.1.0 This method is deprecated because dependency injection should be used instead of
     *                     directly accessing the SecurityCookie instance.
     *                     Use dependency injection to get an instance of SecurityCookie.
     * @see \Magento\Backend\Controller\Adminhtml\System\Account::__construct()
     * @return SecurityCookie
     */
    private function getSecurityCookie()
    {
        if (!($this->securityCookie instanceof SecurityCookie)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(SecurityCookie::class);
        }
        return $this->securityCookie;
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
                $user->setPasswordConfirmation($passwordConfirmation);
            }
            $errors = $user->validate();
            if ($errors !== true && !empty($errors)) {
                foreach ($errors as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
            } else {
                $user->save();
                $user->sendNotificationEmailsIfRequired();

                $modifiedFields = $this->getModifiedFields($user);
                if (!empty($modifiedFields)) {
                    $countModifiedFields = count($modifiedFields);
                    // validate how many fields were modified to display them correctly
                    if ($countModifiedFields > 1) {
                        $lastModifiedField = array_pop($modifiedFields);
                        $modifiedFieldsText = implode(', ', $modifiedFields);
                        $successMessage = __(
                            'The %1 and %2 of this account have been modified successfully.',
                            $modifiedFieldsText,
                            $lastModifiedField
                        );
                    } else {
                        $successMessage = __(
                            'The %1 of this account has been modified successfully.',
                            reset($modifiedFields)
                        );
                    }
                    $this->messageManager->addSuccessMessage($successMessage);
                } else {
                    $this->messageManager->addSuccessMessage(__('You saved the account.'));
                }
            }
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
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

    /**
     * Get user modified fields
     *
     * @param User $user
     * @return array
     */
    private function getModifiedFields(User $user)
    {
        $modifiedFields = [];
        $propertiesToCheck = ['password', 'username', 'firstname', 'lastname', 'email'];
        foreach ($propertiesToCheck as $property) {
            if ($user->getOrigData($property) !== $user->{'get' . ucfirst($property)}()) {
                $modifiedFields[] = $property;
            }
        }
        return $modifiedFields;
    }
}
