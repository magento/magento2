<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

/**
 * Locked users grid
 */
class Grid extends \Magento\User\Controller\Adminhtml\Locks
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
