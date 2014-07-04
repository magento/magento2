<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\User\Controller\Adminhtml;

class User extends \Magento\Backend\App\AbstractAction
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\User\Model\UserFactory $userFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_userFactory = $userFactory;
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_User::system_acl_users'
        )->_addBreadcrumb(
            __('System'),
            __('System')
        )->_addBreadcrumb(
            __('Permissions'),
            __('Permissions')
        )->_addBreadcrumb(
            __('Users'),
            __('Users')
        );
        return $this;
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Users'));
        $this->_initAction();
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Users'));

        $userId = $this->getRequest()->getParam('user_id');
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        if ($userId) {
            $model->load($userId);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This user no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        } else {
            $model->setInterfaceLocale(\Magento\Framework\Locale\ResolverInterface::DEFAULT_LOCALE);
        }

        $this->_title->add($model->getId() ? $model->getName() : __('New User'));

        // Restore previously entered form data from session
        $data = $this->_session->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('permissions_user', $model);

        if (isset($userId)) {
            $breadcrumb = __('Edit User');
        } else {
            $breadcrumb = __('New User');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }

    /**
     * AJAX customer validation action
     *
     * @return void
     */
    public function validateAction()
    {
        $response = new \Magento\Framework\Object();
        $response->setError(0);
        $errors = null;
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPost();
        try {
            /** @var $model \Magento\User\Model\User */
            $model = $this->_userFactory->create()->load($userId);
            $model->setData($this->_getAdminUserData($data));
            $errors = $model->validate();
        } catch (\Magento\Framework\Model\Exception $exception) {
            /* @var $error Error */
            foreach ($exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR) as $error) {
                $errors[] = $error->getText();
            }
        }

        if ($errors !== true && !empty($errors)) {
            foreach ($errors as $error) {
                $this->messageManager->addError($error);
            }
            $response->setError(1);
            $this->_view->getLayout()->initMessages();
            $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->representJson($response->toJson());
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        $userId = (int)$this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPost();
        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        /** @var $model \Magento\User\Model\User */
        $model = $this->_userFactory->create()->load($userId);
        if ($userId && $model->isObjectNew()) {
            $this->messageManager->addError(__('This user no longer exists.'));
            $this->_redirect('adminhtml/*/');
            return;
        }
        $model->setData($this->_getAdminUserData($data));
        $uRoles = $this->getRequest()->getParam('roles', array());
        if (count($uRoles)) {
            $model->setRoleId($uRoles[0]);
        }

        $currentUser = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();
        if ($userId == $currentUser->getId() && $this->_objectManager->get(
            'Magento\Framework\Locale\Validator'
        )->isValid(
            $data['interface_locale']
        )
        ) {
            $this->_objectManager->get(
                'Magento\Backend\Model\Locale\Manager'
            )->switchBackendInterfaceLocale(
                $data['interface_locale']
            );
        }

        try {
            $model->save();
            $this->messageManager->addSuccess(__('You saved the user.'));
            $this->_getSession()->setUserData(false);
            $this->_redirect('adminhtml/*/');
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addMessages($e->getMessages());
            $this->_getSession()->setUserData($data);
            $this->_redirect('adminhtml/*/edit', array('_current' => true));
        }
    }

    /**
     * Retrieve well-formed admin user data from the form input
     *
     * @param array $data
     * @return array
     */
    protected function _getAdminUserData(array $data)
    {
        if (isset($data['password']) && $data['password'] === '') {
            unset($data['password']);
        }
        if (!isset($data['password'])
            && isset($data['password_confirmation'])
            && $data['password_confirmation'] === ''
        ) {
            unset($data['password_confirmation']);
        }
        return $data;
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $currentUser = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();

        if ($userId = $this->getRequest()->getParam('user_id')) {
            if ($currentUser->getId() == $userId) {
                $this->messageManager->addError(__('You cannot delete your own account.'));
                $this->_redirect('adminhtml/*/edit', array('user_id' => $userId));
                return;
            }
            try {
                /** @var \Magento\User\Model\User $model */
                $model = $this->_userFactory->create();
                $model->setId($userId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the user.'));
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', array('user_id' => $this->getRequest()->getParam('user_id')));
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a user to delete.'));
        $this->_redirect('adminhtml/*/');
    }

    /**
     * @return void
     */
    public function rolesGridAction()
    {
        $userId = $this->getRequest()->getParam('user_id');
        /** @var \Magento\User\Model\User $model */
        $model = $this->_userFactory->create();

        if ($userId) {
            $model->load($userId);
        }
        $this->_coreRegistry->register('permissions_user', $model);
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function roleGridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_User::acl_users');
    }
}
