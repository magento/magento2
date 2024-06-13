<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
