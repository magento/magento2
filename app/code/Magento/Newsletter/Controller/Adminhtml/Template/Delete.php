<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

class Delete extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Delete newsletter Template
     *
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Page not found.'));
        }

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
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t delete this template right now.'));
            }
        }
        $this->_redirect('*/template');
    }
}
