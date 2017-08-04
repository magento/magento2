<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;

/**
 * MSRP price model
 */
class MsrpPrice extends FinalPrice implements MsrpPriceInterface
{
    /**
     * Price type MSRP
     */
    const PRICE_CODE = 'msrp_price';

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpData;

    /**
     * @var \Magento\Msrp\Model\Config
     */
    protected $config;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Msrp\Helper\Data $msrpData
     * @param \Magento\Msrp\Model\Config $config
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Msrp\Helper\Data $msrpData,
        \Magento\Msrp\Model\Config $config
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->msrpData = $msrpData;
        $this->config = $config;
    }

    /**
     * Returns whether the MSRP should be shown on gesture
     *
     * @return bool
     */
    public function isShowPriceOnGesture()
    {
        return $this->msrpData->isShowPriceOnGesture($this->product);
    }

    /**
     * Get Msrp message for price
     *
     * @return string
     */
    public function getMsrpPriceMessage()
    {
        return $this->msrpData->getMsrpPriceMessage($this->product);
    }

    /**
     * Check if Minimum Advertised Price is enabled
     *
     * @return bool
     */
    public function isMsrpEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * Check if can apply Minimum Advertise price to product
     *
     * @param Product $product
     * @return bool
     */
    public function canApplyMsrp(Product $product)
    {
        return $this->msrpData->canApplyMsrp($product);
    }

    /**
     * @param Product $product
     * @return bool|float
     */
    public function isMinimalPriceLessMsrp(Product $product)
    {
        return $this->msrpData->isMinimalPriceLessMsrp($product);
    }
}
