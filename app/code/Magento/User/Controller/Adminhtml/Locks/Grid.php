<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

use Magento\User\Controller\Adminhtml\Locks;

/**
 * Locked users grid
 */
class Grid extends Locks
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
