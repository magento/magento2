<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User;

/**
 * Class \Magento\User\Controller\Adminhtml\User\RoleGrid
 *
 * @since 2.0.0
 */
class RoleGrid extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
