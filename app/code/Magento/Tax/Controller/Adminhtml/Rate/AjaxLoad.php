<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

class AjaxLoad extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Json needed for the Ajax Edit Form
     *
     * @return void
     */
    public function execute()
    {
        $rateId = (int)$this->getRequest()->getParam('id');
        try {
            /* @var \Magento\Tax\Api\Data\TaxRateInterface */
            $taxRateDataObject = $this->_taxRateRepository->get($rateId);
            /* @var array */
            $resultArray= $this->_taxRateConverter->createArrayFromServiceObject($taxRateDataObject, true);

            $responseContent = [
                'success' => true,
                'error_message' => '',
                'result'=>$resultArray,
                ];
        } catch (NoSuchEntityException $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => __('An error occurred while loading this tax rate.'),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
