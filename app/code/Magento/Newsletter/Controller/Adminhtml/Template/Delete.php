<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

class Delete extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Delete newsletter Template
     *
     * @return void
     */
    public function execute()
    {
        $template = $this->_objectManager->create(
            'Magento\Newsletter\Model\Template'
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
