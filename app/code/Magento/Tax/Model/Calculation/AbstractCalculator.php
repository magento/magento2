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
namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Model\Calculation;
use Magento\Customer\Service\V1\Data\Address;
use Magento\Tax\Service\V1\Data\QuoteDetails\Item as QuoteDetailsItem;
use Magento\Tax\Service\V1\Data\QuoteDetails;
use Magento\Tax\Service\V1\Data\TaxDetails\ItemBuilder as TaxDetailsItemBuilder;
use Magento\Tax\Service\V1\Data\TaxDetails\Item as TaxDetailsItem;
use Magento\Tax\Service\V1\Data\TaxClassKey;
use \Magento\Tax\Service\V1\Data\TaxClass;
use Magento\Tax\Service\V1\TaxClassService;

abstract class AbstractCalculator
{
    /**#@+
     * Constants for delta rounding key
     */
    const KEY_REGULAR_DELTA_ROUNDING = 'regular';

    const KEY_APPLIED_TAX_DELTA_ROUNDING = 'applied_tax_amount';

    const KEY_TAX_AFTER_DISCOUNT_DELTA_ROUNDING = 'tax_after_discount';
    /**#@-*/

    /**
     * Tax details item builder
     *
     * @var TaxDetailsItemBuilder
     */
    protected $taxDetailsItemBuilder;

    /**
     * Tax calculation tool
     *
     * @var Calculation
     */
    protected $calculationTool;

    /**
     * Store id
     *
     * @var int
     */
    protected $storeId;

    /**
     * Customer tax class id
     *
     * @var int
     */
    protected $customerTaxClassId;

    /**
     * Customer id
     *
     * @var int
     */
    protected $customerId;

    /**
     * Shipping Address
     *
     * @var Address
     */
    protected $shippingAddress;

    /**
     * Billing Address
     *
     * @var Address
     */
    protected $billingAddress;

    /**
     * Tax configuration object
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $config;

    /**
     * Address rate request
     *
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @var \Magento\Framework\Object
     */
    private $addressRateRequest = null;

    /**
     * Rounding deltas for prices
     *
     * @var string[]
     * example:
     *  [
     *      'type' => [
     *          'rate' => 'rounding delta',
     *      ],
     *  ]
     */
    protected $roundingDeltas;

    /**
     * Tax Class Service
     *
     * @var TaxClassService
     */
    protected $taxClassService;

    /**
     * Constructor
     *
     * @param TaxClassService $taxClassService
     * @param TaxDetailsItemBuilder $taxDetailsItemBuilder
     * @param Calculation $calculationTool
     * @param \Magento\Tax\Model\Config $config
     * @param int $storeId
     * @param \Magento\Framework\Object $addressRateRequest
     */
    public function __construct(
        TaxClassService $taxClassService,
        TaxDetailsItemBuilder $taxDetailsItemBuilder,
        Calculation $calculationTool,
        \Magento\Tax\Model\Config $config,
        $storeId,
        \Magento\Framework\Object $addressRateRequest = null
    ) {
        $this->taxClassService = $taxClassService;
        $this->taxDetailsItemBuilder = $taxDetailsItemBuilder;
        $this->calculationTool = $calculationTool;
        $this->config = $config;
        $this->storeId = $storeId;
        $this->addressRateRequest = $addressRateRequest;
    }

    /**
     * Set billing address
     *
     * @param Address $billingAddress
     * @return void
     */
    public function setBillingAddress(Address $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * Set shipping address
     *
     * @param Address $shippingAddress
     * @return void
     */
    public function setShippingAddress(Address $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * Set customer tax class id
     *
     * @param int $customerTaxClassId
     * @return void
     */
    public function setCustomerTaxClassId($customerTaxClassId)
    {
        $this->customerTaxClassId = $customerTaxClassId;
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return void
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * Calculate tax details for quote item with given quantity
     *
     * @param QuoteDetailsItem $item
     * @param int $quantity
     * @return TaxDetailsItem
     */
    public function calculate(QuoteDetailsItem $item, $quantity)
    {
        if ($item->getTaxIncluded()) {
            return $this->calculateWithTaxInPrice($item, $quantity);
        } else {
            return $this->calculateWithTaxNotInPrice($item, $quantity);
        }
    }

    /**
     * Calculate tax details for quote item with tax in price with given quantity
     *
     * @param QuoteDetailsItem $item
     * @param int $quantity
     * @return TaxDetailsItem
     */
    abstract protected function calculateWithTaxInPrice(QuoteDetailsItem $item, $quantity);

    /**
     * Calculate tax details for quote item with tax not in price with given quantity
     *
     * @param QuoteDetailsItem $item
     * @param int $quantity
     * @return TaxDetailsItem
     */
    abstract protected function calculateWithTaxNotInPrice(QuoteDetailsItem $item, $quantity);

    /**
     * Get address rate request
     *
     * Request object contain:
     *  country_id (->getCountryId())
     *  region_id (->getRegionId())
     *  postcode (->getPostcode())
     *  customer_class_id (->getCustomerClassId())
     *  store (->getStore())
     *
     * @return \Magento\Framework\Object
     */
    protected function getAddressRateRequest()
    {
        if (null == $this->addressRateRequest) {
            $this->addressRateRequest = $this->calculationTool->getRateRequest(
                $this->shippingAddress,
                $this->billingAddress,
                $this->customerTaxClassId,
                $this->storeId,
                $this->customerId
            );
        }
        return $this->addressRateRequest;
    }

    /**
     * Check if tax rate is same as store tax rate
     *
     * @param float $rate
     * @param float $storeRate
     * @return bool
     */
    protected function isSameRateAsStore($rate, $storeRate)
    {
        if ((bool)$this->config->crossBorderTradeEnabled($this->storeId)) {
            return true;
        } else {
            return (abs($rate - $storeRate) < 0.00001);
        }
    }

    /**
     * Create AppliedTax data object based applied tax rates and tax amount
     *
     * @param float $rowTax
     * @param array $appliedRate
     * example:
     *  [
     *      'id' => 'id',
     *      'percent' => 7.5,
     *      'rates' => [
     *          'code' => 'code',
     *          'title' => 'title',
     *          'percent' => 5.3,
     *      ],
     *  ]
     * @return \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax
     */
    protected function getAppliedTax($rowTax, $appliedRate)
    {
        $appliedTaxBuilder = $this->taxDetailsItemBuilder->getAppliedTaxBuilder();
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
     * @param float $rowTax
     * @param float $totalTaxRate
     * @param array $appliedRates May contain multiple tax rates when catalog price includes tax
     * example:
     *  [
     *      [
     *          'id' => 'id1',
     *          'percent' => 7.5,
     *          'rates' => [
     *              'code' => 'code1',
     *              'title' => 'title1',
     *              'percent' => 5.3,
     *          ],
     *      ],
     *      [
     *          'id' => 'id2',
     *          'percent' => 8.5,
     *          'rates' => [
     *              'code' => 'code2',
     *              'title' => 'title2',
     *              'percent' => 7.3,
     *          ],
     *      ],
     *  ]
     * @return \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[]
     */
    protected function getAppliedTaxes($rowTax, $totalTaxRate, $appliedRates)
    {
        $appliedTaxBuilder = $this->taxDetailsItemBuilder->getAppliedTaxBuilder();
        $appliedTaxRateBuilder = $appliedTaxBuilder->getAppliedTaxRateBuilder();
        /** @var \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $appliedTaxes */
        $appliedTaxes = [];
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
            $delta = isset($this->roundingDeltas[$type][$rate]) ?
                $this->roundingDeltas[$type][$rate] :
                0.000001;
            $price += $delta;
            $roundPrice = $this->calculationTool->round($price);
            $this->roundingDeltas[$type][$rate] = $price - $roundPrice;
            $price = $roundPrice;
        }
        return $price;
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
        $storeTax = $this->calculationTool->calcTaxAmount($storePriceInclTax, $storeRate, true, false);
        $priceExclTax = $storePriceInclTax - $storeTax;
        $customerTax = $this->calculationTool->calcTaxAmount($priceExclTax, $customerRate, false, false);
        $customerPriceInclTax = $this->calculationTool->round($priceExclTax + $customerTax);
        return $customerPriceInclTax;
    }
}
