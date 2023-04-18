<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;

use Exception;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Controller\Adminhtml\Rule;

class Save extends Rule
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
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
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addError(__('We can\'t save this tax rule right now.'));
            }

            $this->_objectManager->get(Session::class)->setRuleData($postData);
            return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
        return $resultRedirect->setPath('tax/rule');
    }
}
