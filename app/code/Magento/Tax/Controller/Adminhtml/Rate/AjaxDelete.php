<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

class AjaxDelete extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Delete Tax Rate via AJAX
     *
     * @return void
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('tax_calculation_rate_id');
        try {
            $this->_taxRateRepository->deleteById($rateId);
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                ['success' => true, 'error_message' => '']
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                ['success' => false, 'error_message' => $e->getMessage()]
            );
        } catch (\Exception $e) {
            $responseContent = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                ['success' => false, 'error_message' => __('An error occurred while deleting this tax rate.')]
            );
        }
        $this->getResponse()->representJson($responseContent);
    }
}
