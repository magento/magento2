<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Exception;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Tax\Controller\Adminhtml\Rate;

class Save extends Rate
{
    /**
     * Save Rate and Data
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        /** @var ResultRedirect $resultRedirect */
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
            } catch (LocalizedException $e) {
                $this->_objectManager->get(Session::class)->setFormData($ratePost);
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            return $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
        return $resultRedirect->setPath('tax/rate');
    }
}
