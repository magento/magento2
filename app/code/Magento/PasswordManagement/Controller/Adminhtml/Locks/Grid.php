<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Controller\Adminhtml\Locks;

class Grid extends \Magento\PasswordManagement\Controller\Adminhtml\Locks
{
    /**
     * Render AJAX-grid only
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
