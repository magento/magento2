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

use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Catalog\Model\Product;

/**
 * Bundle option price model
 */
class BundleOptionPrice extends AbstractPrice implements BundleOptionPriceInterface
{
    /**
     * Price model code
     */
    const PRICE_CODE = 'bundle_option';

    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;

    /**
     * @var BundleSelectionFactory
     */
    protected $selectionFactory;

    /**
     * @var float|bool|null
     */
    protected $maximalPrice;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
     * @param BundleSelectionFactory $bundleSelectionFactory
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
        BundleSelectionFactory $bundleSelectionFactory
    ) {
        $this->selectionFactory = $bundleSelectionFactory;
        parent::__construct($saleableItem, $quantity, $calculator);
        $this->product->setQty($this->quantity);
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
     * Get Options with attached Selections collection
     *
     * @return \Magento\Bundle\Model\Resource\Option\Collection
     */
    public function getOptions()
    {
        $bundleProduct = $this->product;
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $bundleProduct->getTypeInstance();
        $typeInstance->setStoreFilter($bundleProduct->getStoreId(), $bundleProduct);

        /** @var \Magento\Bundle\Model\Resource\Option\Collection $optionCollection */
        $optionCollection = $typeInstance->getOptionsCollection($bundleProduct);

        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($bundleProduct),
            $bundleProduct
        );

        $priceOptions = $optionCollection->appendSelections($selectionCollection, false, false);
        return $priceOptions;
    }

    /**
     * Get selection amount
     *
     * @param \Magento\Bundle\Model\Selection $selection
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getOptionSelectionAmount($selection)
    {
        $selectionPrice = $this->selectionFactory
            ->create($this->product, $selection, $selection->getSelectionQty());
        return $selectionPrice->getAmount();
    }

    /**
     * Calculate maximal or minimal options value
     *
     * @param bool $searchMin
     * @return bool|float
     */
    protected function calculateOptions($searchMin = true)
    {
        $priceList = [];
        /* @var $option \Magento\Bundle\Model\Option */
        foreach ($this->getOptions() as $option) {
            if ($searchMin && !$option->getRequired()) {
                continue;
            }
            $selectionPriceList = $this->calculator->createSelectionPriceList($option, $this->product);
            $selectionPriceList = $this->calculator->processOptions($option, $selectionPriceList, $searchMin);
            $priceList = array_merge($priceList, $selectionPriceList);
        }
        $amount = $this->calculator->calculateBundleAmount(0., $this->product, $priceList);
        return $amount->getValue();
    }

    /**
     * Get minimal amount of bundle price with options
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getAmount()
    {
        return $this->calculator->getOptionsAmount($this->product);
    }
}
