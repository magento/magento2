<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Collect enough information about product tax
 * In order to allow rendering product on front, we should prepare all cases for all prices
 * This means that we should calculate taxes for each type of price for each product
 * @since 2.2.0
 */
class Tax implements ProductRenderCollectorInterface
{
    /** adjustment final price key */
    const KEY_ADJUSTMENT_FINAL_PRICE = "final_price";

    /** adjustment max price key */
    const KEY_ADJUSTMENT_MAX_PRICE = "max_price";

    /** adjustment min price key */
    const KEY_ADJUSTMENT_MIN_PRICE = "minimal_price";

    /** adjustment regular price key */
    const KEY_ADJUSTMENT_REGULAR_PRICE = "regular_price";

    /**
     * @var PriceCurrencyInterface
     * @since 2.2.0
     */
    private $priceCurrency;

    /**
     * @var \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterface
     * @since 2.2.0
     */
    private $priceInfoExtensionFactory;

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
     * @param \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterfaceFactory $priceInfoExtensionFactory
     * @param PriceInfoInterfaceFactory $priceInfoFactory
     * @param FormattedPriceInfoBuilder $formattedPriceInfoBuilder
     * @since 2.2.0
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterfaceFactory $priceInfoExtensionFactory,
        PriceInfoInterfaceFactory $priceInfoFactory,
        FormattedPriceInfoBuilder $formattedPriceInfoBuilder
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->priceInfoExtensionFactory = $priceInfoExtensionFactory;
        $this->priceInfoFactory = $priceInfoFactory;
        $this->formattedPriceInfoBuilder = $formattedPriceInfoBuilder;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $parentPriceInfo = $productRender->getPriceInfo();

        if (!$parentPriceInfo) {
            $parentPriceInfo = $this->priceInfoFactory->create();
        }

        $extensionAttributes = $parentPriceInfo->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->priceInfoExtensionFactory->create();
        }
        //Prepare prices excluding taxes
        $priceInfo = $this->priceInfoFactory->create();
        $priceInfo->setFinalPrice(
            $product->getPriceInfo()
                    ->getPrice('final_price')
                    ->getAmount()
                    ->getValue(['tax', 'weee'])
        );
        $priceInfo->setMaxPrice(
            $product->getPriceInfo()
                    ->getPrice('final_price')
                    ->getMaximalPrice()
                    ->getValue(['tax', 'weee'])
        );
        $priceInfo->setMinimalPrice(
            $product->getPriceInfo()
                ->getPrice('final_price')
                ->getMinimalPrice()
                ->getValue(['tax', 'weee'])
        );
        $priceInfo->setSpecialPrice($priceInfo->getFinalPrice());
        $priceInfo->setRegularPrice(
            $product->getPriceInfo()
                ->getPrice('regular_price')
                ->getAmount()
                ->getValue(['tax', 'weee'])
        );

        $this->formattedPriceInfoBuilder
            ->build($priceInfo, $productRender->getStoreId(), $productRender->getCurrencyCode());
        $extensionAttributes->setTaxAdjustments($priceInfo);
        $parentPriceInfo->setExtensionAttributes($extensionAttributes);
        $productRender->setPriceInfo($parentPriceInfo);
    }
}
