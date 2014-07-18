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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Resource\Sales\Order\Tax;
use Magento\Tax\Service\V1\Data\QuoteDetails;
use Magento\Tax\Service\V1\Data\QuoteDetails\Item as QuoteDetailsItem;
use Magento\Tax\Service\V1\Data\TaxDetails;
use Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax;
use Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxRate;
use Magento\Tax\Service\V1\Data\TaxDetails\Item as TaxDetailsItem;
use Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder as TaxDetailsItemBuilder;
use Magento\Tax\Service\V1\Data\TaxDetailsBuilder;

/**
 * Tax Calculation Service
 *
 */
class TaxCalculationService implements TaxCalculationServiceInterface
{
    /**#@+
     * Constants for delta rounding key
     */
    const KEY_REGULAR_DELTA_ROUNDING = 'regular';

    const KEY_APPLIED_TAX_DELTA_ROUNDING = 'applied_tax_amount';

    const KEY_TAX_AFTER_DISCOUNT_DELTA_ROUNDING = 'tax_after_discount';
    /**#@-*/

    /**
     * Tax calculation model
     *
     * @var Calculation
     */
    protected $calculator;

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
     * Rounding deltas for prices
     *
     * @var array
     * example:
     *  [
     *      'type' => [
     *          'rate' => 'rounding delta',
     *      ],
     *  ]
     */
    protected $roundingDeltas;

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
     * @var StoreManagerInterface
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
     * Constructor
     *
     * @param Calculation $calculation
     * @param \Magento\Tax\Model\Config $config
     * @param TaxDetailsBuilder $taxDetailsBuilder
     * @param TaxDetailsItemBuilder $taxDetailsItemBuilder
     * @param StoreManagerInterface $storeManager
     * @param CustomerAccountServiceInterface $customerAccountService
     */
    public function __construct(
        Calculation $calculation,
        \Magento\Tax\Model\Config $config,
        TaxDetailsBuilder $taxDetailsBuilder,
        TaxDetailsItemBuilder $taxDetailsItemBuilder,
        StoreManagerInterface $storeManager,
        CustomerAccountServiceInterface $customerAccountService
    ) {
        $this->calculator = $calculation;
        $this->config = $config;
        $this->taxDetailsBuilder = $taxDetailsBuilder;
        $this->taxDetailsItemBuilder = $taxDetailsItemBuilder;
        $this->storeManager = $storeManager;
        $this->customerAccountService = $customerAccountService;
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

        $addressRequest = $this->getAddressTaxRequest($quoteDetails, $storeId, $quoteDetails->getCustomerId());
        if ($this->config->priceIncludesTax($storeId)) {
            $storeRequest = $this->getStoreTaxRequest($storeId);
            $classIds = [];
            foreach ($items as $item) {
                if ($item->getTaxClassId()) {
                    $classIds[] = $item->getTaxClassId();
                }
            }
            $classIds = array_unique($classIds);
            $addressRequest->setProductClassId($classIds);
            $storeRequest->setProductClassId($classIds);
            if ((bool)$this->config->crossBorderTradeEnabled($storeId)) {
                $addressRequest->setSameRateAsStore(true);
            } else {
                $addressRequest->setSameRateAsStore(
                    $this->calculator->compareRequests($storeRequest, $addressRequest)
                );
            }
        }

        // init rounding deltas for this quote
        $this->roundingDeltas = [];
        // init discount tax compensations array
        $this->discountTaxCompensations = [];
        $processedItems = [];
        /** @var QuoteDetailsItem $item */
        foreach ($this->keyedItems as $item) {
            if (isset($this->parentToChildren[$item->getCode()])) {
                $processedChildren = [];
                foreach ($this->parentToChildren[$item->getCode()] as $child) {
                    $processedItem = $this->processItem($child, $addressRequest, $storeId);
                    $taxDetailsData = $this->aggregateItemData($taxDetailsData, $processedItem);
                    $processedItems[$processedItem->getCode()] = $processedItem;
                    $processedChildren[] = $processedItem;
                }
                $processedItemBuilder = $this->calculateParent($processedChildren, $item->getQuantity());
                $processedItemBuilder->setCode($item->getCode());
                $processedItemBuilder->setType($item->getType());
                $processedItem = $processedItemBuilder->create();
            } else {
                $processedItem = $this->processItem($item, $addressRequest, $storeId);
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
            $addressRequestObject = $this->calculator->getRateRequest(null, null, null, $storeId, $customerId);
        } else {
            $addressRequestObject = $this->calculator->getDefaultRateRequest($storeId, $customerId);
        }
        $addressRequestObject->setProductClassId($productTaxClassID);
        return $this->calculator->getRate($addressRequestObject);
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
     * Get request for fetching address tax rate
     *
     * @param QuoteDetails $quoteDetails
     * @param int $storeId
     * @param int $customerId
     * @return \Magento\Framework\Object
     */
    protected function getAddressTaxRequest(QuoteDetails $quoteDetails, $storeId, $customerId)
    {
        return $this->calculator->getRateRequest(
            $quoteDetails->getShippingAddress(),
            $quoteDetails->getBillingAddress(),
            $quoteDetails->getCustomerTaxClassId(),
            $storeId,
            $customerId
        );
    }

    /**
     * Get request for fetching store tax rate
     *
     * @param int $storeId
     * @return \Magento\Framework\Object
     */
    protected function getStoreTaxRequest($storeId)
    {
        return $this->calculator->getRateOriginRequest($storeId);
    }

    /**
     * Calculate item tax with customized rounding level
     *
     * @param QuoteDetailsItem $item
     * @param \Magento\Framework\Object $taxRequest
     * @param int $storeId
     * @return TaxDetailsItem|null
     */
    protected function processItem(
        QuoteDetailsItem $item,
        \Magento\Framework\Object $taxRequest,
        $storeId
    ) {
        switch ($this->config->getAlgorithm($storeId)) {
            case Calculation::CALC_UNIT_BASE:
                return $this->unitBaseCalculation($item, $taxRequest, $storeId);
            case Calculation::CALC_ROW_BASE:
            case Calculation::CALC_TOTAL_BASE:
                return $this->totalBaseCalculation($item, $taxRequest, $storeId);
            default:
                return null;
        }
    }

    /**
     * Calculate item price and row total including/excluding tax based on unit price rounding level
     * This function also saves applied tax per tax rate for the item
     *
     * @param QuoteDetailsItem $item
     * @param \Magento\Framework\Object $taxRateRequest
     * @param int $storeId
     * @return TaxDetailsItem
     */
    protected function unitBaseCalculation(
        QuoteDetailsItem $item,
        \Magento\Framework\Object $taxRateRequest,
        $storeId
    ) {
        /** @var  \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $appliedTaxes */
        $appliedTaxes = [];
        $appliedTaxBuilder = $this->taxDetailsItemBuilder->getAppliedTaxBuilder();
        $appliedTaxRateBuilder = $appliedTaxBuilder->getAppliedTaxRateBuilder();

        $taxRateRequest->setProductClassId($item->getTaxClassId());
        $appliedRates = $this->calculator->getAppliedRates($taxRateRequest);
        $rate = $this->calculator->getRate($taxRateRequest);

        $quantity = $this->getTotalQuantity($item);
        $price = $priceInclTax = $this->calculator->round($item->getUnitPrice());
        $rowTotal = $rowTotalInclTax = $this->calcRowTotal($item);

        $discountAmount = $item->getDiscountAmount();
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($storeId);

        $discountTaxCompensationAmount = 0;

        if ($item->getTaxIncluded()) {
            if ($taxRateRequest->getSameRateAsStore()) {
                $uniTax = $this->calculator->calcTaxAmount($priceInclTax, $rate, true);
                $price = $priceInclTax - $uniTax;
                $rowTax = $uniTax * $quantity;
                $rowTotal = $price * $quantity;
            } else {
                $storeRate = $this->calculator->getStoreRate($taxRateRequest, $storeId);
                $priceInclTax = $this->calculatePriceInclTax($price, $storeRate, $rate);
                $uniTax = $this->calculator->calcTaxAmount($priceInclTax, $rate, true, true);
                $rowTax = $uniTax * $quantity;
                $price = $priceInclTax - $uniTax;
                $rowTotalInclTax = $priceInclTax * $quantity;
                $rowTotal = $price * $quantity;
            }

            //Handle discount
            if ($discountAmount && $applyTaxAfterDiscount) {
                //TODO: handle originalDiscountAmount
                $unitDiscountAmount = $discountAmount / $quantity;
                $taxableAmount = max($priceInclTax - $unitDiscountAmount, 0);
                $unitTaxAfterDiscount = $this->calculator->calcTaxAmount(
                    $taxableAmount,
                    $rate,
                    true,
                    true
                );

                // Set discount tax compensation
                $unitDiscountTaxCompensationAmount = $uniTax - $unitTaxAfterDiscount;
                $discountTaxCompensationAmount = $unitDiscountTaxCompensationAmount * $quantity;
                $rowTax = $unitTaxAfterDiscount * $quantity;
            }

            //save applied taxes
            $appliedTaxes = $this->getAppliedTaxes($appliedTaxBuilder, $rowTax, $rate, $appliedRates);
        } else { //catalog price does not include tax
            $taxable = $price;
            $appliedRates = $this->calculator->getAppliedRates($taxRateRequest);
            $unitTaxes = [];
            $unitTaxesBeforeDiscount = [];
            //Apply each tax rate separately
            foreach ($appliedRates as $appliedRate) {
                $taxId = $appliedRate['id'];
                $taxRate = $appliedRate['percent'];
                $unitTaxPerRate = $this->calculator->calcTaxAmount($taxable, $taxRate, false);
                $unitTaxAfterDiscount = $unitTaxPerRate;

                //Handle discount
                if ($discountAmount && $applyTaxAfterDiscount) {
                    //TODO: handle originalDiscountAmount
                    $unitDiscountAmount = $discountAmount / $quantity;
                    $taxableAmount = max($priceInclTax - $unitDiscountAmount, 0);
                    $unitTaxAfterDiscount = $this->calculator->calcTaxAmount(
                        $taxableAmount,
                        $taxRate,
                        false,
                        true
                    );
                }
                $appliedTaxes[$taxId] = $this->getAppliedTax(
                    $appliedTaxBuilder,
                    $unitTaxAfterDiscount * $quantity,
                    $appliedRate
                );

                $unitTaxes[] = $unitTaxAfterDiscount;
                $unitTaxesBeforeDiscount[] = $unitTaxPerRate;
            }
            $unitTax = array_sum($unitTaxes);
            $unitTaxBeforeDiscount = array_sum($unitTaxesBeforeDiscount);
            $rowTax = $unitTax * $quantity;
            $priceInclTax = $price + $unitTaxBeforeDiscount;
            $rowTotalInclTax = $priceInclTax * $quantity;
        }

        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setRowTax($rowTax);
        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($rowTotal);
        $this->taxDetailsItemBuilder->setRowTotalInclTax($rowTotalInclTax);
        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setTaxPercent($rate);
        $this->taxDetailsItemBuilder->setDiscountTaxCompensationAmount($discountTaxCompensationAmount);

        $this->taxDetailsItemBuilder->setAppliedTaxes($appliedTaxes);
        return $this->taxDetailsItemBuilder->create();
    }

    /**
     * Create AppliedTax data object based applied tax rates and tax amount
     *
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxBuilder $appliedTaxBuilder
     * @param float $rowTax
     * @param array $appliedRate
     * @return \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax
     */
    protected function getAppliedTax($appliedTaxBuilder, $rowTax, $appliedRate)
    {
        $appliedTaxRateBuilder = $appliedTaxBuilder->getAppliedTaxRateBuilder();
        $appliedTaxBuilder->setAmount($rowTax);
        $appliedTaxBuilder->setPercent($appliedRate['percent']);
        $appliedTaxBuilder->setTaxRateKey($appliedRate['id']);

        /** @var  AppliedTaxRate[] $rateDataObjects */
        $rateDataObjects = [];
        foreach ($appliedRate['rates'] as $rate) {
            $appliedTaxRateBuilder->setPercent($rate['percent']);
            $appliedTaxRateBuilder->setCode($rate['code']);
            $appliedTaxRateBuilder->setTitle($rate['title']);
            //Skipped position, priority and rule_id
            $rateDataObjects[$rate['code']] = $appliedTaxRateBuilder->create();
        }
        $appliedTaxBuilder->setRates($rateDataObjects);
        $appliedTax = $appliedTaxBuilder->create();
        return $appliedTax;
    }

    /**
     * Create AppliedTax data object based on applied tax rates and tax amount
     *
     * @param \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxBuilder $appliedTaxBuilder
     * @param float $rowTax
     * @param float $totalTaxRate
     * @param array $appliedRates May contain multiple tax rates when catalog price includes tax
     * @return \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[]
     */
    protected function getAppliedTaxes($appliedTaxBuilder, $rowTax, $totalTaxRate, $appliedRates)
    {
        /** @var \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $appliedTaxes */
        $appliedTaxes = [];
        $appliedTaxRateBuilder = $appliedTaxBuilder->getAppliedTaxRateBuilder();
        $totalAppliedAmount = 0;
        foreach ($appliedRates as $appliedRate) {
            if ($appliedRate['percent'] == 0) {
                continue;
            }

            $appliedAmount = $rowTax / $totalTaxRate * $appliedRate['percent'];
            //Use delta rounding to split tax amounts for each tax rates between items
            $appliedAmount = $this->deltaRound(
                $appliedAmount,
                $appliedRate['id'],
                true,
                self::KEY_APPLIED_TAX_DELTA_ROUNDING
            );
            if ($totalAppliedAmount + $appliedAmount > $rowTax) {
                $appliedAmount = $rowTax - $totalAppliedAmount;
            }
            $totalAppliedAmount += $appliedAmount;

            $appliedTaxBuilder->setAmount($appliedAmount);
            $appliedTaxBuilder->setPercent($appliedRate['percent']);
            $appliedTaxBuilder->setTaxRateKey($appliedRate['id']);

            /** @var  AppliedTaxRate[] $rateDataObjects */
            $rateDataObjects = [];
            foreach ($appliedRate['rates'] as $rate) {
                $appliedTaxRateBuilder->setPercent($rate['percent']);
                $appliedTaxRateBuilder->setCode($rate['code']);
                $appliedTaxRateBuilder->setTitle($rate['title']);
                //Skipped position, priority and rule_id
                $rateDataObjects[$rate['code']] = $appliedTaxRateBuilder->create();
            }
            $appliedTaxBuilder->setRates($rateDataObjects);
            $appliedTax = $appliedTaxBuilder->create();
            $appliedTaxes[$appliedTax->getTaxRateKey()] = $appliedTax;
        }

        return $appliedTaxes;
    }

    /**
     * Calculate item price and row total including/excluding tax based on total price rounding level
     *
     * @param QuoteDetailsItem $item
     * @param \Magento\Framework\Object $taxRateRequest
     * @param int $storeId
     * @return TaxDetailsItem
     */
    protected function totalBaseCalculation(
        QuoteDetailsItem $item,
        \Magento\Framework\Object $taxRateRequest,
        $storeId
    ) {
        /** @var  \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $appliedTaxes */
        $appliedTaxes = [];
        $appliedTaxBuilder = $this->taxDetailsItemBuilder->getAppliedTaxBuilder();
        $appliedTaxRateBuilder = $appliedTaxBuilder->getAppliedTaxRateBuilder();

        $taxRateRequest->setProductClassId($item->getTaxClassId());
        $appliedRates = $this->calculator->getAppliedRates($taxRateRequest);
        $rate = $this->calculator->getRate($taxRateRequest);

        $quantity = $this->getTotalQuantity($item);
        $price = $priceInclTax = $this->calculator->round($item->getUnitPrice());
        $rowTotal = $rowTotalInclTax = $taxableAmount = $this->calcRowTotal($item);

        $discountAmount = $item->getDiscountAmount();
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($storeId);

        $discountTaxCompensationAmount = 0;

        $isTotalBasedCalculation = ($this->config->getAlgorithm($storeId) == Calculation::CALC_TOTAL_BASE);

        if ($item->getTaxIncluded()) {
            if ($taxRateRequest->getSameRateAsStore()) {
                $rowTaxExact = $this->calculator->calcTaxAmount($rowTotalInclTax, $rate, true, false);
                if ($isTotalBasedCalculation) {
                    $rowTax = $this->deltaRound($rowTaxExact, $rate, true);
                } else {
                    $rowTax = $this->calculator->round($rowTaxExact);
                }
                $rowTotal = $rowTotalInclTax - $rowTax;
                $price = $this->calculator->round($rowTotal / $quantity);
            } else {
                $storeRate = $this->calculator->getStoreRate($taxRateRequest, $storeId);
                $priceInclTax = $this->calculatePriceInclTax($price, $storeRate, $rate);
                $rowTotalInclTax = $priceInclTax * $quantity;
                $taxableAmount = $rowTotalInclTax;
                if ($isTotalBasedCalculation) {
                    $rowTax = $this->deltaRound(
                        $this->calculator->calcTaxAmount($rowTotalInclTax, $rate, true, false),
                        $rate,
                        true
                    );
                } else {
                    $rowTax = $this->calculator->calcTaxAmount($rowTotalInclTax, $rate, true, true);
                }
                $rowTotal = $rowTotalInclTax - $rowTax;
                $price = $this->calculator->round($rowTotal / $quantity);
            }

            //Handle discount
            if ($discountAmount && $applyTaxAfterDiscount) {
                //TODO: handle originalDiscountAmount
                $taxableAmount = max($taxableAmount - $discountAmount, 0);
                $rowTaxAfterDiscount = $this->calculator->calcTaxAmount(
                    $taxableAmount,
                    $rate,
                    true,
                    false
                );
                if ($isTotalBasedCalculation) {
                    //Round the row tax using a different type so that we don't pollute the rounding deltas
                    $rowTaxAfterDiscount = $this->deltaRound(
                        $rowTaxAfterDiscount,
                        $rate,
                        true,
                        self::KEY_TAX_AFTER_DISCOUNT_DELTA_ROUNDING
                    );
                } else {
                    $rowTaxAfterDiscount = $this->calculator->round($rowTaxAfterDiscount);
                }

                // Set discount tax compensation
                $discountTaxCompensationAmount = $rowTax - $rowTaxAfterDiscount;
                $rowTax = $rowTaxAfterDiscount;
            }

            //save applied taxes
            $appliedTaxes = $this->getAppliedTaxes(
                $appliedTaxBuilder,
                $rowTax,
                $rate,
                $appliedRates
            );
        } else { //catalog price does not include tax
            $appliedRates = $this->calculator->getAppliedRates($taxRateRequest);
            $rowTaxes = [];
            $rowTaxesBeforeDiscount = [];
            //Apply each tax rate separately
            foreach ($appliedRates as $appliedRate) {
                $taxId = $appliedRate['id'];
                $taxRate = $appliedRate['percent'];
                if ($isTotalBasedCalculation) {
                    $rowTaxPerRate = $this->deltaRound(
                        $this->calculator->calcTaxAmount($rowTotal, $taxRate, false, false),
                        $taxId,
                        false
                    );
                } else {
                    $rowTaxPerRate = $this->calculator->calcTaxAmount($rowTotal, $taxRate, false, true);
                }
                $rowTaxAfterDiscount = $rowTaxPerRate;
                //Handle discount
                if ($discountAmount && $applyTaxAfterDiscount) {
                    //TODO: handle originalDiscountAmount
                    $rowTaxAfterDiscount = $this->calculator->calcTaxAmount(
                        max($rowTotal - $discountAmount, 0),
                        $taxRate,
                        false,
                        false
                    );
                    if ($isTotalBasedCalculation) {
                        //Round the row tax using a different type so that we don't pollute the rounding deltas
                        $rowTaxAfterDiscount = $this->deltaRound(
                            $rowTaxAfterDiscount,
                            $taxRate,
                            false,
                            self::KEY_TAX_AFTER_DISCOUNT_DELTA_ROUNDING
                        );
                    } else {
                        $rowTaxAfterDiscount = $this->calculator->round($rowTaxAfterDiscount);
                    }
                }
                $appliedTaxes[$appliedRate['id']] = $this->getAppliedTax(
                    $appliedTaxBuilder,
                    $rowTaxAfterDiscount,
                    $appliedRate
                );
                $rowTaxes[] = $rowTaxAfterDiscount;
                $rowTaxesBeforeDiscount[] = $rowTaxPerRate;
            }
            $rowTax = array_sum($rowTaxes);
            $rowTaxBeforeDiscount = array_sum($rowTaxesBeforeDiscount);
            $rowTotalInclTax = $rowTotal + $rowTaxBeforeDiscount;
            $priceInclTax = $this->calculator->round($rowTotalInclTax / $quantity);
        }

        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setRowTax($rowTax);
        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($rowTotal);
        $this->taxDetailsItemBuilder->setRowTotalInclTax($rowTotalInclTax);
        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setTaxPercent($rate);
        $this->taxDetailsItemBuilder->setDiscountTaxCompensationAmount($discountTaxCompensationAmount);

        $this->taxDetailsItemBuilder->setAppliedTaxes($appliedTaxes);
        return $this->taxDetailsItemBuilder->create();
    }

    /**
     * Round price based on previous rounding operation delta
     *
     * @param float $price
     * @param string $rate
     * @param bool $direction
     * @param string $type
     * @return float
     */
    protected function deltaRound($price, $rate, $direction, $type = self::KEY_REGULAR_DELTA_ROUNDING)
    {
        if ($price) {
            $rate = (string)$rate;
            $type = $type . $direction;
            // initialize the delta to a small number to avoid non-deterministic behavior with rounding of 0.5
            $delta = isset($this->roundingDeltas[$type][$rate])
                ? $this->roundingDeltas[$type][$rate]
                : 0.000001;
            $price += $delta;
            $roundPrice = $this->calculator->round($price);
            $this->roundingDeltas[$type][$rate] = $price - $roundPrice;
            $price = $roundPrice;
        }
        return $price;
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

        $price = $this->calculator->round($rowTotal / $quantity);
        $priceInclTax = $this->calculator->round($rowTotalInclTax / $quantity);

        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($rowTotal);
        $this->taxDetailsItemBuilder->setRowTotalInclTax($rowTotalInclTax);
        $this->taxDetailsItemBuilder->setRowTax($rowTax);

        return $this->taxDetailsItemBuilder;
    }

    /**
     * Given a store price that includes tax at the store rate, this function will back out the store's tax, and add in
     * the customer's tax.  Returns this new price which is the customer's price including tax.
     *
     * @param float $storePriceInclTax
     * @param float $storeRate
     * @param float $customerRate
     * @return float
     */
    protected function calculatePriceInclTax($storePriceInclTax, $storeRate, $customerRate)
    {
        $storeTax = $this->calculator->calcTaxAmount($storePriceInclTax, $storeRate, true, false);
        $priceExclTax = $storePriceInclTax - $storeTax;
        $customerTax = $this->calculator->calcTaxAmount($priceExclTax, $customerRate, false, false);
        $customerPriceInclTax = $this->calculator->round($priceExclTax + $customerTax);
        return $customerPriceInclTax;
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
        if (!isset($taxDetailsData[TaxDetails::KEY_APPLIED_TAXES])) {
            $taxDetailsData[TaxDetails::KEY_APPLIED_TAXES] = [];
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

    /**
     * Calculate the row total for an item
     *
     * @param QuoteDetailsItem $item
     * @return float
     */
    protected function calcRowTotal(QuoteDetailsItem $item)
    {
        $qty = $this->getTotalQuantity($item);

        // Round unit price before multiplying to prevent losing 1 cent on subtotal
        $total = $this->calculator->round($item->getUnitPrice()) * $qty;

        return $this->calculator->round($total);
    }
}
