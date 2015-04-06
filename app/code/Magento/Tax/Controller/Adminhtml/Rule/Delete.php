<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;

class Delete extends \Magento\Tax\Controller\Adminhtml\Rule
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|void
     */
    public function execute()
    {
        $ruleId = (int)$this->getRequest()->getParam('rule');
        try {
            $this->ruleService->deleteById($ruleId);
            $this->messageManager->addSuccess(__('The tax rule has been deleted.'));
            $this->_redirect('tax/*/');
            return;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addError(__('This rule no longer exists.'));
            $this->_redirect('tax/*/');
            return;
        }
        return $this->getDefaultResult();
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
