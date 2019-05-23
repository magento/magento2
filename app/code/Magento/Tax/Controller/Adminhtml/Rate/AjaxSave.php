<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Tax Rate AjaxSave Controller
 */
class AjaxSave extends \Magento\Tax\Controller\Adminhtml\Rate implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Tax\Model\Calculation\Rate\Converter $taxRateConverter
     * @param \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository
     * @param \Magento\Framework\Escaper|null $escaper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Tax\Model\Calculation\Rate\Converter $taxRateConverter,
        \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository,
        \Magento\Framework\Escaper $escaper = null
    ) {
        $this->escaper = $escaper ?? \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Escaper::class
        );
        parent::__construct($context, $coreRegistry, $taxRateConverter, $taxRateRepository);
    }

    /**
     * Save Tax Rate via AJAX
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \InvalidArgumentException
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
                'code' =>  $this->escaper->escapeHtml($taxRate->getCode()),
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
