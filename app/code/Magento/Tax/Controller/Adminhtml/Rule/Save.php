<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Tax\Controller\Adminhtml\Rule
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $postData = $this->getRequest()->getPostValue();
        if ($postData) {
            $postData['calculate_subtotal'] = $this->getRequest()->getParam('calculate_subtotal', 0);
            $taxRule = $this->populateTaxRule($postData);
            try {
                $taxRule = $this->ruleService->save($taxRule);

                $this->messageManager->addSuccess(__('You saved the tax rule.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('tax/*/edit', ['rule' => $taxRule->getId()]);
                }
                return $resultRedirect->setPath('tax/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t save this tax rule right now.'));
            }

            $this->_objectManager->get('Magento\Backend\Model\Session')->setRuleData($postData);
            return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
        return $resultRedirect->setPath('tax/rule');
    }
}
