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

namespace Magento\Tax\Service\V1;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Resource\Sales\Order\Tax;
use Magento\Tax\Service\V1\Data\QuoteDetails;
use Magento\Tax\Service\V1\Data\QuoteDetails\Item as QuoteDetailsItem;
use Magento\Tax\Service\V1\Data\TaxClass;
use Magento\Tax\Service\V1\Data\TaxClassKey;
use Magento\Tax\Service\V1\Data\TaxDetails;
use Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax;
use Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxRate;
use Magento\Tax\Service\V1\Data\TaxDetails\Item as TaxDetailsItem;
use Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder as TaxDetailsItemBuilder;
use Magento\Tax\Service\V1\Data\TaxDetailsBuilder;
use Magento\Tax\Model\Calculation\CalculatorFactory;

/**
 * Tax Calculation Service
 *
 */
class TaxCalculationService implements TaxCalculationServiceInterface
{
    /**
     * Tax calculation model
     *
     * @var Calculation
     */
    protected $calculationTool;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $config;

    /**
     * Tax Details builder
     *
     * @var TaxDetailsBuilder
     */
    protected $taxDetailsBuilder;

    /**
     * Array to hold discount compensations for items
     *
     * @var array
     */
    protected $discountTaxCompensations;

    /**
     * Tax details item builder
     *
     * @var TaxDetailsBuilderItem
     */
    protected $taxDetailsItemBuilder;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Item code to Item object array.
     *
     * @var QuoteDetailsItem[]
     */
    private $keyedItems;

    /**
     * parent item code to children item array.
     *
     * @var QuoteDetailsItem[][]
     */
    private $parentToChildren;

    /**
     * @var CustomerAccountServiceInterface
     */
    protected $customerAccountService;

    /**
     * Tax Class Service
     *
     * @var TaxClassService
     */
    protected $taxClassService;

    /**
     * Calculator Factory
     *
     * @var CalculatorFactory
     */
    protected $calculatorFactory;

    /**
     * Constructor
     *
     * @param Calculation $calculation
     * @param CalculatorFactory $calculatorFactory
     * @param \Magento\Tax\Model\Config $config
     * @param TaxDetailsBuilder $taxDetailsBuilder
     * @param TaxDetailsItemBuilder $taxDetailsItemBuilder
     * @param StoreManagerInterface $storeManager
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param TaxClassService $taxClassService
     */
    public function __construct(
        Calculation $calculation,
        CalculatorFactory $calculatorFactory,
        \Magento\Tax\Model\Config $config,
        TaxDetailsBuilder $taxDetailsBuilder,
        TaxDetailsItemBuilder $taxDetailsItemBuilder,
        StoreManagerInterface $storeManager,
        CustomerAccountServiceInterface $customerAccountService,
        TaxClassService $taxClassService
    ) {
        $this->calculationTool = $calculation;
        $this->calculatorFactory = $calculatorFactory;
        $this->config = $config;
        $this->taxDetailsBuilder = $taxDetailsBuilder;
        $this->taxDetailsItemBuilder = $taxDetailsItemBuilder;
        $this->storeManager = $storeManager;
        $this->customerAccountService = $customerAccountService;
        $this->taxClassService = $taxClassService;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateTax(QuoteDetails $quoteDetails, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getStoreId();
        }

        // initial TaxDetails data
        $taxDetailsData = [
            TaxDetails::KEY_SUBTOTAL => 0.0,
            TaxDetails::KEY_TAX_AMOUNT => 0.0,
            TaxDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT => 0.0,
            TaxDetails::KEY_APPLIED_TAXES => [],
            TaxDetails::KEY_ITEMS => [],
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
            $this->taxClassService->getTaxClassId($quoteDetails->getCustomerTaxClassKey(), 'customer'),
            $quoteDetails->getCustomerId()
        );

        $processedItems = [];
        /** @var QuoteDetailsItem $item */
        foreach ($this->keyedItems as $item) {
            if (isset($this->parentToChildren[$item->getCode()])) {
                $processedChildren = [];
                foreach ($this->parentToChildren[$item->getCode()] as $child) {
                    $processedItem = $this->processItem($child, $calculator);
                    $taxDetailsData = $this->aggregateItemData($taxDetailsData, $processedItem);
                    $processedItems[$processedItem->getCode()] = $processedItem;
                    $processedChildren[] = $processedItem;
                }
                $processedItemBuilder = $this->calculateParent($processedChildren, $item->getQuantity());
                $processedItemBuilder->setCode($item->getCode());
                $processedItemBuilder->setType($item->getType());
                $processedItem = $processedItemBuilder->create();
            } else {
                $processedItem = $this->processItem($item, $calculator);
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
     * @param QuoteDetailsItem[] $items
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
     * @param QuoteDetailsItem $item
     * @param Calculation\AbstractCalculator $calculator
     * @return TaxDetailsItem
     */
    protected function processItem(
        QuoteDetailsItem $item,
        Calculation\AbstractCalculator $calculator
    ) {
        $quantity = $this->getTotalQuantity($item);
        return $calculator->calculate($item, $quantity);
    }

    /**
     * Calculate row information for item based on children calculation
     *
     * @param TaxDetailsItem[] $children
     * @param int $quantity
     * @return TaxDetailsItemBuilder
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
     * @param TaxDetailsItem $item
     * @return array
     */
    protected function aggregateItemData($taxDetailsData, TaxDetailsItem $item)
    {
        $taxDetailsData[TaxDetails::KEY_SUBTOTAL]
            = $taxDetailsData[TaxDetails::KEY_SUBTOTAL] + $item->getRowTotal();

        $taxDetailsData[TaxDetails::KEY_TAX_AMOUNT]
            = $taxDetailsData[TaxDetails::KEY_TAX_AMOUNT] + $item->getRowTax();

        $taxDetailsData[TaxDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT] =
            $taxDetailsData[TaxDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT]
            + $item->getDiscountTaxCompensationAmount();

        $itemAppliedTaxes = $item->getAppliedTaxes();
        if (is_null($itemAppliedTaxes)) {
            return $taxDetailsData;
        }
        $appliedTaxes = $taxDetailsData[TaxDetails::KEY_APPLIED_TAXES];
        foreach ($itemAppliedTaxes as $taxId => $itemAppliedTax) {
            if (!isset($appliedTaxes[$taxId])) {
                //convert rate data object to array
                $rates = [];
                $rateDataObjects = $itemAppliedTax->getRates();
                foreach ($rateDataObjects as $rateDataObject) {
                    $rates[$rateDataObject->getCode()] = [
                        AppliedTaxRate::KEY_CODE => $rateDataObject->getCode(),
                        AppliedTaxRate::KEY_TITLE => $rateDataObject->getTitle(),
                        AppliedTaxRate::KEY_PERCENT => $rateDataObject->getPercent(),
                    ];
                }
                $appliedTaxes[$taxId] = [
                    AppliedTax::KEY_AMOUNT => $itemAppliedTax->getAmount(),
                    AppliedTax::KEY_PERCENT => $itemAppliedTax->getPercent(),
                    AppliedTax::KEY_RATES => $rates,
                    AppliedTax::KEY_TAX_RATE_KEY => $itemAppliedTax->getTaxRateKey(),
                ];
            } else {
                $appliedTaxes[$taxId][AppliedTax::KEY_AMOUNT] += $itemAppliedTax->getAmount();
            }
        }

        $taxDetailsData[TaxDetails::KEY_APPLIED_TAXES] = $appliedTaxes;
        return $taxDetailsData;
    }

    /**
     * Calculates the total quantity for this item.
     *
     * What this really means is that if this is a child item, it return the parent quantity times
     * the child quantity and return that as the child's quantity.
     *
     * @param QuoteDetailsItem $item
     * @return float
     */
    protected function getTotalQuantity(QuoteDetailsItem $item)
    {
        if ($item->getParentCode()) {
            $parentQuantity = $this->keyedItems[$item->getParentCode()]->getQuantity();
            return $parentQuantity * $item->getQuantity();
        }
        return $item->getQuantity();
    }
}
