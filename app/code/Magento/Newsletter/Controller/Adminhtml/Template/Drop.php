<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
