<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Tax\Controller\Adminhtml\Rule
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $taxRuleId = $this->getRequest()->getParam('rule');
        $this->_coreRegistry->register('tax_rule_id', $taxRuleId);
        /** @var \Magento\Backend\Model\Session $backendSession */
        $backendSession = $this->_objectManager->get(\Magento\Backend\Model\Session::class);
        if ($taxRuleId) {
            try {
                $taxRule = $this->ruleService->get($taxRuleId);
                $pageTitle = sprintf("%s", $taxRule->getCode());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $backendSession->unsRuleData();
                $this->messageManager->addError(__('This rule no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('tax/*/');
            }
        } else {
            $pageTitle = __('New Tax Rule');
        }
        $data = $backendSession->getRuleData(true);
        if (!empty($data)) {
            $this->_coreRegistry->register('tax_rule_form_data', $data);
        }
        $breadcrumb = $taxRuleId ? __('Edit Rule') : __('New Rule');
        $resultPage = $this->initResultPage();
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
        $resultPage->getConfig()->getTitle()->prepend(__('Tax Rules'));
        $resultPage->getConfig()->getTitle()->prepend($pageTitle);
        return $resultPage;
    }
}
