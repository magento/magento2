<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Pricing data helper
 *
 * @api
 * @since 2.0.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($context);
        $this->priceCurrency =  $priceCurrency;
    }

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  float|string
     * @since 2.0.0
     */
    public function currency($value, $format = true, $includeContainer = true)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat($value, $includeContainer)
            : $this->priceCurrency->convert($value);
    }

    /**
     * Convert and format price value for specified store
     *
     * @param   float $value
     * @param   int|\Magento\Store\Model\Store $store
     * @param   bool $format
     * @param   bool $includeContainer
     * @return  float|string
     * @since 2.0.0
     */
    public function currencyByStore($value, $store = null, $format = true, $includeContainer = true)
    {
        if ($format) {
            $value = $this->priceCurrency->convertAndFormat(
                $value,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $store
            );
        } else {
            $value = $this->priceCurrency->convert($value, $store);
        }

        return $value;
    }
}
