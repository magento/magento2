<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
