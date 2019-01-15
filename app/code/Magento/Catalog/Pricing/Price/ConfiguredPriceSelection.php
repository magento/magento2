<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;

/**
 * Configured price selection model
 */
class ConfiguredPriceSelection
{
    /**
     * @var CalculatorInterface
     */
    private $calculator;

    /**
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        CalculatorInterface $calculator
    ) {
        $this->calculator = $calculator;
    }

    /**
     * Get Selection pricing list.
     *
     * @param ConfiguredPriceInterface $price
     * @return array
     */
    public function getSelectionPriceList(ConfiguredPriceInterface $price): array
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
     * Create Selection Price List.
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
