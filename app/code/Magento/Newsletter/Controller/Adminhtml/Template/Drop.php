<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

use Magento\Framework\Exception\NotFoundException;

class Drop extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Drop Newsletter Template
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        $this->_view->loadLayout('newsletter_template_preview_popup');
        $this->_view->renderLayout();
    }
}
