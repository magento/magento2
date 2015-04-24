<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Controller\RegistryConstants;

class AjaxLoad extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Show Edit Form
     *
     * @return void
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('id');
        try {
            $taxRateDataObject = $this->_taxRateRepository->get($rateId);
            $result_array=$this->extractTaxRateData($taxRateDataObject);
            $responseContent = $this->_objectManager->get(
                'Magento\Framework\Json\Helper\Data'
            )->jsonEncode(
                ['success' => true, 'error_message' => '','result'=>$result_array ]
            );

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = $this->_objectManager->get(
                'Magento\Framework\Json\Helper\Data'
            )->jsonEncode(
                ['success' => false, 'error_message' => $e->getMessage()]
            );
        } catch (\Exception $e) {
            $responseContent = $this->_objectManager->get(
                'Magento\Framework\Json\Helper\Data'
            )->jsonEncode(
                ['success' => false, 'error_message' => __('An error occurred while loading this tax rate.')]
            );
        }

        $this->getResponse()->representJson($responseContent);
    }
}
