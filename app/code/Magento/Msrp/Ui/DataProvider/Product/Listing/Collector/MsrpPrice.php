<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterface;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterfaceFactory;
use Magento\Msrp\Helper\Data;
use Magento\Msrp\Model\Config;

/**
 * Collects all information about Manufacture Advertise Price of product
 * This price will be used on front and will be rendered by JS.
 * @since 2.2.0
 */
class MsrpPrice implements ProductRenderCollectorInterface
{
    /** msrp price key */
    const KEY = "msrp_price";

    /**
     * @var PriceCurrencyInterface
     * @since 2.2.0
     */
    private $priceCurrency;

    /**
     * @var Data
     * @since 2.2.0
     */
    private $msrpHelper;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * @var PriceInfoExtensionFactory
     * @since 2.2.0
     */
    private $priceInfoExtensionFactory;

    /**
     * @var MsrpPriceInfoInterfaceFactory
     * @since 2.2.0
     */
    private $msrpPriceInfoFactory;

    /**
     * @var CalculatorInterface
     * @since 2.2.0
     */
    private $adjustmentCalculator;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $msrpHelper
     * @param Config $config
     * @param PriceInfoExtensionFactory $priceInfoExtensionFactory
     * @param MsrpPriceInfoInterfaceFactory $msrpPriceInfoFactory
     * @param CalculatorInterface $adjustmentCalculator
     * @since 2.2.0
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Data $msrpHelper,
        Config $config,
        PriceInfoExtensionFactory $priceInfoExtensionFactory,
        MsrpPriceInfoInterfaceFactory $msrpPriceInfoFactory,
        CalculatorInterface $adjustmentCalculator
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->msrpHelper = $msrpHelper;
        $this->config = $config;
        $this->priceInfoExtensionFactory = $priceInfoExtensionFactory;
        $this->msrpPriceInfoFactory = $msrpPriceInfoFactory;
        $this->adjustmentCalculator = $adjustmentCalculator;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        /** @var MsrpPriceInfoInterface $msrpPriceInfo */
        $msrpPriceInfo = $this->msrpPriceInfoFactory->create();
        /** @var \Magento\Msrp\Pricing\Price\MsrpPrice $msrpPriceType */
        $msrpPriceType = $product->getPriceInfo()->getPrice('msrp_price');
        $msrpPriceInfo->setIsApplicable(
            $msrpPriceType->canApplyMsrp($product) && $msrpPriceType->isMinimalPriceLessMsrp($product)
        );
        $msrpPriceInfo->setIsShownPriceOnGesture(
            $this->msrpHelper->isShowPriceOnGesture($product)
        );
        $msrpPriceInfo->setMsrpMessage(
            $this->msrpHelper->getMsrpPriceMessage($product)
        );
        $msrpPriceInfo->setExplanationMessage(
            $this->config->getExplanationMessage()
        );
        $msrpPriceInfo->setMsrpPrice(
            $this->priceCurrency->format(
                $this->adjustmentCalculator->getAmount($product->getMsrp(), $product)->getValue(),
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $productRender->getStoreId(),
                $productRender->getCurrencyCode()
            )
        );

        $priceInfo = $productRender->getPriceInfo();
        $extensionAttributes = $priceInfo->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->priceInfoExtensionFactory->create();
        }

        $extensionAttributes->setMsrp($msrpPriceInfo);
        $priceInfo->setExtensionAttributes($extensionAttributes);
        $productRender->setPriceInfo($priceInfo);
    }
}
