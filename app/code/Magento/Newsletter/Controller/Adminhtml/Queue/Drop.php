<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Queue\Drop
 *
 * @since 2.0.0
 */
class Drop extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Drop Newsletter queue template
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout('newsletter_queue_preview_popup');
        $this->_view->renderLayout();
    }
}
