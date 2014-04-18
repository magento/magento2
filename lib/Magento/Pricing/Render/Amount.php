<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Pricing
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Pricing\Render;

use Magento\Pricing\Amount\AmountInterface;
use Magento\Pricing\Object\SaleableInterface;
use Magento\Pricing\Price\PriceInterface;
use Magento\View\Element\Template;
use Magento\Pricing\PriceCurrencyInterface;

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
     * @var float
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
     * @param \Magento\Pricing\Price\PriceInterface $price
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
     * @return \Magento\Pricing\Price\PriceInterface
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $code
     * @param string $html
     * @return void
     */
    public function addAdjustmentHtml($code, $html)
    {
        $this->adjustmentsHtml[$code] = $html;
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
        // apply Price Adjustment Renders if available
        $adjustmentRenders = $this->getApplicableAdjustmentRenders();
        if ($adjustmentRenders) {
            $this->applyAdjustments($adjustmentRenders);
        }
        $html = parent::_toHtml();
        return $html;
    }

    /**
     * Collect correspondent Price Adjustment Renders
     *
     * @return AdjustmentRenderInterface[]
     */
    protected function getApplicableAdjustmentRenders()
    {
        if (!$this->hasSkipAdjustments()) {
            return $this->getAdjustmentRenders();
        } else {
            return [];
        }
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
     * @return void
     */
    protected function applyAdjustments($adjustmentRenders)
    {
        $this->setAdjustmentCssClasses($adjustmentRenders);
        $data = $this->getData();
        foreach ($adjustmentRenders as $adjustmentRender) {
            $adjustmentRender->render($this, $data);
        }
    }

    /**
     * Convert and format price value
     *
     * @param float $amount
     * @param bool $includeContainer
     * @param int $precision
     * @return float
     */
    public function convertAndFormatCurrency(
        $amount,
        $includeContainer = true,
        $precision = PriceCurrencyInterface::DEFAULT_PRECISION
    ) {
        return $this->priceCurrency->convertAndFormat($amount, $includeContainer, $precision);
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
