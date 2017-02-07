<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Layer price filter based on Search API
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends AbstractFilter
{
    /** Price delta for filter  */
    const PRICE_DELTA = 0.001;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Search\Dynamic\Algorithm
     */
    private $priceAlgorithm;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory $algorithmFactory,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $dataProviderFactory,
        array $data = []
    ) {
        $this->_requestVar = 'price';
        $this->priceCurrency = $priceCurrency;
        $this->resource = $resource;
        $this->customerSession = $customerSession;
        $this->priceAlgorithm = $priceAlgorithm;
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set active currency rate for filter
     *
     * @param float $rate
     * @return $this
     */
    public function setCurrencyRate($rate)
    {
        return $this->setData('currency_rate', $rate);
    }

    /**
     * Apply price range filter
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        $filterParams = explode(',', $filter);
        $filter = $this->dataProvider->validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        $this->dataProvider->setInterval($filter);
        $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
        if ($priorFilters) {
            $this->dataProvider->setPriorIntervals($priorFilters);
        }

        list($from, $to) = $filter;

        $this->getLayer()->getProductCollection()->addFieldToFilter(
            'price',
            ['from' => $from, 'to' =>  empty($to) || $from == $to ? $to : $to - self::PRICE_DELTA]
        );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
        );

        return $this;
    }

    /**
     * Retrieve active currency rate for filter
     *
     * @return float
     */
    public function getCurrencyRate()
    {
        $rate = $this->_getData('currency_rate');
        if ($rate === null) {
            $rate = $this->_storeManager->getStore($this->getStoreId())
                ->getCurrentCurrencyRate();
        }
        if (!$rate) {
            $rate = 1;
        }

        return $rate;
    }

    /**
     * Prepare text of range label
     *
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return float|\Magento\Framework\Phrase
     */
    protected function _renderRangeLabel($fromPrice, $toPrice)
    {
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }

            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $facets = $productCollection->getFacetedData($attribute->getAttributeCode());

        $data = [];
        if (count($facets) > 1) { // two range minimum
            foreach ($facets as $key => $aggregation) {
                $count = $aggregation['count'];
                if (strpos($key, '_') === false) {
                    continue;
                }
                $data[] = $this->prepareData($key, $count, $data);
            }
        }

        return $data;
    }

    /**
     * @param float $from
     * @return float
     */
    protected function getTo($from)
    {
        $to = '';
        $interval = $this->dataProvider->getInterval();
        if ($interval && is_numeric($interval[1]) && $interval[1] > $from) {
            $to = $interval[1];
        }
        return $to;
    }

    /**
     * @param float $from
     * @return float
     */
    protected function getFrom($from)
    {
        $to = '';
        $interval = $this->dataProvider->getInterval();
        if ($interval && is_numeric($interval[0]) && $interval[0] < $from) {
            $to = $interval[0];
        }
        return $to;
    }

    /**
     * @param string $key
     * @param int $count
     * @return array
     */
    private function prepareData($key, $count)
    {
        list($from, $to) = explode('_', $key);
        if ($from == '*') {
            $from = $this->getFrom($to);
        }
        if ($to == '*') {
            $to = $this->getTo($to);
        }
        $label = $this->_renderRangeLabel(
            empty($from) ? 0 : $from * $this->getCurrencyRate(),
            empty($to) ? $to : $to * $this->getCurrencyRate()
        );
        $value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();

        $data = [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];

        return $data;
    }
}
