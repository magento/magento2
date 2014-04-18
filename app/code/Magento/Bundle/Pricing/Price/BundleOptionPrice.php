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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Pricing\Object\SaleableInterface;
use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;

/**
 * Bundle option price model
 */
class BundleOptionPrice extends RegularPrice implements BundleOptionPriceInterface
{
    /**
     * @var string
     */
    protected $priceType = self::PRICE_TYPE_BUNDLE_OPTION;

    /**
     * @var array
     */
    protected $priceOptions;

    /**
     * @var BundleSelectionFactory
     */
    protected $selectionFactory;

    /**
     * @var float|bool|null
     */
    protected $maximalPrice;

    /**
     * @param SaleableInterface $salableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(
        SaleableInterface $salableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
        BundleSelectionFactory $bundleSelectionFactory
    ) {
        $this->selectionFactory = $bundleSelectionFactory;
        parent::__construct($salableItem, $quantity, $calculator);
        $this->salableItem->setQty($this->quantity);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = $this->calculateOptions();
        }
        return $this->value;
    }

    /**
     * Get Options with attached Selections collection
     *
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function getOptions()
    {
        if (null !== $this->priceOptions) {
            return $this->priceOptions;
        }
        $this->salableItem->getTypeInstance()->setStoreFilter($this->salableItem->getStoreId(), $this->salableItem);

        $optionCollection = $this->salableItem->getTypeInstance()->getOptionsCollection($this->salableItem);

        $selectionCollection = $this->salableItem->getTypeInstance()->getSelectionsCollection(
            $this->salableItem->getTypeInstance()->getOptionsIds($this->salableItem),
            $this->salableItem
        );

        $this->priceOptions = $optionCollection->appendSelections($selectionCollection, false, false);
        return $this->priceOptions;
    }

    /**
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Pricing\Amount\AmountInterface
     */
    public function getOptionSelectionAmount($selection)
    {
        return $this->createSelection($selection)->getAmount();
    }

    /**
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Bundle\Pricing\Price\BundleSelectionPriceInterface
     */
    protected function createSelection($selection)
    {
        return $this->selectionFactory->create($this->salableItem, $selection, $selection->getSelectionQty());
    }

    /**
     * @param bool $searchMin
     * @return bool|float
     */
    protected function calculateOptions($searchMin = true)
    {
        $price = false;
        $amountList = [];
        /* @var $option \Magento\Bundle\Model\Option */
        foreach ($this->getOptions() as $option) {
            if (!$option->getSelections()) {
                continue;
            }
            $amountList = array_merge($amountList, $this->processOptions($option, $searchMin));
        }
        if (!empty($amountList)) {
            $price = 0.;
            foreach ($amountList as $itemAmount) {
                $price += $itemAmount->getValue();
            }
        }
        return $price;
    }

    /**
     * @param \Magento\Bundle\Model\Option $option
     * @param bool $searchMin
     * @return \Magento\Pricing\Amount\AmountInterface[]
     */
    protected function processOptions($option, $searchMin = true)
    {
        $result = [];
        foreach ($option->getSelections() as $selection) {
            /* @var $selection \Magento\Bundle\Model\Selection */
            if (!$selection->isSalable() || ($searchMin && !$option->getRequired())) {
                // @todo CatalogInventory Show out of stock Products
                continue;
            }
            $current = $this->createSelection($selection);
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
     * Getter for maximal price of options
     *
     * @return bool|float
     */
    public function getMaxValue()
    {
        if (null === $this->maximalPrice) {
            $this->maximalPrice = $this->calculateOptions(false);
        }
        return $this->maximalPrice;
    }

    /**
     * @return \Magento\Pricing\Amount\AmountInterface
     */
    public function getAmount()
    {
        return $this->calculator->getAmount(0, $this->salableItem);
    }
}
