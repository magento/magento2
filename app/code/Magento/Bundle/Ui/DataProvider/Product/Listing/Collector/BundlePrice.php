<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;

/**
 * Collect information about bundle price
 *
 * This information can be used on front in order to render product list or product view
 * Price is collected always with VAT and fixed taxes
 */
class BundlePrice implements ProductRenderCollectorInterface
{
    const PRODUCT_TYPE = "bundle";

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var array
     */
    private $excludeAdjustments;

    /**
     * @var PriceInfoInterfaceFactory
     */
    private $priceInfoFactory;

    /**
     * @var FormattedPriceInfoBuilder
     */
    private $formattedPriceInfoBuilder;

    /**
     * BundlePrice constructor.
     * @param PriceCurrencyInterface $priceCurrency
     * @param PriceInfoInterfaceFactory $priceInfoFactory
     * @param FormattedPriceInfoBuilder $formattedPriceInfoBuilder
     * @param array $excludeAdjustments
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
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        if ($product->getTypeId() == self::PRODUCT_TYPE) {
            $priceInfo = $productRender->getPriceInfo();

            if (!$productRender->getPriceInfo()) {
                /** @var PriceInfoInterface $priceInfo */
                $priceInfo = $this->priceInfoFactory->create();
            }

            $priceInfo->setMaxPrice(
                $product
                    ->getPriceInfo()
                    ->getPrice('final_price')
                    ->getMaximalPrice()
                    ->getValue()
            );

            $priceInfo->setMaxRegularPrice(
                $product
                    ->getPriceInfo()
                    ->getPrice('regular_price')
                    ->getMaximalPrice()
                    ->getValue()
            );

            $priceInfo->setMinimalPrice(
                $product
                    ->getPriceInfo()
                    ->getPrice('final_price')
                    ->getMinimalPrice()
                    ->getValue()
            );

            $priceInfo->setMinimalRegularPrice(
                $product
                    ->getPriceInfo()
                    ->getPrice('regular_price')
                    ->getMinimalPrice()
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
}
