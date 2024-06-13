<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\User\Controller\Adminhtml\User;

class RolesGrid extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @return void
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
