<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Api\ExtensibleDataInterface;
<<<<<<< HEAD
=======
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

/**
 * Configured price selection model
 */
class ConfiguredPriceSelection
{
    /**
<<<<<<< HEAD
     * @var \Magento\Framework\Pricing\Adjustment\CalculatorInterface
=======
     * @var CalculatorInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $calculator;

    /**
<<<<<<< HEAD
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     */
    public function __construct(
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
=======
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        CalculatorInterface $calculator
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    ) {
        $this->calculator = $calculator;
    }

    /**
     * Get Selection pricing list.
     *
<<<<<<< HEAD
     * @param \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface $price
     * @return array
     */
    public function getSelectionPriceList(\Magento\Catalog\Pricing\Price\ConfiguredPriceInterface $price): array
=======
     * @param ConfiguredPriceInterface $price
     * @return array
     */
    public function getSelectionPriceList(ConfiguredPriceInterface $price): array
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        $selectionPriceList = [];
        foreach ($price->getOptions() as $option) {
            $selectionPriceList = array_merge(
                $selectionPriceList,
                $this->createSelectionPriceList($option, $price->getProduct())
            );
        }
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $selectionPriceList;
    }

    /**
<<<<<<< HEAD
     * Create Selection Price List
=======
     * Create Selection Price List.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
