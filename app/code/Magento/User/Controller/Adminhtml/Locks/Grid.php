<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

/**
 * Locked users grid
 * @since 2.0.0
 */
class Grid extends \Magento\User\Controller\Adminhtml\Locks
{
    /**
     * Render AJAX-grid only
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
