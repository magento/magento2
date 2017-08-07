<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rule;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Tax\Model\Rate\Provider as RatesProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Model\Calculation\Rate;

/**
 * Class AjaxLoadRates is intended to load existing
 * Tax rates as options for a select element.
 * @since 2.2.0
 */
class AjaxLoadRates extends Action
{
    /**
     * @var RatesProvider
     * @since 2.2.0
     */
    private $ratesProvider;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.2.0
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RatesProvider $ratesProvider
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RatesProvider $ratesProvider
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->ratesProvider = $ratesProvider;
    }

    /**
     * Get rates page via AJAX
     *
     * @return Json
     * @since 2.2.0
     */
    public function execute()
    {
        $ratesPage = (int) $this->getRequest()->getParam('p');
        $ratesFilter = trim($this->getRequest()->getParam('s'));

        try {
            if (!empty($ratesFilter)) {
                $this->searchCriteriaBuilder->addFilter(
                    Rate::KEY_CODE,
                    '%'.$ratesFilter.'%',
                    'like'
                );
            }

            $searchCriteria = $this->searchCriteriaBuilder
                ->setPageSize($this->ratesProvider->getPageSize())
                ->setCurrentPage($ratesPage)
                ->create();

            $options = $this->ratesProvider->toOptionArray($searchCriteria);

            $response = [
                'success' => true,
                'errorMessage' => '',
                'result'=> $options,
            ];
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'errorMessage' => __('An error occurred while loading tax rates.')
            ];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);

        return $resultJson;
    }
}
