<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Configured price selection model
 */
class ConfiguredPriceSelection
{
    /**
     * @var \Magento\Framework\Pricing\Adjustment\CalculatorInterface
     */
    private $calculator;

    /**
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     */
    public function __construct(
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
    ) {
        $this->calculator = $calculator;
    }

    /**
     * Get Selection pricing list.
     *
     * @param \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface $price
     * @return array
     */
    public function getSelectionPriceList(\Magento\Catalog\Pricing\Price\ConfiguredPriceInterface $price): array
    {
        $selectionPriceList = [];
        foreach ($price->getOptions() as $option) {
            $selectionPriceList = array_merge(
                $selectionPriceList,
                $this->createSelectionPriceList($option, $price->getProduct())
            );
        }
        return $selectionPriceList;
    }

    /**
     * Create Selection Price List
     *
     * @param ExtensibleDataInterface $option
     * @param Product $product
     * @return array
     */
    private function createSelectionPriceList(ExtensibleDataInterface $option, Product $product): array
    {
        return $this->calculator->createSelectionPriceList($option, $product);
    }
}
