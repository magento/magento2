<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation;

use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractCalculator
{
    /**#@+
     * Constants for delta rounding key
     */
    public const KEY_REGULAR_DELTA_ROUNDING = 'regular';

    public const KEY_APPLIED_TAX_DELTA_ROUNDING = 'applied_tax_amount';

    public const KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING = 'tax_before_discount';
    /**#@-*/

    /**
     * @var TaxDetailsItemInterfaceFactory
     */
    protected $taxDetailsItemDataObjectFactory;

    /**
     * @var Calculation
     */
    protected $calculationTool;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var int
     */
    protected $customerTaxClassId;

    /**
     * @var int
     */
    protected $customerId;

    /**
     * @var CustomerAddress
     */
    protected $shippingAddress;

    /**
     * @var CustomerAddress
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
     * @var \Magento\Framework\DataObject
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
     * @var TaxClassManagementInterface
     */
    protected $taxClassManagement;

    /**
     * @var AppliedTaxInterfaceFactory
     */
    protected $appliedTaxDataObjectFactory;

    /**
     * @var AppliedTaxRateInterfaceFactory
     */
    protected $appliedTaxRateDataObjectFactory;

    /**
     * Constructor
     *
     * @param TaxClassManagementInterface $taxClassService
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
     * @param Calculation $calculationTool
     * @param \Magento\Tax\Model\Config $config
     * @param int $storeId
     * @param \Magento\Framework\DataObject $addressRateRequest
     */
    public function __construct(
        TaxClassManagementInterface $taxClassService,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory,
        Calculation $calculationTool,
        \Magento\Tax\Model\Config $config,
        $storeId,
        \Magento\Framework\DataObject $addressRateRequest = null
    ) {
        $this->taxClassManagement = $taxClassService;
        $this->taxDetailsItemDataObjectFactory = $taxDetailsItemDataObjectFactory;
        $this->appliedTaxDataObjectFactory = $appliedTaxDataObjectFactory;
        $this->appliedTaxRateDataObjectFactory = $appliedTaxRateDataObjectFactory;
        $this->calculationTool = $calculationTool;
        $this->config = $config;
        $this->storeId = $storeId;
        $this->addressRateRequest = $addressRateRequest;
    }

    /**
     * Set billing address
     *
     * @codeCoverageIgnoreStart
     * @param CustomerAddress $billingAddress
     * @return void
     */
    public function setBillingAddress(CustomerAddress $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * Set shipping address
     *
     * @param CustomerAddress $shippingAddress
     * @return void
     */
    public function setShippingAddress(CustomerAddress $shippingAddress)
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

    // @codeCoverageIgnoreEnd

    /**
     * Calculate tax details for quote item with given quantity
     *
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @return TaxDetailsItemInterface
     */
    public function calculate(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        if ($item->getIsTaxIncluded()) {
            return $this->calculateWithTaxInPrice($item, $quantity, $round);
        } else {
            return $this->calculateWithTaxNotInPrice($item, $quantity, $round);
        }
    }

    /**
     * Calculate tax details for quote item with tax in price with given quantity
     *
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @return TaxDetailsItemInterface
     */
    abstract protected function calculateWithTaxInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true);

    /**
     * Calculate tax details for quote item with tax not in price with given quantity
     *
     * @param QuoteDetailsItemInterface $item
     * @param int $quantity
     * @param bool $round
     * @return TaxDetailsItemInterface
     */
    abstract protected function calculateWithTaxNotInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true);

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
     * @return \Magento\Framework\DataObject
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
        if ((bool)$this->config->crossBorderTradeEnabled($this->storeId) && $rate > 0.0) {
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
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface
     */
    protected function getAppliedTax($rowTax, $appliedRate)
    {
        $appliedTaxDataObject = $this->appliedTaxDataObjectFactory->create();
        $appliedTaxDataObject->setAmount($rowTax);
        $appliedTaxDataObject->setPercent($appliedRate['percent']);
        $appliedTaxDataObject->setTaxRateKey($appliedRate['id']);

        /** @var  \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rateDataObjects */
        $rateDataObjects = [];
        foreach ($appliedRate['rates'] as $rate) {
            //Skipped position, priority and rule_id
            $rateDataObjects[$rate['code']] = $this->appliedTaxRateDataObjectFactory->create()
                ->setPercent($rate['percent'])
                ->setCode($rate['code'])
                ->setTitle($rate['title']);
        }
        $appliedTaxDataObject->setRates($rateDataObjects);
        return $appliedTaxDataObject;
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
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface[]
     */
    protected function getAppliedTaxes($rowTax, $totalTaxRate, $appliedRates)
    {
        /** @var \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes */
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

            $appliedTaxDataObject = $this->appliedTaxDataObjectFactory->create();
            $appliedTaxDataObject->setAmount($appliedAmount);
            $appliedTaxDataObject->setPercent($appliedRate['percent']);
            $appliedTaxDataObject->setTaxRateKey($appliedRate['id']);

            /** @var  \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rateDataObjects */
            $rateDataObjects = [];
            foreach ($appliedRate['rates'] as $rate) {
                //Skipped position, priority and rule_id
                $rateDataObjects[$rate['code']] = $this->appliedTaxRateDataObjectFactory->create()
                    ->setPercent($rate['percent'])
                    ->setCode($rate['code'])
                    ->setTitle($rate['title']);
            }
            $appliedTaxDataObject->setRates($rateDataObjects);
            $appliedTaxes[$appliedTaxDataObject->getTaxRateKey()] = $appliedTaxDataObject;
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
     * @param bool $round
     * @return float
     */
    protected function deltaRound($price, $rate, $direction, $type = self::KEY_REGULAR_DELTA_ROUNDING, $round = true)
    {
        if ($price) {
            $rate = (string)$rate;
            $type = $type . $direction;
            // initialize the delta to a small number to avoid non-deterministic behavior with rounding of 0.5
            $delta = isset($this->roundingDeltas[$type][$rate]) ?
                $this->roundingDeltas[$type][$rate] :
                0.000001;
            $price += $delta;
            $roundPrice = $price;
            if ($round) {
                $roundPrice = $this->calculationTool->round($roundPrice);
            }
            $this->roundingDeltas[$type][$rate] = $price - $roundPrice;
            $price = $roundPrice;
        }
        return $price;
    }

    /**
     * Given a store price that includes tax at the store rate, this function will back out the store's tax, ...
     *
     * ... and add in the customer's tax. Returns this new price which is the customer's price including tax.
     *
     * @param float $storePriceInclTax
     * @param float $storeRate
     * @param float $customerRate
     * @param boolean $round
     * @return float
     */
    protected function calculatePriceInclTax($storePriceInclTax, $storeRate, $customerRate, $round = true)
    {
        $storeTax = $this->calculationTool->calcTaxAmount($storePriceInclTax, $storeRate, true, false);
        $priceExclTax = $storePriceInclTax - $storeTax;
        $customerTax = $this->calculationTool->calcTaxAmount($priceExclTax, $customerRate, false, false);
        $customerPriceInclTax = $priceExclTax + $customerTax;
        if ($round) {
            $customerPriceInclTax = $this->calculationTool->round($customerPriceInclTax);
        }
        return $customerPriceInclTax;
    }
}
