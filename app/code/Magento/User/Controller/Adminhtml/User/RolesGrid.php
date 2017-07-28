<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

/**
 * Class \Magento\User\Controller\Adminhtml\User\RolesGrid
 *
 * @since 2.0.0
 */
class RolesGrid extends \Magento\User\Controller\Adminhtml\User
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
        }
        $this->_coreRegistry->register('permissions_user', $model);
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
