<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

class Preview extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * Preview transactional email action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout('systemPreview');
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred. The email template can not be opened for preview.'));
            $this->_redirect('adminhtml/*/');
        }
    }
}
