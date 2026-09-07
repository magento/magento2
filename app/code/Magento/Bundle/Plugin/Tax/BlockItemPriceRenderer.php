<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Tax;

use Magento\Bundle\Model\Product\Price as BundleProductPrice;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Bundle\Model\Quote\Item\Option as BundleQuoteItemOption;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Tax\Block\Item\Price\Renderer as ItemPriceRenderer;

class BlockItemPriceRenderer
{
    /**
     * @var BundleQuoteItemOption
     */
    private BundleQuoteItemOption $bundleQuoteItemOption;

    /**
     * @var JsonSerializer
     */
    private JsonSerializer $serializer;

    /**
     * @param BundleQuoteItemOption $bundleQuoteItemOption
     * @param JsonSerializer $serializer
     */
    public function __construct(
        BundleQuoteItemOption $bundleQuoteItemOption,
        JsonSerializer $serializer
    ) {
        $this->bundleQuoteItemOption = $bundleQuoteItemOption;
        $this->serializer = $serializer;
    }

    /**
     * Recalculate price conversion for the bundle product.
     *
     * @param ItemPriceRenderer $itemPriceRenderer
     * @param float $result
     * @return float
     */
    public function afterGetItemDisplayPriceExclTax(
        ItemPriceRenderer $itemPriceRenderer,
        float $result
    ): float {
        if ($itemPriceRenderer->getItem()->getProductType() === BundleProductType::TYPE_CODE) {
            $bundleProduct = $itemPriceRenderer->getItem()->getProduct();
            $bundleSelectionOptions = $this->bundleQuoteItemOption->getSelectionOptions($bundleProduct);
            if (empty($bundleSelectionOptions)
                || $bundleProduct->getPriceType() == BundleProductPrice::PRICE_TYPE_FIXED
            ) {
                return $result;
            }

            $price = 0.0;
            foreach ($bundleSelectionOptions as $bundleSelectionOption) {
                $selectionOptionValue = $this->serializer->unserialize(reset($bundleSelectionOption)['value']);
                $price += $selectionOptionValue['price'] * $selectionOptionValue['qty'];
            }

            return $price;
        }

        return $result;
    }
}
