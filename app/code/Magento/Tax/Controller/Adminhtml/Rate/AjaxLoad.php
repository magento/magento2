<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Controller\Adminhtml\Rate;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Controller\Adminhtml\Rate;

class AjaxLoad extends Rate
{
    /**
     * Json needed for the Ajax Edit Form
     *
     * @return ResultJson
     * @throws InvalidArgumentException
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('id');
        try {
            /* @var TaxRateInterface $taxRateDataObject */
            $taxRateDataObject = $this->_taxRateRepository->get($rateId);
            /* @var array $resultArray */
            $resultArray = $this->_taxRateConverter->createArrayFromServiceObject($taxRateDataObject, true);

            $responseContent = [
                'success' => true,
                'error_message' => '',
                'result' => $resultArray,
            ];
        } catch (NoSuchEntityException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('An error occurred while loading this tax rate.'),
            ];
        }

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
