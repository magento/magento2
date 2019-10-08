<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Controller\Adminhtml\Email\Template;

use Magento\Email\Controller\Adminhtml\Email\Template;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Rendering email template preview.
 */
class Preview extends Template implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Preview transactional email action.
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Email Preview'));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred. The email template can not be opened for preview.')
            );
            $this->_redirect('adminhtml/*/');
        }
    }
}
