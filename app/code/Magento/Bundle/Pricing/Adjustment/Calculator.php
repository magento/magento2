<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Pricing
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Pricing\Adjustment;

use Magento\Pricing\Object\SaleableInterface;
use Magento\Pricing\Amount\AmountFactory;
use Magento\Pricing\Adjustment\Calculator as CalculatorBase;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Price\BundleOptionPriceInterface;
use Magento\Bundle\Pricing\Price\BundleSelectionFactory;
use Magento\Bundle\Pricing\Price\BundleOptionPrice;

/**
 * Bundle price calculator
 */
class Calculator implements BundleCalculatorInterface
{
    /**
     * @var CalculatorBase
     */
    protected $calculator;

    /**
     * @var AmountFactory
     */
    protected $amountFactory;

    /**
     * @var BundleSelectionFactory
     */
    protected $selectionFactory;

    /**
     * @param CalculatorBase $calculator
     * @param AmountFactory $amountFactory
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(
        CalculatorBase $calculator,
        AmountFactory $amountFactory,
        BundleSelectionFactory $bundleSelectionFactory
    ) {
        $this->calculator = $calculator;
        $this->amountFactory = $amountFactory;
        $this->selectionFactory = $bundleSelectionFactory;
    }

    /**
     * Get amount for current product which is included price of existing options with minimal price
     *
     * @param float|string $amount
     * @param SaleableInterface $saleableItem
     * @param null|string $exclude
     * @return \Magento\Pricing\Amount\AmountInterface
     */
    public function getAmount($amount, SaleableInterface $saleableItem, $exclude = null)
    {
        return $this->getOptionsAmount($amount, $saleableItem, $exclude, true);
    }

    /**
     * Get amount for current product which is included price of existing options with maximal price
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null $exclude
     * @return \Magento\Pricing\Amount\AmountInterface
     */
    public function getMaxAmount($amount, SaleableInterface $saleableItem, $exclude = null)
    {
        return $this->getOptionsAmount($amount, $saleableItem, $exclude, false);
    }

    /**
     * Base calculation of amount for saleable item
     *
     * @param float $amount
     * @param SaleableInterface $saleableItem
     * @param null|string $exclude
     * @param bool $searchMin
     * @return \Magento\Pricing\Amount\AmountInterface
     */
    protected function getOptionsAmount($amount, SaleableInterface $saleableItem, $exclude = null, $searchMin = true)
    {
        $fullAmount = 0.;
        $adjustments = [];
        // Get amount for bundle product
        $amountList[] = $this->calculator->getAmount($amount, $saleableItem);

        $minOptionAmount = null;
        /* @var $option \Magento\Bundle\Model\Option */
        foreach ($this->getBundleOptionPrice($saleableItem)->getOptions() as $option) {
            if (!$option->getSelections()) {
                continue;
            }
            // Add amounts for custom options
            $optionsAmounts = $this->processOptions($option, $saleableItem, $searchMin);
            if ($searchMin
                && ($minOptionAmount === null || end($optionsAmounts)->getValue() < $minOptionAmount->getValue())
            ) {
                $minOptionAmount = end($optionsAmounts);
            } elseif (!$searchMin) {
                $amountList = array_merge($amountList, $optionsAmounts);
            }
        }

        if ($searchMin && $minOptionAmount) {
            $amountList[] = $minOptionAmount;
        }

        /** @var \Magento\Pricing\Amount\AmountInterface $itemAmount */
        foreach ($amountList as $itemAmount) {
            $fullAmount += $itemAmount->getValue();
            foreach ($itemAmount->getAdjustmentAmounts() as $code => $adjustment) {
                if ($exclude === null || $exclude !== $code) {
                    $adjustments[$code] = isset($adjustments[$code]) ? $adjustments[$code] + $adjustment : $adjustment;
                }
            }
        }
        return $this->amountFactory->create($fullAmount, $adjustments);
    }

    /**
     * @param SaleableInterface $saleableItem
     * @return BundleOptionPrice
     */
    protected function getBundleOptionPrice(SaleableInterface $saleableItem)
    {
        return $saleableItem->getPriceInfo()->getPrice(BundleOptionPriceInterface::PRICE_TYPE_BUNDLE_OPTION);
    }

    /**
     * @param \Magento\Bundle\Model\Option $option
     * @param SaleableInterface $saleableItem
     * @param bool $searchMin
     * @return \Magento\Pricing\Amount\AmountInterface[]
     */
    protected function processOptions($option, $saleableItem, $searchMin = true)
    {
        $result = [];
        foreach ($option->getSelections() as $selection) {
            /* @var $selection \Magento\Bundle\Model\Selection|\Magento\Catalog\Model\Product */
            if (!$selection->isSalable()/* || ($searchMin && !$option->getRequired())*/) {
                // @todo CatalogInventory Show out of stock Products
                continue;
            }
            $current = $this->getSelection($selection, $saleableItem);
            if (empty($result)) {
                $result = [$current];
                continue;
            }
            if ($searchMin && end($result)->getValue() > $current->getValue()) {
                $result = [$current];
            } elseif (!$searchMin && $option->isMultiSelection()) {
                $result[] = $current;
            } elseif (!$searchMin && !$option->isMultiSelection() && end($result)->getValue() < $current->getValue()) {
                $result = [$current];
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Bundle\Model\Selection $selection
     * @param SaleableInterface $saleableItem
     * @return \Magento\Pricing\Amount\AmountInterface
     */
    protected function getSelection($selection, $saleableItem)
    {
        if ($saleableItem->getPriceType() == Price::PRICE_TYPE_FIXED) {
            return $this->createFixedAmount($selection, $saleableItem);
        } else {
            return $this->createDynamicAmount($selection, $saleableItem);
        }
    }

    /**
     * @param \Magento\Bundle\Model\Selection $selection
     * @param SaleableInterface $saleableItem
     * @return \Magento\Pricing\Amount\AmountInterface
     */
    protected function createDynamicAmount($selection, $saleableItem)
    {
        /** @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice $price */
        $price = $this->selectionFactory->create($saleableItem, $selection, $selection->getSelectionQty());
        return $price->getAmount();
    }

    /**
     * @param \Magento\Bundle\Model\Selection $selection
     * @param SaleableInterface $saleableItem
     * @return \Magento\Pricing\Amount\AmountInterface
     */
    protected function createFixedAmount($selection, $saleableItem)
    {
        $selectionPrice = $this->selectionFactory
            ->create($saleableItem, $selection, $selection->getSelectionQty())
            ->getValue();
        return $this->calculator->getAmount($selectionPrice, $saleableItem);
    }
}
