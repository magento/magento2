<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

use Magento\Framework\Locale\Resolver;

/**
 * Class \Magento\User\Controller\Adminhtml\User\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
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
            $model->setInterfaceLocale(Resolver::DEFAULT_LOCALE);
        }

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
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Users'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New User'));
        $this->_view->renderLayout();
    }
}
