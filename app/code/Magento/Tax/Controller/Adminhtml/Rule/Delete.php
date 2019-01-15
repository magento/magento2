<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Tax\Controller\Adminhtml\Rule implements HttpPostActionInterface
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ruleId = (int)$this->getRequest()->getParam('rule');
        try {
            $this->ruleService->deleteById($ruleId);
            $this->messageManager->addSuccess(__('The tax rule has been deleted.'));
            return $resultRedirect->setPath('tax/*/');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addError(__('This rule no longer exists.'));
            return $resultRedirect->setPath('tax/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong deleting this tax rule.'));
        }

        return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
