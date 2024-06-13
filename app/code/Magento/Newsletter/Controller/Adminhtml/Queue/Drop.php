<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

class Drop extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Drop Newsletter queue template
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout('newsletter_queue_preview_popup');
        $this->_view->renderLayout();
    }
}
