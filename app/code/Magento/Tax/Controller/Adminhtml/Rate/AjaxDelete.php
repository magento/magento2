<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Exception;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Controller\Adminhtml\Rate;

class AjaxDelete extends Rate
{
    /**
     * Delete Tax Rate via AJAX
     *
     * @return ResultJson
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('tax_calculation_rate_id');
        try {
            $this->_taxRateRepository->deleteById($rateId);
            $responseContent = ['success' => true, 'error_message' => ''];
        } catch (LocalizedException $e) {
            $responseContent = ['success' => false, 'error_message' => $e->getMessage()];
        } catch (Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('We can\'t delete this tax rate right now.')
            ];
        }

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
