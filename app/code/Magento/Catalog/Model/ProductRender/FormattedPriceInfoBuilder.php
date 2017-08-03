<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductRender;

use Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Builder which format all types of float prices into html representation
 * @since 2.2.0
 */
class FormattedPriceInfoBuilder
{
    /**
     * @var PriceCurrencyInterface
     * @since 2.2.0
     */
    private $priceCurrency;

    /**
     * @var FormattedPriceInfoInterfaceFactory
     * @since 2.2.0
     */
    private $formattedPriceInfoFactory;

    /**
     * FormattedPriceInfoBuilder constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param FormattedPriceInfoInterfaceFactory $formattedPriceInfoFactory
     * @since 2.2.0
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        FormattedPriceInfoInterfaceFactory $formattedPriceInfoFactory
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->formattedPriceInfoFactory = $formattedPriceInfoFactory;
    }

    /**
     * Find from abstract object all numeric values (float, double, etc...)
     * Try to convert it into price format
     * Set the result into original Price object
     *
     * Allows to extend standard PriceInfoInterface with converted and formatted prices
     *
     * @param PriceInfoInterface $priceInfo
     * @param int $storeId
     * @param string $currencyCode
     * @return void
     * @since 2.2.0
     */
    public function build(PriceInfoInterface $priceInfo, $storeId, $currencyCode)
    {
        /** @var FormattedPriceInfo $formattedPriceInfo */
        $formattedPriceInfo = $this->formattedPriceInfoFactory->create();

        foreach ($priceInfo->getData() as $key => $value) {
            if (is_numeric($value)) {
                $formattedValue = $this->priceCurrency
                    ->format(
                        $value,
                        true,
                        PriceCurrencyInterface::DEFAULT_PRECISION,
                        $storeId,
                        $currencyCode
                    );
                $formattedPriceInfo->setData($key, $formattedValue);
            }
        }

        $priceInfo->setFormattedPrices($formattedPriceInfo);
    }
}
