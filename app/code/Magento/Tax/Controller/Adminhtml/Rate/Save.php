<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;

class Save extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Save Rate and Data
     *
     * @return void
     */
    public function execute()
    {
        $ratePost = $this->getRequest()->getPost();
        if ($ratePost) {
            $rateId = $this->getRequest()->getParam('tax_calculation_rate_id');
            if ($rateId) {
                try {
                    $this->_taxRateRepository->get($rateId);
                } catch (NoSuchEntityException $e) {
                    unset($ratePost['tax_calculation_rate_id']);
                }
            }

            try {
                $taxData = $this->populateTaxRateData($ratePost);
                $this->_taxRateRepository->save($taxData);

                $this->messageManager->addSuccess(__('The tax rate has been saved.'));
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($ratePost);
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return;
        }
        $this->getResponse()->setRedirect($this->getUrl('tax/rate'));
    }
}
