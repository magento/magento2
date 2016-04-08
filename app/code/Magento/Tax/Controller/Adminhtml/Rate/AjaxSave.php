<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Controller\ResultFactory;

class AjaxSave extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Save Tax Rate via AJAX
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $rateData = $this->_processRateData($this->getRequest()->getPostValue());
            /** @var \Magento\Tax\Api\Data\TaxRateInterface  $taxRate */
            $taxRate = $this->_taxRateConverter->populateTaxRateData($rateData);
            $this->_taxRateRepository->save($taxRate);
            $responseContent = [
                'success' => true,
                'error_message' => '',
                'tax_calculation_rate_id' => $taxRate->getId(),
                'code' =>  htmlspecialchars($taxRate->getCode()),
            ];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage(),
                'tax_calculation_rate_id' => '',
                'code' => '',
            ];
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('We can\'t save this rate right now.'),
                'tax_calculation_rate_id' => '',
                'code' => '',
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
