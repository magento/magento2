<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing\Render;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;

/**
 * Price amount renderer
 *
 * @method string getAdjustmentCssClasses()
 * @method string getDisplayLabel()
 * @method string getPriceId()
 * @method bool getIncludeContainer()
 * @method bool getSkipAdjustments()
 * @since 2.0.0
 */
class Amount extends Template implements AmountRenderInterface
{
    /**
     * @var SaleableInterface
     * @since 2.0.0
     */
    protected $saleableItem;

    /**
     * @var PriceInterface
     * @since 2.0.0
     */
    protected $price;

    /**
     * @var AdjustmentRenderInterface[]
     * @since 2.0.0
     */
    protected $adjustmentRenders;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @var RendererPool
     * @since 2.0.0
     */
    protected $rendererPool;

    /**
     * @var AmountInterface
     * @since 2.0.0
     */
    protected $amount;

    /**
     * @var null|float
     * @since 2.0.0
     */
    protected $displayValue;

    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $adjustmentsHtml = [];

    /**
     * @param Template\Context $context
     * @param AmountInterface $amount
     * @param PriceCurrencyInterface $priceCurrency
     * @param RendererPool $rendererPool
     * @param SaleableInterface $saleableItem
     * @param \Magento\Framework\Pricing\Price\PriceInterface $price
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        AmountInterface $amount,
        PriceCurrencyInterface $priceCurrency,
        RendererPool $rendererPool,
        SaleableInterface $saleableItem = null,
        PriceInterface $price = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->amount = $amount;
        $this->saleableItem = $saleableItem;
        $this->price = $price;
        $this->priceCurrency = $priceCurrency;
        $this->rendererPool = $rendererPool;
    }

    /**
     * @param float $value
     * @return void
     * @since 2.0.0
     */
    public function setDisplayValue($value)
    {
        $this->displayValue = $value;
    }

    /**
     * @return float
     * @since 2.0.0
     */
    public function getDisplayValue()
    {
        if ($this->displayValue !== null) {
            return $this->displayValue;
        } else {
            return $this->getAmount()->getValue();
        }
    }

    /**
     * @return AmountInterface
     * @since 2.0.0
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return SaleableInterface
     * @since 2.0.0
     */
    public function getSaleableItem()
    {
        return $this->saleableItem;
    }

    /**
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     * @since 2.0.0
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getDisplayCurrencyCode()
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getDisplayCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function hasAdjustmentsHtml()
    {
        return (bool) count($this->adjustmentsHtml);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAdjustmentsHtml()
    {
        return implode('', $this->adjustmentsHtml);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $adjustmentRenders = $this->getAdjustmentRenders();
        if ($adjustmentRenders) {
            $adjustmentHtml = $this->getAdjustments($adjustmentRenders);
            if (!$this->hasSkipAdjustments() ||
                ($this->hasSkipAdjustments() && $this->getSkipAdjustments() == false)) {
                $this->adjustmentsHtml = $adjustmentHtml;
            }
        }
        $html = parent::_toHtml();
        return $html;
    }

    /**
     * @return AdjustmentRenderInterface[]
     * @since 2.0.0
     */
    protected function getAdjustmentRenders()
    {
        return $this->rendererPool->getAdjustmentRenders($this->saleableItem, $this->price);
    }

    /**
     * @param AdjustmentRenderInterface[] $adjustmentRenders
     * @return array
     * @since 2.0.0
     */
    protected function getAdjustments($adjustmentRenders)
    {
        $this->setAdjustmentCssClasses($adjustmentRenders);
        $data = $this->getData();
        $adjustments = [];
        foreach ($adjustmentRenders as $adjustmentRender) {
            if ($this->getAmount()->getAdjustmentAmount($adjustmentRender->getAdjustmentCode()) !== false) {
                $html = $adjustmentRender->render($this, $data);
                if (trim($html)) {
                    $adjustments[$adjustmentRender->getAdjustmentCode()] = $html;
                }
            }
        }
        return $adjustments;
    }

    /**
     * Format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return float
     * @since 2.0.0
     */
    public function formatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->priceCurrency->format($amount, $includeContainer, $precision);
    }

    /**
     * @param AdjustmentRenderInterface[] $adjustmentRenders
     * @return array
     * @since 2.0.0
     */
    protected function setAdjustmentCssClasses($adjustmentRenders)
    {
        $cssClasses = $this->hasData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
        $cssClasses = array_merge($cssClasses, array_keys($adjustmentRenders));
        $this->setData('adjustment_css_classes', join(' ', $cssClasses));
        return $this;
    }
}
