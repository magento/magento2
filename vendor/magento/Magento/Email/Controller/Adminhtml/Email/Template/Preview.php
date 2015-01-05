<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
