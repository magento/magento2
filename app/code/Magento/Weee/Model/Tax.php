<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Tax\Model\CalculationFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Tax extends AbstractModel
{
    /**
     * Including FPT only
     */
    const DISPLAY_INCL = 0;

    /**
     * Including FPT and FPT description
     */
    const DISPLAY_INCL_DESCR = 1;

    /**
     * Excluding FPT. Including FPT description and final price
     */
    const DISPLAY_EXCL_DESCR_INCL = 2;

    /**
     * Excluding FPT
     */
    const DISPLAY_EXCL = 3;

    /**
     * @var array|null
     */
    protected $_allAttributes = null;

    /**
     * Tax data
     *
     * @var Data
     */
    protected $_taxData = null;

    /**
     * @var AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CalculationFactory
     */
    protected $_calculationFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param AttributeFactory $attributeFactory
     * @param StoreManagerInterface $storeManager
     * @param CalculationFactory $calculationFactory
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param Data $taxData
     * @param ResourceModel\Tax $resource
     * @param Config $weeeConfig
     * @param PriceCurrencyInterface $priceCurrency
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AttributeFactory $attributeFactory,
        StoreManagerInterface $storeManager,
        CalculationFactory $calculationFactory,
        Session $customerSession,
        protected AccountManagementInterface $accountManagement,
        Data $taxData,
        ResourceModel\Tax $resource,
        protected Config $weeeConfig,
        protected PriceCurrencyInterface $priceCurrency,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_storeManager = $storeManager;
        $this->_calculationFactory = $calculationFactory;
        $this->_customerSession = $customerSession;
        $this->_taxData = $taxData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Tax::class);
    }

    /**
     * @param Product $product
     * @param null|false|DataObject $shipping
     * @param null|false|DataObject $billing
     * @param Website $website
     * @param bool $calculateTax
     * @return float
     */
    public function getWeeeAmount(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = false
    ) {
        $amount = 0;
        $attributes = $this->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            $calculateTax
        );
        foreach ($attributes as $attribute) {
            $amount += $attribute->getAmount();
        }
        return $amount;
    }

    /**
     * @param Product $product
     * @param null|false|DataObject $shipping
     * @param null|false|DataObject $billing
     * @param Website $website
     * @return float
     */
    public function getWeeeAmountExclTax(
        $product,
        $shipping = null,
        $billing = null,
        $website = null
    ) {
        $amountExclTax = 0;
        $attributes = $this->getProductWeeeAttributes(
            $product,
            $shipping,
            $billing,
            $website,
            true,
            false
        );
        if (Type::TYPE_BUNDLE !== $product->getTypeId() || $product->getPriceType()) {
            foreach ($attributes as $attribute) {
                $amountExclTax += $attribute->getAmountExclTax();
            }
        }
        return $amountExclTax;
    }

    /**
     * @param bool $forceEnabled
     * @return array
     */
    public function getWeeeAttributeCodes($forceEnabled = false)
    {
        return $this->getWeeeTaxAttributeCodes(null, $forceEnabled);
    }

    /**
     * Retrieve Wee tax attribute codes
     *
     * @param null|string|bool|int|Store $store
     * @param bool $forceEnabled
     * @return array
     */
    public function getWeeeTaxAttributeCodes($store = null, $forceEnabled = false)
    {
        if (!$forceEnabled && !$this->weeeConfig->isEnabled($store)) {
            return [];
        }

        if ($this->_allAttributes === null) {
            $this->_allAttributes = $this->_attributeFactory->create()->getAttributeCodesByFrontendType('weee');
        }
        return $this->_allAttributes;
    }

    /**
     * @param Product $product
     * @param null|false|Address $shipping
     * @param null|false|Address $billing
     * @param Website $website
     * @param bool $calculateTax
     * @param bool $round
     * @return DataObject[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getProductWeeeAttributes(
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $round = true
    ) {
        $result = [];
        $websiteId = null;
        /** @var Store $store */
        $store = null;
        if (!$website) {
            $store = $product->getStore();
            if ($store) {
                $websiteId = $store->getWebsiteId();
            }
        }
        if (!$websiteId) {
            $websiteObject = $this->_storeManager->getWebsite($website);
            $websiteId = $websiteObject->getId();
            $store = $websiteObject->getDefaultGroup()->getDefaultStore();
        }

        $allWeee = $this->getWeeeTaxAttributeCodes($store);
        if (!$allWeee) {
            return $result;
        }

        /** @var Calculation $calculator */
        $calculator = $this->_calculationFactory->create();

        $customerId = $this->_customerSession->getCustomerId();
        if ($shipping && $shipping->getCountryId()) {
            $customerTaxClass = $shipping->getQuote()->getCustomerTaxClassId();
        } else {
            // if customer logged use it default shipping and billing address
            if ($customerId) {
                $shipping = $this->accountManagement->getDefaultShippingAddress($customerId);
                $billing = $this->accountManagement->getDefaultBillingAddress($customerId);
                $customerTaxClass = null;
            } else {
                $shippingAddressArray = $this->_customerSession->getDefaultTaxShippingAddress();
                $billingAddressArray = $this->_customerSession->getDefaultTaxBillingAddress();
                if (!empty($billingAddressArray)) {
                    $billing = new DataObject($billingAddressArray);
                }
                if (!empty($shippingAddressArray)) {
                    $shipping = new DataObject($shippingAddressArray);
                }
                $customerTaxClass = $this->_customerSession->getCustomerTaxClassId();
            }
        }

        $rateRequest = $calculator->getRateRequest(
            $shipping,
            $billing,
            $customerTaxClass,
            $store,
            $customerId
        );
        $defaultRateRequest = $calculator->getDefaultRateRequest($store);

        $productAttributes = $this->getResource()->fetchWeeeTaxCalculationsByEntity(
            $rateRequest->getCountryId(),
            $rateRequest->getRegionId(),
            $websiteId,
            $store->getId(),
            $product->getId()
        );

        foreach ($productAttributes as $attribute) {
            $value = $attribute['weee_value'];
            if ($value) {
                $taxAmount = $amount = 0;
                $amount = $value;
                $amountExclTax = $value;
                if ($calculateTax && $this->weeeConfig->isTaxable($store)) {
                    /** @var Calculation $calculator */
                    $defaultPercent = $calculator->getRate(
                        $defaultRateRequest->setProductClassId($product->getTaxClassId())
                    );
                    $currentPercent = $calculator->getRate(
                        $rateRequest->setProductClassId($product->getTaxClassId())
                    );
                    if ($this->_taxData->priceIncludesTax($store)) {
                        $amountInclTax = $value / (100 + $defaultPercent) * (100 + $currentPercent);
                        if ($round) {
                            $amountInclTax = $this->priceCurrency->round($amountInclTax);
                        }
                        $taxAmount = $amountInclTax - $amountInclTax / (100 + $currentPercent) * 100;
                        if ($round) {
                            $taxAmount = $this->priceCurrency->round($taxAmount);
                        }
                        $amountExclTax = $amountInclTax - $taxAmount;
                    } else {
                        $appliedRates = $this->_calculationFactory->create()->getAppliedRates($rateRequest);
                        if (is_array($appliedRates) && count($appliedRates) > 1) {
                            $taxAmount = 0;
                            foreach ($appliedRates as $appliedRate) {
                                $taxRate = $appliedRate['percent'];
                                if ($round) {
                                    $taxAmount += $this->priceCurrency->round($value * $taxRate / 100);
                                } else {
                                    $taxAmount += $value * $taxRate / 100;
                                }
                            }
                        } else {
                            if ($round) {
                                $taxAmount = $this->priceCurrency->round(
                                    $value * $currentPercent / 100
                                );
                            } else {
                                $taxAmount = $value * $currentPercent / 100;
                            }
                        }
                    }
                }

                $one = new DataObject();
                $one->setName(
                    $attribute['label_value'] ? __($attribute['label_value']) : __($attribute['frontend_label'])
                )
                    ->setAmount($amount)
                    ->setTaxAmount($taxAmount)
                    ->setAmountExclTax($amountExclTax)
                    ->setCode($attribute['attribute_code']);

                $result[] = $one;
            }
        }
        return $result;
    }

    /**
     * @param int $countryId
     * @param int $regionId
     * @param int $websiteId
     * @return boolean
     */
    public function isWeeeInLocation($countryId, $regionId, $websiteId)
    {
        return $this->getResource()->isWeeeInLocation($countryId, $regionId, $websiteId);
    }
}
