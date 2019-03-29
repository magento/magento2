<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

class Delete extends \Magento\Tax\Controller\Adminhtml\Rule
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ruleId = (int)$this->getRequest()->getParam('rule');
        try {
            $this->ruleService->deleteById($ruleId);
            $this->messageManager->addSuccessMessage(__('The tax rule has been deleted.'));
            return $resultRedirect->setPath('tax/*/');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
            return $resultRedirect->setPath('tax/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong deleting this tax rule.'));
        }

        return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
