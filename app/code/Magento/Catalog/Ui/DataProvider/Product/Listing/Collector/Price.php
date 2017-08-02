<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Collect information about base prices of products
 * Base price contains: final_price, min, max and regular prices.
 * Base price can be applied for all types of products
 * Base price fully describe simple product
 * @since 2.2.0
 */
class Price implements ProductRenderCollectorInterface
{
    /** FInal Price key */
    const KEY_FINAL_PRICE = "final_price";

    /** Minimal Price key */
    const KEY_MINIMAL_PRICE = "minimal_price";

    /** Regular Price key */
    const KEY_REGULAR_PRICE = "regular_price";

    /** Max Price key */
    const KEY_MAX_PRICE = "max_price";

    /**
     * @var PriceCurrencyInterface
     * @since 2.2.0
     */
    private $priceCurrency;

    /**
     * @var array
     * @since 2.2.0
     */
    private $excludeAdjustments;

    /**
     * @var PriceInfoInterfaceFactory
     * @since 2.2.0
     */
    private $priceInfoFactory;

    /**
     * @var FormattedPriceInfoBuilder
     * @since 2.2.0
     */
    private $formattedPriceInfoBuilder;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param PriceInfoInterfaceFactory $priceInfoFactory
     * @param FormattedPriceInfoBuilder $formattedPriceInfoBuilder
     * @param array $excludeAdjustments
     * @since 2.2.0
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        PriceInfoInterfaceFactory $priceInfoFactory,
        FormattedPriceInfoBuilder $formattedPriceInfoBuilder,
        array $excludeAdjustments = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->excludeAdjustments = $excludeAdjustments;
        $this->priceInfoFactory = $priceInfoFactory;
        $this->formattedPriceInfoBuilder = $formattedPriceInfoBuilder;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $priceInfo = $productRender->getPriceInfo();

        if (!$productRender->getPriceInfo()) {
            /** @var PriceInfoInterface $priceInfo */
            $priceInfo = $this->priceInfoFactory->create();
        }

        $priceInfo->setFinalPrice(
            $product
                ->getPriceInfo()
                ->getPrice('final_price')
                ->getAmount()
                ->getValue()
        );
        $priceInfo->setMinimalPrice(
            $product
                ->getPriceInfo()
                ->getPrice('final_price')
                ->getMinimalPrice()
                ->getValue()
        );
        $priceInfo->setRegularPrice(
            $product
                ->getPriceInfo()
                ->getPrice('regular_price')
                ->getAmount()
                ->getValue()
        );
        $priceInfo->setMaxPrice(
            $product
                ->getPriceInfo()
                ->getPrice('final_price')
                ->getMaximalPrice()
                ->getValue()
        );

        $this->formattedPriceInfoBuilder->build(
            $priceInfo,
            $productRender->getStoreId(),
            $productRender->getCurrencyCode()
        );

        $productRender->setPriceInfo($priceInfo);
    }
}
