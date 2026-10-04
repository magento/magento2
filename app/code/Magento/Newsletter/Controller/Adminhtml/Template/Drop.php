<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Drop extends \Magento\Newsletter\Controller\Adminhtml\Template implements HttpPostActionInterface
{
    /**
     * Drop Newsletter Template
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout('newsletter_template_preview_popup');
        $this->_view->renderLayout();
    }
}
