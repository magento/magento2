<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

class Grid extends \Magento\Newsletter\Controller\Adminhtml\Subscriber
{
    /**
     * Managing newsletter grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
