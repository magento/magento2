<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Layer decimal filter
 */
class Decimal extends AbstractFilter
{
    /** Decimal delta for filter  */
    private const DECIMAL_DELTA = 0.001;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal
     */
    private $resource;

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
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
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\DecimalFactory $filterDecimalFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->resource = $filterDecimalFactory->create();
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Apply price range filter
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
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

        list($from, $to) = explode('-', $filter);

        // When the range is 10-20 we only need to get products that are in the 10-19.99 range.
        $toValue = $to;
        if (!empty($toValue) && $from !== $toValue) {
            $toValue -= self::DECIMAL_DELTA;
        }

        $this->getLayer()
            ->getProductCollection()
            ->addFieldToFilter(
                $this->getAttributeModel()->getAttributeCode(),
                ['from' => $from, 'to' => $toValue]
            );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
        );

        return $this;
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $productSize = $productCollection->getSize();
        $facets = $productCollection->getFacetedData($attribute->getAttributeCode());

        $data = [];
        foreach ($facets as $key => $aggregation) {
            $count = $aggregation['count'];
            if (!$this->isOptionReducesResults($count, $productSize)) {
                continue;
            }
            list($from, $to) = explode('_', $key);
            if ($from == '*') {
                $from = '';
            }
            if ($to == '*') {
                $to = '';
            }
            $label = $this->renderRangeLabel(empty($from) ? 0 : $from, $to);
            $value = $from . '-' . $to;

            $data[] = [
                'label' => $label,
                'value' => $value,
                'count' => $count,
                'from' => $from,
                'to' => $to
            ];
        }

        return $data;
    }

    /**
     * Prepare text of range label
     *
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return \Magento\Framework\Phrase
     */
    protected function renderRangeLabel($fromPrice, $toPrice)
    {
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }
            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }
}
