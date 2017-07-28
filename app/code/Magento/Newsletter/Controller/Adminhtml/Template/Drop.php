<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Template\Drop
 *
 * @since 2.0.0
 */
class Drop extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Drop Newsletter Template
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout('newsletter_template_preview_popup');
        $this->_view->renderLayout();
    }
}
