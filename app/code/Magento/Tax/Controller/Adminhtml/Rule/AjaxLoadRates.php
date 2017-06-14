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

class AjaxLoadRates extends Action
{
    const PAGE_PARAM_NAME = 'p';
    const FILTER_PARAM_NAME = 's';
    const SEARCH_CONDITION_TYPE = 'like';

    /**
     * @var RatesProvider
     */
    private $ratesProvider;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RatesProvider $ratesProvider
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
     */
    public function execute()
    {
        $ratesPage = (int) $this->getRequest()->getParam(self::PAGE_PARAM_NAME);
        $ratesFilter = trim($this->getRequest()->getParam(self::FILTER_PARAM_NAME));

        try {
            if (!empty($ratesFilter)) {
                $this->searchCriteriaBuilder->addFilter(
                    Rate::KEY_CODE,
                    '%'.$ratesFilter.'%',
                    self::SEARCH_CONDITION_TYPE
                );
            }

            $searchCriteria = $this->searchCriteriaBuilder
                ->setPageSize(RatesProvider::PAGE_SIZE)
                ->setCurrentPage($ratesPage)
                ->create();

            $options = $this->ratesProvider->toOptionArray($searchCriteria);

            $response = [
                'success' => true,
                'errorMessage' => '',
                'result'=>$options,
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
