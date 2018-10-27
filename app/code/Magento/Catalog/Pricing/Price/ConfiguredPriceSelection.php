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
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
=======
>>>>>>> upstream/2.2-develop

/**
 * Configured price selection model
 */
class ConfiguredPriceSelection
{
    /**
<<<<<<< HEAD
     * @var CalculatorInterface
=======
     * @var \Magento\Framework\Pricing\Adjustment\CalculatorInterface
>>>>>>> upstream/2.2-develop
     */
    private $calculator;

    /**
<<<<<<< HEAD
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        CalculatorInterface $calculator
=======
     * @param \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
     */
    public function __construct(
        \Magento\Framework\Pricing\Adjustment\CalculatorInterface $calculator
>>>>>>> upstream/2.2-develop
    ) {
        $this->calculator = $calculator;
    }

    /**
     * Get Selection pricing list.
     *
<<<<<<< HEAD
     * @param ConfiguredPriceInterface $price
     * @return array
     */
    public function getSelectionPriceList(ConfiguredPriceInterface $price): array
=======
     * @param \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface $price
     * @return array
     */
    public function getSelectionPriceList(\Magento\Catalog\Pricing\Price\ConfiguredPriceInterface $price): array
>>>>>>> upstream/2.2-develop
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
>>>>>>> upstream/2.2-develop
        return $selectionPriceList;
    }

    /**
<<<<<<< HEAD
     * Create Selection Price List.
=======
     * Create Selection Price List
>>>>>>> upstream/2.2-develop
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
