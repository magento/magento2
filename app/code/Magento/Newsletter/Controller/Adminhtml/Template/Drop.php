<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

class Drop extends \Magento\Newsletter\Controller\Adminhtml\Template
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
