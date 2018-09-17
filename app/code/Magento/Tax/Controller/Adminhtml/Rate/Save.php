<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Save Rate and Data
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ratePost = $this->getRequest()->getPostValue();
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
                $taxData = $this->_taxRateConverter->populateTaxRateData($ratePost);
                $this->_taxRateRepository->save($taxData);

                $this->messageManager->addSuccess(__('You saved the tax rate.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($ratePost);
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
        return $resultRedirect->setPath('tax/rate');
    }
}
