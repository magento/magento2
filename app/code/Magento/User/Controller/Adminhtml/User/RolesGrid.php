<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

use Magento\User\Controller\Adminhtml\User;
use Magento\User\Model\User as ModelUser;

class RolesGrid extends User
{
    /**
     * @return void
     */
    public function execute()
    {
        $userId = $this->getRequest()->getParam('user_id');
        /** @var ModelUser $model */
        $model = $this->_userFactory->create();

        if ($userId) {
            $model->load($userId);
        }
        $this->_coreRegistry->register('permissions_user', $model);
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
