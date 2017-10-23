<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Layer\Filter;

/**
 * Layer price filter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    /**
     * Resource instance
     *
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    protected $_resource;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer filter price algorithm
     *
     * @var \Magento\Framework\Search\Dynamic\Algorithm
     */
    protected $_priceAlgorithm;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Dynamic\AlgorithmFactory
     */
    private $algorithmFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @param ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Search\Dynamic\Algorithm $priceAlgorithm
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param Dynamic\AlgorithmFactory $algorithmFactory
     * @param DataProvider\PriceFactory $dataProviderFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        $this->priceCurrency = $priceCurrency;
        $this->_resource = $resource;
        $this->_customerSession = $customerSession;
        $this->_priceAlgorithm = $priceAlgorithm;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
        $this->_requestVar = 'price';
        $this->algorithmFactory = $algorithmFactory;
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * Apply price range filter
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
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

        //validate filter
        $filterParams = explode(',', $filter);
        $filter = $this->dataProvider->validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        list($from, $to) = $filter;

        $this->dataProvider->setInterval([$from, $to]);

        $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
        if ($priorFilters) {
            $this->dataProvider->setPriorIntervals($priorFilters);
        }

        $this->_applyPriceRange();
        $this->getLayer()
            ->getState()
            ->addFilter(
                $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
            );

        return $this;
    }

    /**
     * Retrieve active customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = $this->_getData('customer_group_id');
        if (is_null($customerGroupId)) {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
        }

        return $customerGroupId;
    }

    /**
     * Set active customer group id for filter
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData('customer_group_id', $customerGroupId);
    }

    /**
     * Retrieve active currency rate for filter
     *
     * @return float
     */
    public function getCurrencyRate()
    {
        $rate = $this->_getData('currency_rate');
        if (is_null($rate)) {
            $rate = $this->_storeManager->getStore($this->getStoreId())
                ->getCurrentCurrencyRate();
        }
        if (!$rate) {
            $rate = 1;
        }

        return $rate;
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
     * Get filter value for reset current filter state
     *
     * @return null|string
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * Get 'clear price' link text
     *
     * @return \Magento\Framework\Phrase|bool
     */
    public function getClearLinkText()
    {
        if ($this->dataProvider->getPriorIntervals()
        ) {
            return __('Clear Price');
        }

        return parent::getClearLinkText();
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
        } elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()
        ) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }

            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }

    /**
     * Get additional request param data
     *
     * @return string
     */
    protected function _getAdditionalRequestData()
    {
        $result = '';
        $appliedInterval = $this->dataProvider->getInterval();
        if ($appliedInterval) {
            $result = ',' . $appliedInterval[0] . '-' . $appliedInterval[1];
            $priorIntervals = $this->getResetValue();
            if ($priorIntervals) {
                $result .= ',' . $priorIntervals;
            }
        }

        return $result;
    }

    /**
     * Get data for build price filter items
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $algorithm = $this->algorithmFactory->create();

        return $algorithm->getItemsData((array)$this->dataProvider->getInterval(), $this->dataProvider->getAdditionalRequestData());
    }

    /**
     * Apply price range filter to collection
     *
     * @return $this
     */
    protected function _applyPriceRange()
    {
        $this->dataProvider->getResource()->applyPriceRange($this, $this->dataProvider->getInterval());

        return $this;
    }
}
