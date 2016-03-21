<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Layer Decimal Attribute Filter Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Layer\Filter;

class Decimal extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    /**
     * Resource instance
     *
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var DataProvider\Decimal
     */
    private $dataProvider;

    /**
     * @param ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\DecimalFactory $dataProviderFactory,
        array $data = []
    ) {
        $this->_requestVar = 'decimal';
        $this->priceCurrency = $priceCurrency;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * Apply decimal range filter to product collection
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        parent::apply($request);

        /**
         * Filter must be string: $index, $range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        $filter = explode(',', $filter);
        if (count($filter) != 2) {
            return $this;
        }

        list($index, $range) = $filter;
        if ((int)$index && (int)$range) {
            $this->dataProvider->setRange((int)$range);

            $this->dataProvider->getResource()->applyFilterToCollection($this, $range, $index);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderItemLabel($range, $index), $filter)
            );

            $this->_items = [];
        }

        return $this;
    }

    /**
     * Prepare text of item label
     *
     * @param   int $range
     * @param   float $value
     * @return \Magento\Framework\Phrase
     */
    protected function _renderItemLabel($range, $value)
    {
        $from = $this->priceCurrency->format(($value - 1) * $range, false);
        $to = $this->priceCurrency->format($value * $range, false);
        return __('%1 - %2', $from, $to);
    }

    /**
     * Retrieve data for build decimal filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $range = $this->dataProvider->getRange($this);
        $dbRanges = $this->dataProvider->getRangeItemCounts($range, $this);

        foreach ($dbRanges as $index => $count) {
            $this->itemDataBuilder->addItemData(
                $this->_renderItemLabel($range, $index),
                $index . ',' . $range,
                $count
            );
        }

        return $this->itemDataBuilder->build();
    }
}
