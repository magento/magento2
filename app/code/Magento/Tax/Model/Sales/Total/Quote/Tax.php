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
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Store\Model\Store;
use Magento\Sales\Model\Quote\Address;
use Magento\Sales\Model\Quote\Address\Total\AbstractTotal;
use Magento\Tax\Model\Calculation;
use Magento\Sales\Model\Quote\Item\AbstractItem;
use Magento\Customer\Service\V1\Data\AddressBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetailsBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetails\ItemBuilder;
use Magento\Tax\Service\V1\Data\QuoteDetails\Item as ItemDataObject;
use Magento\Tax\Service\V1\Data\TaxClassKey;
use Magento\Tax\Service\V1\Data\TaxDetails;

/**
 * Tax totals calculation model
 */
class Tax extends CommonTaxCollector
{
    /**
     * Static counter
     *
     * @var int
     */
    protected static $counter = 0;

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
     * Hidden taxes array
     *
     * @var array
     */
    protected $_hiddenTaxes = array();

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Service\V1\TaxCalculationService $taxCalculationService
     * @param \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder $quoteDetailsBuilder
     * @param \Magento\Tax\Helper\Data $taxData
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Service\V1\TaxCalculationService $taxCalculationService,
        \Magento\Tax\Service\V1\Data\QuoteDetailsBuilder $quoteDetailsBuilder,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->setCode('tax');
        $this->_taxData = $taxData;
        parent::__construct($taxConfig, $taxCalculationService, $quoteDetailsBuilder);
    }

    /**
     * Collect tax totals for quote address
     *
     * @param   Address $address
     * @return  $this
     */
    public function collect(Address $address)
    {
        parent::collect($address);
        $items = $this->_getAddressItems($address);
        if (!$items) {
            return $this;
        }

        $baseTaxDetails = $this->getQuoteTaxDetails($address, true);
        $taxDetails = $this->getQuoteTaxDetails($address, false);

        //Populate address and items with tax calculation results
        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);
        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($address, $itemsByType[self::ITEM_TYPE_PRODUCT]);
        }

        if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
            $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_ITEM];
            $baseShippingTaxDetails =
                $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_BASE_ITEM];
            $this->processShippingTaxInfo($address, $shippingTaxDetails, $baseShippingTaxDetails);
        }

        //Process taxable items that are not product or shipping
        $this->processExtraTaxables($address, $itemsByType);

        //Save applied taxes for each item and the quote in aggregation
        $this->processAppliedTaxes($address, $itemsByType);

        if ($this->includeExtraTax()) {
            $this->_addAmount($address->getExtraTaxAmount());
            $this->_addBaseAmount($address->getBaseExtraTaxAmount());
        }

        return $this;
    }

    /**
     * Call tax calculation service to get tax details on the quote and items
     *
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return TaxDetails
     */
    protected function getQuoteTaxDetails($address, $useBaseCurrency)
    {
        //Setup taxable items
        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());
        $itemDataObjects = $this->mapItems($address, $priceIncludesTax, $useBaseCurrency);

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($address, $useBaseCurrency);
        if ($shippingDataObject != null) {
            $itemDataObjects[] = $shippingDataObject;
        }

        //process extra taxable items associated only with quote
        $quoteExtraTaxables = $this->mapQuoteExtraTaxables(
            $this->quoteDetailsBuilder->getItemBuilder(),
            $address,
            $useBaseCurrency
        );
        if (!empty($quoteExtraTaxables)) {
            $itemDataObjects = array_merge($itemDataObjects, $quoteExtraTaxables);
        }

        //Preparation for calling taxCalculationService
        $quoteDetails = $this->prepareQuoteDetails($address, $itemDataObjects);

        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());

        return $taxDetails;
    }


    /**
     * Map extra taxables associated with quote
     *
     * @param ItemBuilder $itemBuilder
     * @param Address $address
     * @param bool $useBaseCurrency
     * @return ItemDataObject[]
     */
    public function mapQuoteExtraTaxables(
        ItemBuilder $itemBuilder,
        Address $address,
        $useBaseCurrency
    ) {
        $itemDataObjects = [];
        $extraTaxables = $address->getAssociatedTaxables();
        if (!$extraTaxables) {
            return [];
        }

        foreach ($extraTaxables as $extraTaxable) {
            $itemBuilder->setCode($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_CODE]);
            $itemBuilder->setType($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_TYPE]);
            $itemBuilder->setQuantity($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_QUANTITY]);
            $itemBuilder->setTaxClassKey(
                $itemBuilder->getTaxClassKeyBuilder()
                    ->setType(TaxClassKey::TYPE_ID)
                    ->setValue($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID])
                    ->create()
            );
            if ($useBaseCurrency) {
                $unitPrice = $extraTaxable[self::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE];
            } else {
                $unitPrice = $extraTaxable[self::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE];
            }
            $itemBuilder->setUnitPrice($unitPrice);
            $itemBuilder->setTaxIncluded($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX]);
            $itemBuilder->setAssociatedItemCode($extraTaxable[self::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE]);
            $itemDataObjects[] = $itemBuilder->create();
        }

        return $itemDataObjects;
    }

    /**
     * Process everything other than product or shipping, save the result in quote
     *
     * @param Address $address
     * @param array $itemsByType
     * @return $this
     */
    protected function processExtraTaxables(Address $address, Array $itemsByType)
    {
        $extraTaxableDetails = [];
        foreach ($itemsByType as $itemType => $itemTaxDetails) {
            if ($itemType != self::ITEM_TYPE_PRODUCT and $itemType != self::ITEM_TYPE_SHIPPING) {
                foreach ($itemTaxDetails as $itemCode => $itemTaxDetail) {
                    /** @var ItemTaxDetails $taxDetails */
                    $taxDetails = $itemTaxDetail[self::KEY_ITEM];
                    /** @var ItemTaxDetails $baseTaxDetails */
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

                    $address->addTotalAmount('tax', $taxDetails->getRowTax());
                    $address->addBaseTotalAmount('tax', $baseTaxDetails->getRowTax());
                    //TODO: save applied taxes for the item
                }
            }
        }

        $address->setExtraTaxableDetails($extraTaxableDetails);
        return $this;
    }

    /**
     * Add tax totals information to address object
     *
     * @param   Address $address
     * @return  $this
     */
    public function fetch(Address $address)
    {
        $applied = $address->getAppliedTaxes();
        $store = $address->getQuote()->getStore();
        $amount = $address->getTaxAmount();

        $items = $this->_getAddressItems($address);
        $discountTaxCompensation = 0;
        foreach ($items as $item) {
            $discountTaxCompensation += $item->getDiscountTaxCompensation();
        }
        $taxAmount = $amount + $discountTaxCompensation;

        $area = null;
        if ($this->_config->displayCartTaxWithGrandTotal($store) && $address->getGrandTotal()) {
            $area = 'taxes';
        }

        if ($amount != 0 || $this->_config->displayCartZeroTax($store)) {
            $address->addTotal(
                array(
                    'code' => $this->getCode(),
                    'title' => __('Tax'),
                    'full_info' => $applied ? $applied : array(),
                    'value' => $amount,
                    'area' => $area
                )
            );
        }

        $store = $address->getQuote()->getStore();
        /**
         * Modify subtotal
         */
        if ($this->_config->displayCartSubtotalBoth($store) || $this->_config->displayCartSubtotalInclTax($store)) {
            if ($address->getSubtotalInclTax() > 0) {
                $subtotalInclTax = $address->getSubtotalInclTax();
            } else {
                $subtotalInclTax = $address->getSubtotal() + $taxAmount - $address->getShippingTaxAmount();
            }

            $address->addTotal(
                array(
                    'code' => 'subtotal',
                    'title' => __('Subtotal'),
                    'value' => $subtotalInclTax,
                    'value_incl_tax' => $subtotalInclTax,
                    'value_excl_tax' => $address->getSubtotal()
                )
            );
        }

        return $this;
    }

    /**
     * Process model configuration array.
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
     * @return string
     */
    public function getLabel()
    {
        return __('Tax');
    }
}
