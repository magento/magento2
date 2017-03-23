<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;

/**
 * Adjustment render abstract
 *
 * @method string getZone()
 */
abstract class AbstractAdjustment extends Template implements AdjustmentRenderInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var AmountRenderInterface
     */
    protected $amountRender;

    /**
     * @param Template\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * @param AmountRenderInterface $amountRender
     * @param array $arguments
     * @return string
     */
    public function render(AmountRenderInterface $amountRender, array $arguments = [])
    {
        $this->amountRender = $amountRender;

        $origArguments = $this->getData();
        $this->setData(array_replace($origArguments, $arguments));

        $html = $this->apply();

        // restore original block arguments
        $this->setData($origArguments);
        return $html;
    }

    /**
     * @return AmountRenderInterface
     */
    public function getAmountRender()
    {
        return $this->amountRender;
    }

    /**
     * @param string $priceCode
     * @return PriceInterface
     */
    public function getPriceType($priceCode)
    {
        return $this->getSaleableItem()->getPriceInfo()->getPrice($priceCode);
    }

    /**
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    public function getPrice()
    {
        return $this->amountRender->getPrice();
    }

    /**
     * @return SaleableInterface
     */
    public function getSaleableItem()
    {
        return $this->amountRender->getSaleableItem();
    }

    /**
     * Format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return string
     */
    public function formatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->priceCurrency->format($amount, $includeContainer, $precision);
    }

    /**
     * Convert and format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return string
     */
    public function convertAndFormatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->priceCurrency->convertAndFormat($amount, $includeContainer, $precision);
    }

    /**
     * @return \Magento\Framework\Pricing\Adjustment\AdjustmentInterface
     */
    public function getAdjustment()
    {
        return $this->getSaleableItem()->getPriceInfo()->getAdjustment($this->getAdjustmentCode());
    }

    /**
     * @return string
     */
    abstract protected function apply();
}
