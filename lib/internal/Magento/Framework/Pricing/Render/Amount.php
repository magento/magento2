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
 */
class Amount extends Template implements AmountRenderInterface
{
    /**
     * @var SaleableInterface
     */
    protected $saleableItem;

    /**
     * @var PriceInterface
     */
    protected $price;

    /**
     * @var AdjustmentRenderInterface[]
     */
    protected $adjustmentRenders;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var RendererPool
     */
    protected $rendererPool;

    /**
     * @var AmountInterface
     */
    protected $amount;

    /**
     * @var null|float
     */
    protected $displayValue;

    /**
     * @var string[]
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
     */
    public function setDisplayValue($value)
    {
        $this->displayValue = $value;
    }

    /**
     * @return float
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
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return SaleableInterface
     */
    public function getSaleableItem()
    {
        return $this->saleableItem;
    }

    /**
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getDisplayCurrencyCode()
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }

    /**
     * @return string
     */
    public function getDisplayCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * @return bool
     */
    public function hasAdjustmentsHtml()
    {
        return (bool) count($this->adjustmentsHtml);
    }

    /**
     * @return string
     */
    public function getAdjustmentsHtml()
    {
        return implode('', $this->adjustmentsHtml);
    }

    /**
     * @return string
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
     */
    protected function getAdjustmentRenders()
    {
        return $this->rendererPool->getAdjustmentRenders($this->saleableItem, $this->price);
    }

    /**
     * @param AdjustmentRenderInterface[] $adjustmentRenders
     * @return array
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
     */
    protected function setAdjustmentCssClasses($adjustmentRenders)
    {
        $cssClasses = $this->hasData('css_classes') ? explode(' ', $this->getData('css_classes')) : [];
        $cssClasses = array_merge($cssClasses, array_keys($adjustmentRenders));
        $this->setData('adjustment_css_classes', join(' ', $cssClasses));
        return $this;
    }
}
