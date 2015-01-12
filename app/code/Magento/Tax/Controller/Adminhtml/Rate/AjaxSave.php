<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

class AjaxSave extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Save Tax Rate via AJAX
     *
     * @return void
     */
    public function execute()
    {
        $responseContent = '';
        try {
            $rateData = $this->_processRateData($this->getRequest()->getPost());
            /** @var \Magento\Tax\Api\Data\TaxRateInterface  $taxRate */
            $taxRate = $this->populateTaxRateData($rateData);
            $this->_taxRateRepository->save($taxRate);
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                [
                    'success' => true,
                    'error_message' => '',
                    'tax_calculation_rate_id' => $taxRate->getId(),
                    'code' => $taxRate->getCode(),
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                [
                    'success' => false,
                    'error_message' => $e->getMessage(),
                    'tax_calculation_rate_id' => '',
                    'code' => '',
                ]
            );
        } catch (\Exception $e) {
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                [
                    'success' => false,
                    'error_message' => __('Something went wrong saving this rate.'),
                    'tax_calculation_rate_id' => '',
                    'code' => '',
                ]
            );
        }
        $this->getResponse()->representJson($responseContent);
    }
}
