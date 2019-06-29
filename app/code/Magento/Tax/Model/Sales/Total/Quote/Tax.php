<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Model\Calculation;

/**
 * Tax totals calculation model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tax extends CommonTaxCollector
{
    /**
     * Counter
     *
     * @var int
     */
    protected $counter = 0;

    /**
     * Tax module helper
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * Discount tax compensationes array
     *
     * @var array
     */
    protected $_discountTaxCompensationes = [];

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param CustomerAddressFactory $customerAddressFactory
     * @param CustomerAddressRegionFactory $customerAddressRegionFactory
     * @param \Magento\Tax\Helper\Data $taxData
     * @param Json $serializer
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        CustomerAddressFactory $customerAddressFactory,
        CustomerAddressRegionFactory $customerAddressRegionFactory,
        \Magento\Tax\Helper\Data $taxData,
        Json $serializer = null
    ) {
        $this->setCode('tax');
        $this->_taxData = $taxData;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsDataObjectFactory,
            $quoteDetailsItemDataObjectFactory,
            $taxClassKeyDataObjectFactory,
            $customerAddressFactory,
            $customerAddressRegionFactory
        );
    }

    /**
     * Collect tax totals for quote address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $baseTaxDetails = $this->getQuoteTaxDetails($shippingAssignment, $total, true);
        $taxDetails = $this->getQuoteTaxDetails($shippingAssignment, $total, false);

        //Populate address and items with tax calculation results
        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);
        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT], $total);
        }

        if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
            $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_ITEM];
            $baseShippingTaxDetails =
                $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_BASE_ITEM];
            $this->processShippingTaxInfo($shippingAssignment, $total, $shippingTaxDetails, $baseShippingTaxDetails);
        }

        //Process taxable items that are not product or shipping
        $this->processExtraTaxables($total, $itemsByType);

        //Save applied taxes for each item and the quote in aggregation
        $this->processAppliedTaxes($total, $shippingAssignment, $itemsByType);

        if ($this->includeExtraTax()) {
            $total->addTotalAmount('extra_tax', $total->getExtraTaxAmount());
            $total->addBaseTotalAmount('extra_tax', $total->getBaseExtraTaxAmount());
        }

        return $this;
    }

    /**
     * Clear tax related total values in address
     *
     * @param Address\Total $total
     * @return void
     */
    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('shipping', 0);
        $total->setBaseTotalAmount('shipping', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
        $total->setShippingInclTax(0);
        $total->setBaseShippingInclTax(0);
        $total->setShippingTaxAmount(0);
        $total->setBaseShippingTaxAmount(0);
        $total->setShippingAmountForDiscount(0);
        $total->setBaseShippingAmountForDiscount(0);
        $total->setBaseShippingAmountForDiscount(0);
        $total->setTotalAmount('extra_tax', 0);
        $total->setBaseTotalAmount('extra_tax', 0);
    }

    /**
     * Call tax calculation service to get tax details on the quote and items
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\TaxDetailsInterface
     */
    protected function getQuoteTaxDetails($shippingAssignment, $total, $useBaseCurrency)
    {
        $address = $shippingAssignment->getShipping()->getAddress();
        //Setup taxable items
        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());
        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, $useBaseCurrency);

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, $useBaseCurrency);
        if ($shippingDataObject != null) {
            $itemDataObjects[] = $shippingDataObject;
        }

        //process extra taxable items associated only with quote
        $quoteExtraTaxables = $this->mapQuoteExtraTaxables(
            $this->quoteDetailsItemDataObjectFactory,
            $address,
            $useBaseCurrency
        );
        if (!empty($quoteExtraTaxables)) {
            $itemDataObjects = array_merge($itemDataObjects, $quoteExtraTaxables);
        }

        //Preparation for calling taxCalculationService
        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);

        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());

        return $taxDetails;
    }

    /**
     * Map extra taxables associated with quote
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return \Magento\Tax\Api\Data\QuoteDetailsItemInterface[]
     */
    public function mapQuoteExtraTaxables(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        Address $address,
        $useBaseCurrency
    ) {
        $itemDataObjects = [];
        $extraTaxables = $address->getAssociatedTaxables();
        if (!$extraTaxables) {
            return [];
        }

        foreach ($extraTaxables as $extraTaxable) {
            if ($useBaseCurrency) {
                $unitPrice = $extraTaxable[self::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE];
            } else {
                $unitPrice = $extraTaxable[self::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE];
            }
            $itemDataObjects[] = $itemDataObjectFactory->create()
                ->setCode($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_CODE])
                ->setType($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_TYPE])
                ->setQuantity($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_QUANTITY])
                ->setTaxClassKey(
                    $this->taxClassKeyDataObjectFactory->create()
                        ->setType(TaxClassKeyInterface::TYPE_ID)
                        ->setValue($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID])
                )
                ->setUnitPrice($unitPrice)
                ->setIsTaxIncluded($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX])
                ->setAssociatedItemCode($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE]);
        }

        return $itemDataObjects;
    }

    /**
     * Process everything other than product or shipping, save the result in quote
     *
     * @param Address\Total $total
     * @param array $itemsByType
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function processExtraTaxables(Address\Total $total, array $itemsByType)
    {
        $extraTaxableDetails = [];
        foreach ($itemsByType as $itemType => $itemTaxDetails) {
            if ($itemType != self::ITEM_TYPE_PRODUCT && $itemType != self::ITEM_TYPE_SHIPPING) {
                foreach ($itemTaxDetails as $itemCode => $itemTaxDetail) {
                    /** @var \Magento\Tax\Api\Data\TaxDetailsInterface $taxDetails */
                    $taxDetails = $itemTaxDetail[self::KEY_ITEM];
                    /** @var \Magento\Tax\Api\Data\TaxDetailsInterface $baseTaxDetails */
                    $baseTaxDetails = $itemTaxDetail[self::KEY_BASE_ITEM];

                    $appliedTaxes = $taxDetails->getAppliedTaxes();
                    $baseAppliedTaxes = $baseTaxDetails->getAppliedTaxes();

                    $associatedItemCode = $taxDetails->getAssociatedItemCode();

                    $appliedTaxesArray = $this->convertAppliedTaxes($appliedTaxes, $baseAppliedTaxes);
                    $extraTaxableDetails[$itemType][$associatedItemCode][] = [
                        self::KEY_TAX_DETAILS_TYPE => $taxDetails->getType(),
                        self::KEY_TAX_DETAILS_CODE => $taxDetails->getCode(),
                        self::KEY_TAX_DETAILS_PRICE_EXCL_TAX => $taxDetails->getPrice(),
                        self::KEY_TAX_DETAILS_PRICE_INCL_TAX => $taxDetails->getPriceInclTax(),
                        self::KEY_TAX_DETAILS_BASE_PRICE_EXCL_TAX => $baseTaxDetails->getPrice(),
                        self::KEY_TAX_DETAILS_BASE_PRICE_INCL_TAX => $baseTaxDetails->getPriceInclTax(),
                        self::KEY_TAX_DETAILS_ROW_TOTAL => $taxDetails->getRowTotal(),
                        self::KEY_TAX_DETAILS_ROW_TOTAL_INCL_TAX => $taxDetails->getRowTotalInclTax(),
                        self::KEY_TAX_DETAILS_BASE_ROW_TOTAL => $baseTaxDetails->getRowTotal(),
                        self::KEY_TAX_DETAILS_BASE_ROW_TOTAL_INCL_TAX => $baseTaxDetails->getRowTotalInclTax(),
                        self::KEY_TAX_DETAILS_TAX_PERCENT => $taxDetails->getTaxPercent(),
                        self::KEY_TAX_DETAILS_ROW_TAX => $taxDetails->getRowTax(),
                        self::KEY_TAX_DETAILS_BASE_ROW_TAX => $baseTaxDetails->getRowTax(),
                        self::KEY_TAX_DETAILS_APPLIED_TAXES => $appliedTaxesArray,
                    ];

                    $total->addTotalAmount('tax', $taxDetails->getRowTax());
                    $total->addBaseTotalAmount('tax', $baseTaxDetails->getRowTax());
                    //TODO: save applied taxes for the item
                }
            }
        }

        $total->setExtraTaxableDetails($extraTaxableDetails);
        return $this;
    }

    /**
     * Add tax totals information to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $totals = [];
        $store = $quote->getStore();
        $applied = $total->getAppliedTaxes();
        if (is_string($applied)) {
            $applied = $this->serializer->unserialize($applied);
        }
        $amount = $total->getTaxAmount();
        if ($amount === null) {
            $this->enhanceTotalData($quote, $total);
            $amount = $total->getTaxAmount();
        }
        $taxAmount = $amount + $total->getTotalAmount('discount_tax_compensation');

        $area = null;
        if ($this->_config->displayCartTaxWithGrandTotal($store) && $total->getGrandTotal()) {
            $area = 'taxes';
        }

        $totals[] = [
            'code' => $this->getCode(),
            'title' => __('Tax'),
            'full_info' => $applied ? $applied : [],
            'value' => $amount,
            'area' => $area,
        ];

        /**
         * Modify subtotal
         */
        if ($this->_config->displayCartSubtotalBoth($store) || $this->_config->displayCartSubtotalInclTax($store)) {
            if ($total->getSubtotalInclTax() > 0) {
                $subtotalInclTax = $total->getSubtotalInclTax();
            } else {
                $subtotalInclTax = $total->getSubtotal() + $taxAmount - $total->getShippingTaxAmount();
            }

            $totals[] = [
                'code' => 'subtotal',
                'title' => __('Subtotal'),
                'value' => $subtotalInclTax,
                'value_incl_tax' => $subtotalInclTax,
                'value_excl_tax' => $total->getSubtotal(),
            ];
        }

        if (empty($totals)) {
            return null;
        }
        return $totals;
    }

    /**
     * Adds minimal tax information to the "total" data structure
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return null
     */
    protected function enhanceTotalData(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $taxAmount = 0;
        $shippingTaxAmount = 0;
        $discountTaxCompensation = 0;

        $subtotalInclTax = $total->getSubtotalInclTax();
        $computeSubtotalInclTax = true;
        if ($total->getSubtotalInclTax() > 0) {
            $computeSubtotalInclTax = false;
        }

        /** @var \Magento\Quote\Model\Quote\Address $address */
        foreach ($quote->getAllAddresses() as $address) {
            $taxAmount += $address->getTaxAmount();
            $shippingTaxAmount += $address->getShippingTaxAmount();
            $discountTaxCompensation += $address->getDiscountTaxCompensationAmount();
            if ($computeSubtotalInclTax) {
                $subtotalInclTax += $address->getSubtotalInclTax();
            }
        }

        $total->setTaxAmount($taxAmount);
        $total->setShippingTaxAmount($shippingTaxAmount);
        $total->setDiscountTaxCompensationAmount($discountTaxCompensation); // accessed via 'discount_tax_compensation'
        $total->setSubtotalInclTax($subtotalInclTax);
        return;
    }

    /**
     * Process model configuration array.
     *
     * This method can be used for changing totals collect sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store)
    {
        $calculationSequence = $this->_taxData->getCalculationSequence($store);
        switch ($calculationSequence) {
            case Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $config['before'][] = 'discount';
                break;
            default:
                $config['after'][] = 'discount';
                break;
        }
        return $config;
    }

    /**
     * Get Tax label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Tax');
    }
}
