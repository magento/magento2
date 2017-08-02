<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Ui\Component\Design\Config;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Data Provider for 'design_config_form' and 'design_config_listing' components
 *
 * @api
 * @since 2.1.0
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var StoreManagerInterface
     * @since 2.1.0
     */
    protected $storeManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.1.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->storeManager = $storeManager;
    }

    /**
     * Get data
     *
     * @return array
     * @since 2.1.0
     */
    public function getData()
    {
        if ($this->storeManager->isSingleStoreMode()) {
            $websites = $this->storeManager->getWebsites();
            $singleStoreWebsite = array_shift($websites);

            $this->addFilter(
                $this->filterBuilder->setField('store_website_id')
                    ->setValue($singleStoreWebsite->getId())
                    ->create()
            );
            $this->addFilter(
                $this->filterBuilder->setField('store_group_id')
                    ->setConditionType('null')
                    ->create()
            );
        }
        $data = parent::getData();
        foreach ($data['items'] as & $item) {
            $item += ['default' => __('Global')];
        }

        return $data;
    }
}
