<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\Price;

use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class \Magento\Catalog\Model\Layer\Filter\Price\Render
 *
 * @since 2.0.0
 */
class Render
{
    const XML_PATH_ONE_PRICE_INTERVAL = 'catalog/layered_navigation/one_price_interval';

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    private $priceCurrency;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    private $scopeConfig;

    /**
     * @var DataBuilder
     * @since 2.0.0
     */
    private $itemDataBuilder;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeConfigInterface $scopeConfig
     * @param DataBuilder $itemDataBuilder
     * @since 2.0.0
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        ScopeConfigInterface $scopeConfig,
        DataBuilder $itemDataBuilder
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig = $scopeConfig;
        $this->itemDataBuilder = $itemDataBuilder;
    }

    /**
     * Prepare text of range label
     *
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return float|\Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function renderRangeLabel($fromPrice, $toPrice)
    {
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        $priceInterval = $this->scopeConfig->getValue(
            self::XML_PATH_ONE_PRICE_INTERVAL,
            ScopeInterface::SCOPE_STORE
        );
        if ($toPrice === '') {
            return __('%1 and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $priceInterval) {
            return $formattedFromPrice;
        } else {
            if ($fromPrice != $toPrice) {
                $toPrice -= .01;
            }

            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }

    /**
     * @param int $range
     * @param int[] $dbRanges
     * @return array
     * @since 2.0.0
     */
    public function renderRangeData($range, $dbRanges)
    {
        if (empty($dbRanges)) {
            return [];
        }
        $lastIndex = array_keys($dbRanges);
        $lastIndex = $lastIndex[count($lastIndex) - 1];

        foreach ($dbRanges as $index => $count) {
            $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
            $toPrice = $index == $lastIndex ? '' : $index * $range;
            $this->itemDataBuilder->addItemData(
                $this->renderRangeLabel($fromPrice, $toPrice),
                $fromPrice . '-' . $toPrice,
                $count
            );
        }
        return $this->itemDataBuilder->build();
    }
}
