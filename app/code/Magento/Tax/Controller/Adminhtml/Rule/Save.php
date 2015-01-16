<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;


class Save extends \Magento\Tax\Controller\Adminhtml\Rule
{
    /**
     * @return void
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            $postData['calculate_subtotal'] = $this->getRequest()->getParam('calculate_subtotal', 0);
            $taxRule = $this->populateTaxRule($postData);
            try {
                $taxRule = $this->ruleService->save($taxRule);

                $this->messageManager->addSuccess(__('The tax rule has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('tax/*/edit', ['rule' => $taxRule->getId()]);
                    return;
                }

                $this->_redirect('tax/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong saving this tax rule.'));
            }

            $this->_objectManager->get('Magento\Backend\Model\Session')->setRuleData($postData);
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return;
        }
        $this->getResponse()->setRedirect($this->getUrl('tax/rule'));
    }
}
