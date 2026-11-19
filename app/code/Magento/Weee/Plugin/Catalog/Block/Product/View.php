<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\View as ProductView;
use Magento\Catalog\Model\Product;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Render;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Pricing\Adjustment as TaxAdjustment;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Tax;

/**
 * Keep Fixed Product Tax in the excluding-tax amounts the product price box redraws.
 */
class View
{
    /**
     * @param WeeeHelper $weeeHelper
     * @param TaxHelper $taxHelper
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(
        private readonly WeeeHelper $weeeHelper,
        private readonly TaxHelper $taxHelper,
        private readonly EncoderInterface $jsonEncoder,
        private readonly DecoderInterface $jsonDecoder
    ) {
    }

    /**
     * Include Fixed Product Tax in the excluding-tax price the price box writes back.
     *
     * Catalog prices that include tax are shown excluding tax by changing the price
     * element's type to basePrice. The price box then replaces the rendered amount with
     * basePrice from this config. That amount has every adjustment removed, including
     * Fixed Product Tax, so the tax disappears after the script runs.
     *
     * @param ProductView $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig(ProductView $subject, string $result): string
    {
        $product = $subject->getProduct();
        if (!$this->priceBoxUsesExcludingTaxAmount($product)) {
            return $result;
        }

        $config = $this->jsonDecoder->decode($result);
        if (!is_array($config) || empty($config['prices']) || !is_array($config['prices'])) {
            return $result;
        }

        $changed = false;
        foreach (['basePrice' => 'final_price', 'baseOldPrice' => 'regular_price'] as $priceKey => $priceCode) {
            if (!isset($config['prices'][$priceKey]['amount'])) {
                continue;
            }
            $displayAmount = $this->excludingTaxAmount($product, $priceCode);
            if ((float)$config['prices'][$priceKey]['amount'] === $displayAmount) {
                continue;
            }
            $config['prices'][$priceKey]['amount'] = $displayAmount;
            $changed = true;
        }

        return $changed ? $this->jsonEncoder->encode($config) : $result;
    }

    /**
     * Whether the price box redraws the excluding-tax amount, and that amount includes FPT.
     *
     * @param Product $product
     * @return bool
     */
    private function priceBoxUsesExcludingTaxAmount(Product $product): bool
    {
        $storeId = $product->getStoreId();
        if (!$this->weeeHelper->isEnabled($storeId) || !$this->taxHelper->priceIncludesTax($storeId)) {
            return false;
        }
        if (!$this->taxHelper->displayPriceExcludingTax()) {
            return false;
        }

        return (bool)$this->weeeHelper->typeOfDisplay(
            [Tax::DISPLAY_INCL, Tax::DISPLAY_INCL_DESCR],
            Render::ZONE_ITEM_VIEW,
            $storeId
        );
    }

    /**
     * Full price with the catalog tax adjustment removed. This is the figure in the price HTML.
     *
     * @param Product $product
     * @param string $priceCode
     * @return float
     */
    private function excludingTaxAmount(Product $product, string $priceCode): float
    {
        return (float)$product->getPriceInfo()
            ->getPrice($priceCode)
            ->getAmount()
            ->getValue(TaxAdjustment::ADJUSTMENT_CODE);
    }
}
