<?php
// phpcs:ignoreFile
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminAdobeIms\Controller\Adminhtml\User;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Locale\Manager;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Validator\Exception;
use Magento\Framework\Validator\Locale;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\SecurityCookie;
use Magento\User\Controller\Adminhtml\User as UserController;
use Magento\User\Model\Spi\NotificationExceptionInterface;
use Magento\User\Block\User\Edit\Tab\Main;
use Magento\User\Model\User;

/**
 * Save admin user.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends UserController implements HttpPostActionInterface
{
    /**
     * @var SecurityCookie
     */
    private $securityCookie;

    /**
     * Get security cookie
     *
     * @return SecurityCookie
     * @deprecated 100.1.0
     */
    private function getSecurityCookie()
    {
        if (!($this->securityCookie instanceof SecurityCookie)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(SecurityCookie::class);
        } else {
            return $this->securityCookie;
        }
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPostValue();
        if (array_key_exists('form_key', $data)) {
            unset($data['form_key']);
        }
        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }

        /** @var $model User */
        $model = $this->_userFactory->create()->load($userId);
        if ($userId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This user no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }
        $model->setData($this->_getAdminUserData($data));
        $userRoles = $this->getRequest()->getParam('roles', []);
        if (count($userRoles)) {
            $model->setRoleId($userRoles[0]);
        }

        /** @var $currentUser User */
        $currentUser = $this->_objectManager->get(Session::class)->getUser();
        if ($userId == $currentUser->getId()
            && $this->_objectManager->get(Locale::class)
                ->isValid($data['interface_locale'])
        ) {
            $this->_objectManager->get(
                Manager::class
            )->switchBackendInterfaceLocale(
                $data['interface_locale']
            );
        }

        /** Before updating admin user data, ensure that password of current admin user is entered and is correct */
        try {
            $currentUser->performIdentityCheck($data[Main::CURRENT_USER_PASSWORD_FIELD] ?? '');
            $model->save();

            $this->messageManager->addSuccess(__('You saved the user.'));
            $this->_getSession()->setUserData(false);
            $this->_redirect('adminhtml/*/');

            $model->sendNotificationEmailsIfRequired();
        } catch (UserLockedException $e) {
            $this->_auth->logout();
            $this->getSecurityCookie()->setLogoutReasonCookie(
                AdminSessionsManager::LOGOUT_REASON_USER_LOCKED
            );
            $this->_redirect('*');
        } catch (NotificationExceptionInterface $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (AuthenticationException $e) {
            $this->messageManager->addError(
                __('The password entered for the current user is invalid. Verify the password and try again.')
            );
            $this->redirectToEdit($model, $data);
        } catch (Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->redirectToEdit($model, $data);
        }
    }

    /**
     * Redirect to Edit form.
     *
     * @param User $model
     * @param array $data
     * @return void
     */
    private function redirectToEdit(User $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['user_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => '']);
        $this->_redirect('adminhtml/*/edit', $arguments);
    }
}
