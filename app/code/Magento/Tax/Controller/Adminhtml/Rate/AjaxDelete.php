<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Controller\ResultFactory;

class AjaxDelete extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Delete Tax Rate via AJAX
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('tax_calculation_rate_id');
        try {
            $this->_taxRateRepository->deleteById($rateId);
            $responseContent = ['success' => true, 'error_message' => ''];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $responseContent = ['success' => false, 'error_message' => $e->getMessage()];
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('We can\'t delete this tax rate right now.')
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
