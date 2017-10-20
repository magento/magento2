<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\ProductRender\FormattedPriceInfoBuilder;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeInterface;
use Magento\Weee\Api\Data\ProductRender\WeeeAdjustmentAttributeInterfaceFactory;
use Magento\Weee\Helper\Data;

/**
 * Collect information about weee attributes of specific product and add this information
 * into \Magento\Catalog\Api\Data\ProductRenderInterface
 * Calculate weee taxes for each weee attribute
 */
class Weee implements ProductRenderCollectorInterface
{
    /** Weee attribute key */
    const KEY = "weee_attributes";

    /** Wee adjustment key */
    const KEY_ADJUSTMENT = "weee_adjustment";

    /**
     * @var Data
     */
    private $weeeHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var WeeeAdjustmentAttributeInterfaceFactory
     */
    private $weeeAdjustmentAttributeFactory;

    /**
     * @var FormattedPriceInfoBuilder
     */
    private $formattedPriceInfoBuilder;

    /**
     * @var \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterfaceFactory
     */
    private $priceInfoExtensionFactory;

    /**
     * @param Data $weeeHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param WeeeAdjustmentAttributeInterfaceFactory $weeeAdjustmentAttributeFactory
     * @param \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterfaceFactory $priceInfoExtensionFactory
     * @param FormattedPriceInfoBuilder $formattedPriceInfoBuilder
     */
    public function __construct(
        Data $weeeHelper,
        PriceCurrencyInterface $priceCurrency,
        WeeeAdjustmentAttributeInterfaceFactory $weeeAdjustmentAttributeFactory,
        \Magento\Catalog\Api\Data\ProductRender\PriceInfoExtensionInterfaceFactory $priceInfoExtensionFactory,
        FormattedPriceInfoBuilder $formattedPriceInfoBuilder
    ) {
        $this->weeeHelper = $weeeHelper;
        $this->priceCurrency = $priceCurrency;
        $this->weeeAdjustmentAttributeFactory = $weeeAdjustmentAttributeFactory;
        $this->formattedPriceInfoBuilder = $formattedPriceInfoBuilder;
        $this->priceInfoExtensionFactory = $priceInfoExtensionFactory;
    }

    /**
     * @param string $value
     * @param int $storeId
     * @param string $currencyCode
     * @return float
     */
    private function format($value, $storeId, $currencyCode)
    {
        return $this->priceCurrency
            ->format(
                (float) $value,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $storeId,
                $currencyCode
            );
    }

    /**
     * @inheritdoc
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        $extensionAttributes = $productRender->getPriceInfo()->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->priceInfoExtensionFactory->create();
        }

        $weeeAttributesData = [];
        $attributes = $this->weeeHelper->getProductWeeeAttributesForDisplay($product);

        foreach ($attributes as $attribute) {
            /** @var WeeeAdjustmentAttributeInterface $weeeAdjustmentAttribute */
            $weeeAdjustmentAttribute = $this->weeeAdjustmentAttributeFactory->create();
            $weeeAdjustmentAttribute->setAttributeCode($attribute->getData('code'));
            $weeeAdjustmentAttribute->setAmount($this->format(
                $attribute->getData('amount'),
                $productRender->getStoreId(),
                $productRender->getCurrencyCode()
            ));
            $weeeAdjustmentAttribute->setTaxAmount($this->format(
                $attribute->getData('tax_amount'),
                $productRender->getStoreId(),
                $productRender->getCurrencyCode()
            ));
            $weeeAdjustmentAttribute->setAmountExclTax($this->format(
                $attribute->getData('amount_excl_tax'),
                $productRender->getStoreId(),
                $productRender->getCurrencyCode()
            ));

            $weeeAdjustmentAttribute->setTaxAmountInclTax($this->format(
                $attribute->getData('tax_amount') + $attribute->getData('amount_excl_tax'),
                $productRender->getStoreId(),
                $productRender->getCurrencyCode()
            ));
            $weeeAttributesData[] = $weeeAdjustmentAttribute;
        }

        $extensionAttributes->setWeeeAttributes($weeeAttributesData);
        $extensionAttributes->setWeeeAdjustment(
            $this->format(
                $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                $productRender->getStoreId(),
                $productRender->getCurrencyCode()
            )
        );

        $productRender->getPriceInfo()->setExtensionAttributes($extensionAttributes);
    }
}
