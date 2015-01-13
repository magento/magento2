<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsDataBuilder;
use Magento\Tax\Api\Data\TaxDetailsItemDataBuilder;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;
use Magento\Tax\Api\Data\AppliedTaxInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Calculation\AbstractCalculator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\TaxCalculationInterface;

class TaxCalculation implements TaxCalculationInterface
{
    /**
     * Tax Details builder
     *
     * @var TaxDetailsDataBuilder
     */
    protected $taxDetailsBuilder;

    /**
     * Tax configuration object
     *
     * @var Config
     */
    protected $config;

    /**
     * Tax calculation model
     *
     * @var Calculation
     */
    protected $calculationTool;

    /**
     * Array to hold discount compensations for items
     *
     * @var array
     */
    protected $discountTaxCompensations;

    /**
     * Tax details item builder
     *
     * @var TaxDetailsItemDataBuilder
     */
    protected $taxDetailsItemBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Item code to Item object array.
     *
     * @var QuoteDetailsItemInterface[]
     */
    private $keyedItems;

    /**
     * Parent item code to children item array.
     *
     * @var QuoteDetailsItemInterface[][]
     */
    private $parentToChildren;

    /**
     * Tax Class Management
     *
     * @var TaxClassManagementInterface
     */
    protected $taxClassManagement;

    /**
     * Calculator Factory
     *
     * @var CalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * @param Calculation $calculation
     * @param CalculatorFactory $calculatorFactory
     * @param Config $config
     * @param TaxDetailsDataBuilder $taxDetailsBuilder
     * @param TaxDetailsItemDataBuilder $taxDetailsItemBuilder
     * @param StoreManagerInterface $storeManager
     * @param TaxClassManagementInterface $taxClassManagement
     */
    public function __construct(
        Calculation $calculation,
        CalculatorFactory $calculatorFactory,
        Config $config,
        TaxDetailsDataBuilder $taxDetailsBuilder,
        TaxDetailsItemDataBuilder $taxDetailsItemBuilder,
        StoreManagerInterface $storeManager,
        TaxClassManagementInterface $taxClassManagement
    ) {
        $this->calculationTool = $calculation;
        $this->calculatorFactory = $calculatorFactory;
        $this->config = $config;
        $this->taxDetailsBuilder = $taxDetailsBuilder;
        $this->taxDetailsItemBuilder = $taxDetailsItemBuilder;
        $this->storeManager = $storeManager;
        $this->taxClassManagement = $taxClassManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateTax(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteDetails,
        $storeId = null,
        $round = true
    ) {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getStoreId();
        }

        // initial TaxDetails data
        $taxDetailsData = [
            TaxDetailsInterface::KEY_SUBTOTAL => 0.0,
            TaxDetailsInterface::KEY_TAX_AMOUNT => 0.0,
            TaxDetailsInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT => 0.0,
            TaxDetailsInterface::KEY_APPLIED_TAXES => [],
            TaxDetailsInterface::KEY_ITEMS => [],
        ];
        $items = $quoteDetails->getItems();
        if (empty($items)) {
            return $this->taxDetailsBuilder->populateWithArray($taxDetailsData)->create();
        }
        $this->computeRelationships($items);

        $calculator = $this->calculatorFactory->create(
            $this->config->getAlgorithm($storeId),
            $storeId,
            $quoteDetails->getBillingAddress(),
            $quoteDetails->getShippingAddress(),
            $this->taxClassManagement->getTaxClassId($quoteDetails->getCustomerTaxClassKey(), 'customer'),
            $quoteDetails->getCustomerId()
        );

        $processedItems = [];
        /** @var QuoteDetailsItemInterface $item */
        foreach ($this->keyedItems as $item) {
            if (isset($this->parentToChildren[$item->getCode()])) {
                $processedChildren = [];
                foreach ($this->parentToChildren[$item->getCode()] as $child) {
                    $processedItem = $this->processItem($child, $calculator, $round);
                    $taxDetailsData = $this->aggregateItemData($taxDetailsData, $processedItem);
                    $processedItems[$processedItem->getCode()] = $processedItem;
                    $processedChildren[] = $processedItem;
                }
                $processedItemBuilder = $this->calculateParent($processedChildren, $item->getQuantity());
                $processedItemBuilder->setCode($item->getCode());
                $processedItemBuilder->setType($item->getType());
                $processedItem = $processedItemBuilder->create();
            } else {
                $processedItem = $this->processItem($item, $calculator, $round);
                $taxDetailsData = $this->aggregateItemData($taxDetailsData, $processedItem);
            }
            $processedItems[$processedItem->getCode()] = $processedItem;
        }

        return $this->taxDetailsBuilder->populateWithArray($taxDetailsData)->setItems($processedItems)->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultCalculatedRate(
        $productTaxClassID,
        $customerId = null,
        $storeId = null
    ) {
        return $this->getRate($productTaxClassID, $customerId, $storeId, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculatedRate(
        $productTaxClassID,
        $customerId = null,
        $storeId = null
    ) {
        return $this->getRate($productTaxClassID, $customerId, $storeId);
    }

    /**
     * Calculate rate based on default parameter
     *
     * @param int $productTaxClassID
     * @param int|null $customerId
     * @param string|null $storeId
     * @param bool $isDefault
     * @return float
     */
    protected function getRate(
        $productTaxClassID,
        $customerId = null,
        $storeId = null,
        $isDefault = false
    ) {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getStoreId();
        }
        if (!$isDefault) {
            $addressRequestObject = $this->calculationTool->getRateRequest(null, null, null, $storeId, $customerId);
        } else {
            $addressRequestObject = $this->calculationTool->getDefaultRateRequest($storeId, $customerId);
        }
        $addressRequestObject->setProductClassId($productTaxClassID);
        return $this->calculationTool->getRate($addressRequestObject);
    }

    /**
     * Computes relationships between items, primarily the child to parent relationship.
     *
     * @param QuoteDetailsItemInterface[] $items
     * @return void
     */
    private function computeRelationships($items)
    {
        $this->keyedItems = [];
        $this->parentToChildren = [];
        foreach ($items as $item) {
            if ($item->getParentCode() === null) {
                $this->keyedItems[$item->getCode()] = $item;
            } else {
                $this->parentToChildren[$item->getParentCode()][] = $item;
            }
        }
    }

    /**
     * Calculate item tax with customized rounding level
     *
     * @param QuoteDetailsItemInterface $item
     * @param AbstractCalculator $calculator
     * @param bool $round
     * @return TaxDetailsItemInterface
     */
    protected function processItem(
        QuoteDetailsItemInterface $item,
        AbstractCalculator $calculator,
        $round = true
    ) {
        $quantity = $this->getTotalQuantity($item);
        return $calculator->calculate($item, $quantity, $round);
    }

    /**
     * Calculate row information for item based on children calculation
     *
     * @param TaxDetailsItemInterface[] $children
     * @param int $quantity
     * @return TaxDetailsItemDataBuilder
     */
    protected function calculateParent($children, $quantity)
    {
        $rowTotal = 0.00;
        $rowTotalInclTax = 0.00;
        $rowTax = 0.00;
        $taxableAmount = 0.00;

        foreach ($children as $child) {
            $rowTotal += $child->getRowTotal();
            $rowTotalInclTax += $child->getRowTotalInclTax();
            $rowTax += $child->getRowTax();
            $taxableAmount += $child->getTaxableAmount();
        }

        $price = $this->calculationTool->round($rowTotal / $quantity);
        $priceInclTax = $this->calculationTool->round($rowTotalInclTax / $quantity);

        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($rowTotal);
        $this->taxDetailsItemBuilder->setRowTotalInclTax($rowTotalInclTax);
        $this->taxDetailsItemBuilder->setRowTax($rowTax);

        return $this->taxDetailsItemBuilder;
    }

    /**
     * Add row total item amount to subtotal
     *
     * @param array $taxDetailsData
     * @param TaxDetailsItemInterface $item
     * @return array
     */
    protected function aggregateItemData($taxDetailsData, TaxDetailsItemInterface $item)
    {
        $taxDetailsData[TaxDetailsInterface::KEY_SUBTOTAL]
            = $taxDetailsData[TaxDetailsInterface::KEY_SUBTOTAL] + $item->getRowTotal();

        $taxDetailsData[TaxDetailsInterface::KEY_TAX_AMOUNT]
            = $taxDetailsData[TaxDetailsInterface::KEY_TAX_AMOUNT] + $item->getRowTax();

        $taxDetailsData[TaxDetailsInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT] =
            $taxDetailsData[TaxDetailsInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT]
            + $item->getDiscountTaxCompensationAmount();

        $itemAppliedTaxes = $item->getAppliedTaxes();
        if (is_null($itemAppliedTaxes)) {
            return $taxDetailsData;
        }
        $appliedTaxes = $taxDetailsData[TaxDetailsInterface::KEY_APPLIED_TAXES];
        foreach ($itemAppliedTaxes as $taxId => $itemAppliedTax) {
            if (!isset($appliedTaxes[$taxId])) {
                //convert rate data object to array
                $rates = [];
                $rateDataObjects = $itemAppliedTax->getRates();
                foreach ($rateDataObjects as $rateDataObject) {
                    $rates[$rateDataObject->getCode()] = [
                        AppliedTaxRateInterface::KEY_CODE => $rateDataObject->getCode(),
                        AppliedTaxRateInterface::KEY_TITLE => $rateDataObject->getTitle(),
                        AppliedTaxRateInterface::KEY_PERCENT => $rateDataObject->getPercent(),
                    ];
                }
                $appliedTaxes[$taxId] = [
                    AppliedTaxInterface::KEY_AMOUNT => $itemAppliedTax->getAmount(),
                    AppliedTaxInterface::KEY_PERCENT => $itemAppliedTax->getPercent(),
                    AppliedTaxInterface::KEY_RATES => $rates,
                    AppliedTaxInterface::KEY_TAX_RATE_KEY => $itemAppliedTax->getTaxRateKey(),
                ];
            } else {
                $appliedTaxes[$taxId][AppliedTaxInterface::KEY_AMOUNT] += $itemAppliedTax->getAmount();
            }
        }

        $taxDetailsData[TaxDetailsInterface::KEY_APPLIED_TAXES] = $appliedTaxes;
        return $taxDetailsData;
    }

    /**
     * Calculates the total quantity for this item.
     *
     * What this really means is that if this is a child item, it return the parent quantity times
     * the child quantity and return that as the child's quantity.
     *
     * @param QuoteDetailsItemInterface $item
     * @return float
     */
    protected function getTotalQuantity(QuoteDetailsItemInterface $item)
    {
        if ($item->getParentCode()) {
            $parentQuantity = $this->keyedItems[$item->getParentCode()]->getQuantity();
            return $parentQuantity * $item->getQuantity();
        }
        return $item->getQuantity();
    }
}
