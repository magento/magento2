<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Adminhtml\Template;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Newsletter\Controller\Adminhtml\Template;

class Delete extends Template implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Delete newsletter Template
     *
     * @return void
     */
    public function execute()
    {
        $template = $this->_objectManager->create(
            \Magento\Newsletter\Model\Template::class
        )->load(
            $this->getRequest()->getParam('id')
        );
        if ($template->getId()) {
            try {
                $template->delete();
                $this->messageManager->addSuccess(__('The newsletter template has been deleted.'));
                $this->_getSession()->setFormData(false);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t delete this template right now.'));
            }
        }
        $this->_redirect('*/template');
    }
}
