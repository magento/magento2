<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product options abstract type block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Catalog\Block\Product\View\Options;

use Magento\Catalog\Pricing\Price\CalculateCustomOptionCatalogRule;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Product options section abstract block.
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractOptions extends \Magento\Framework\View\Element\Template
{
    /**
     * Product object
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Product option object
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $_option;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogHelper;

    /**
     * @var CalculateCustomOptionCatalogRule
     */
    private $calculateCustomOptionCatalogRule;

    /**
     * @var CalculatorInterface
     */
    private $calculator;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param array $data
     * @param CalculateCustomOptionCatalogRule|null $calculateCustomOptionCatalogRule
     * @param CalculatorInterface|null $calculator
     * @param PriceCurrencyInterface|null $priceCurrency
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        array $data = [],
        CalculateCustomOptionCatalogRule $calculateCustomOptionCatalogRule = null,
        CalculatorInterface $calculator = null,
        PriceCurrencyInterface $priceCurrency = null
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->_catalogHelper = $catalogData;
        $this->calculateCustomOptionCatalogRule = $calculateCustomOptionCatalogRule
            ?? ObjectManager::getInstance()->get(CalculateCustomOptionCatalogRule::class);
        $this->calculator = $calculator
            ?? ObjectManager::getInstance()->get(CalculatorInterface::class);
        $this->priceCurrency = $priceCurrency
            ?? ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * Set Product object
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Block\Product\View\Options\AbstractOptions
     */
    public function setProduct(\Magento\Catalog\Model\Product $product = null)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Retrieve Product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Set option
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return \Magento\Catalog\Block\Product\View\Options\AbstractOptions
     */
    public function setOption(\Magento\Catalog\Model\Product\Option $option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * Get option
     *
     * @return \Magento\Catalog\Model\Product\Option
     */
    public function getOption()
    {
        return $this->_option;
    }

    /**
     * Retrieve formatted price
     *
     * @return string
     * @since 102.0.6
     */
    public function getFormattedPrice()
    {
        if ($option = $this->getOption()) {
            return $this->_formatPrice(
                [
                    'is_percent' => $option->getPriceType() == 'percent',
                    'pricing_value' => $option->getPrice($option->getPriceType() == 'percent'),
                ]
            );
        }
        return '';
    }

    /**
     * Retrieve formatted price.
     *
     * @return string
     *
     * @deprecated 102.0.6
     * @see getFormattedPrice()
     */
    public function getFormatedPrice()
    {
        return $this->getFormattedPrice();
    }

    /**
     * Return formatted price
     *
     * @param array $value
     * @param bool $flag
     * @return string
     */
    protected function _formatPrice($value, $flag = true)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;

        $customOptionPrice = $this->getProduct()->getPriceInfo()->getPrice('custom_option_price');
        $isPercent = (bool) $value['is_percent'];

        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        $optionAmount = $isPercent
            ? $this->calculator->getAmount(
                $this->priceCurrency->roundPrice($value['pricing_value']),
                $this->getProduct(),
                null,
                $context
            ) : $customOptionPrice->getCustomAmount($value['pricing_value'], null, $context);
        $priceStr .= $this->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $optionAmount,
            $customOptionPrice,
            $this->getProduct()
        );

        if ($flag) {
            $priceStr = '<span class="price-notice">' . $priceStr . '</span>';
        }

        return $priceStr;
    }

    /**
     * Get price with including/excluding tax
     *
     * @param float $price
     * @param bool $includingTax
     * @return float
     */
    public function getPrice($price, $includingTax = null)
    {
        if ($includingTax !== null) {
            $price = $this->_catalogHelper->getTaxPrice($this->getProduct(), $price, true);
        } else {
            $price = $this->_catalogHelper->getTaxPrice($this->getProduct(), $price);
        }
        return $price;
    }

    /**
     * Returns price converted to current currency rate
     *
     * @param float $price
     * @return float|string
     */
    public function getCurrencyPrice($price)
    {
        $store = $this->getProduct()->getStore();
        return $this->pricingHelper->currencyByStore($price, $store, false);
    }
}
